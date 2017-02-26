<?php

declare(strict_types=1);

namespace TinyComputer;

class Assembler {

	protected $instructionSet;

	protected $labels = [];
	protected $addresses = [];

	public function __construct(InstructionSet $instructionSet) {
		$this->instructionSet = $instructionSet;
	}

	public function assemble(string $code, bool $includeSelfTest = true, $offset = 0xC000): string {
		$addressOffset = 0;
		$memoryOffset = 0;
		$result = "";
		$labels = [];
		$addresses = [];
		$dataSegments = [];

		
		if ($includeSelfTest) {
			$code = "
				" . file_get_contents(__DIR__ . "/self-test.asm")
				. "
				__start__:
				"
				. $code;
		} else {
			$code = "
				JUMP __init__;
				__start__:
				" 
				. $code;
		}
		$code = '.offset 0x' . dechex($offset) . "\n" . $code;

		$code = preg_replace('(//[^\n\r]*$)im', '', $code);
		$code = preg_replace('(^\s*(\r\n|\n|\r))im', '', $code);
		$arg = "[A-Z0-9_]*";
		$label = "[A-Z0-9_-]+";
		$command = "[A-Z][A-Z0-9-]*";
		$parser = "(^((?:\s*$label:\s*)*)\s*(\.?$command)(\s+$arg\s*(,(?3)|\\$\s*-\s*$label|'[^']*'(,(?3))?)?)?(;.*)?$)im";
		preg_match_all($parser, $code, $matches, PREG_SET_ORDER);

		$section = "TEXT";
		foreach ($matches as $match) {
			$newLabels = $this->extractLabels($match[1]);
			foreach ($newLabels as $label) {
				// enter the offset of the label into the labels array
				$labels[$label] = $addressOffset + strlen($result);
			}
			$name = strtoupper($match[2]);
			$instruction = $this->instructionSet->namedInstructions[$name] ?? null;
			$args = array_map('trim', explode(',', $match[3] ?? ''));
			if ($instruction && $section == 'TEXT') {
				$result .= chr($instruction->code);
				$argOffset = 0;
				foreach ($instruction->args as $arg) {
					$numArgs = $arg->numberOfArguments();
					if ($numArgs > count($args) - $argOffset) {
						throw new \LogicException("Not enough arguments for $name");
					}
					if ($arg instanceof Argument\Address && substr($args[$argOffset], 0, 2) !== '0x') {
						// indirect jump
						$addresses[$args[$argOffset]][] = strlen($result);
						$result .= $arg->encode(["0x0000"]);
					} else {
						$result .= $arg->encode(array_slice($args, $argOffset, $numArgs));
					}
					$argOffset += $numArgs;
				}
				if ($argOffset != count($args) && ($argOffset !== 0 && $args === [''])) {
					throw new \LogicException("Too many arguments for $name");
				}
			} elseif ($name === "CALL" && $section === 'TEXT') {
				// compile the call
				if (count($args) !== 1) {
					throw new \RuntimeException("Call requires exactly 1 argument");
				}
				$return = $addressOffset + strlen($result) + 7;
				$result .= chr((new Instruction\PUSH_I)->code);
				$result .= (new Argument\Value)->encode(["0x" . dechex($return >> 8)]);
				$result .= chr((new Instruction\PUSH_I)->code);
				$result .= (new Argument\Value)->encode(["0x" . dechex($return & 0xFF)]);
				$result .= chr(0x30); // Jump
				$result .= chr(0x00);
				$result .= chr(0x00); // jump destination
				$addresses[$args[0]][] = strlen($result) - 2;
			} elseif ($name === "RETURN" && $section === 'TEXT') {
				if (count($args) !== 0 && !(count($args) === 1 && empty($args[0]))) {
					throw new \RuntimeException("Return requires exactly 0 arguments");
				}
				$result .= chr((new Instruction\POP)->code);
				$result .= (new Argument\WriteRegister)->encode(["RJ2"]);
				$result .= chr((new Instruction\POP)->code);
				$result .= (new Argument\WriteRegister)->encode(["RJ1"]);
				$result .= chr(0x40); // Jump-I
			} elseif ($name === '.BSS') {
				$section = "BSS";
			} elseif ($name === '.TEXT') {
				$section = "TEXT";
			} elseif ($name === '.RODATA') {
				$section = "DATA";
			} elseif ($name === '.DATA') {
				$section = "RODATA";
			} elseif ($name === '.OFFSET') {
				if (count($args) !== 1) {
					throw new \LogicException("Unknown offset value: " . $match[3]);
				}
				$addressOffset = intval($args[0], 0);
			} elseif ($section === "BSS") {
				// non-initialized ram blocks
				if (count($args) !== 1) {
					throw new \RuntimeException("BSS declarations require exactly 1 argument");
				}
				$argSize = intval($args[0], 0);
				switch ($name) {
					case 'RESB': 
						$size = $argSize;
						break;
					case 'RESW':
						$size = 2 * $argSize;
						break;
					default:
						throw new \RuntimeException("Unknown BSS command: $name");
				}
				foreach ($newLabels as $label) {
					$labels[$label] = $memoryOffset;
				}
				$memoryOffset += $size;
			} elseif ($section === "RODATA" || $section === "DATA") {
				$name = $match[2]; // restore case
				$labels[$name] = $addressOffset + strlen($result) + 1;
				if ($section === "DATA") {
					$dataSegments[$name] = [$addressOffset + strlen($result)];
				}
				list ($type, $args[0]) = explode(' ', $args[0], 2);
				$type = strtolower($type);
				if (!in_array($type, ["db", "dw", "equ"])) {
					throw new \RuntimeException("Unknown data section type: $type");
				}
				foreach ($args as $arg) {
					$result .= $this->processArgType($arg, $type, $name, $labels);
				}
				if ($section === "DATA") {
					$dataSegments[$name][] = $addressOffset + strlen($result) - 1;
					$labels[$name] = $memoryOffset;
					$memoryOffset += $dataSegments[$name][1] - $dataSegments[$name][0];
				}
			} else {
				throw new \LogicException("Unknown Instruction $name");
			}
		}
		if (!empty($dataSegments)) {
			$labels["__init__"] = $addressOffset + strlen($result) - 1;
			foreach ($dataSegments as $name => $segment) {
				// LOAD-I RB, $segment[0][high]
				// LOAD-I RC, $segment[0][low]
				// LOAD-M RA, RB, RC
				// STORE-I RA, $labels[$name]
				// INC16 RC, RB
				// LOAD-M RA, RB, RC
				// STORE-I RA, $labels[$name]
				// TODO: write code here to initialize RAM
			}
			// JUMP start
			$result .= chr(0x30) . chr(0x00) . chr(0x00);
			$addresses["__start__"][] = strlen($result) - 2;
		} else {
			// zero out init and the original jump
			foreach ($addresses["__init__"] as $addr) {
				// zero out the jump as well
				$result[$addr - 1] = "\0";
				$result[$addr] = "\0";
				$result[$addr + 1] = "\0";
			}
			unset($addresses["__init__"]);
		}
		foreach ($addresses as $label => $offsets) {
			if (!isset($labels[$label])) {
				throw new \RuntimeException("Unknown label for indirect jump: $label");
			}
			$encoded = Argument\Address::encodeRaw($labels[$label]);
			foreach ($offsets as $offset) {
				$result[$offset] = $encoded[0];
				$result[$offset+1] = $encoded[1];
			}
		}
		return $result;
	}

	protected function processArgType(string $arg, string $type, string $name, array $labels): string {
		if ($type === "equ") {
			$sides = array_map('trim', explode('-', $arg, 2));
			if (count($sides) !== 2) {
				throw new \LogicException("Unsupported operations for $name: $arg");
			}
			$values = [];
			foreach ($sides as $side) {
				if ($side == '$') {
					$values[] = $labels[$name];
				} elseif (isset($labels[$side])) {
					$values[] = $labels[$side];
				} else {
					throw new \RuntimeException("Unknown label for expression: $side in $name: $arg");
				}
			}
			return $this->processArgType("0x" . dechex($values[0] - $values[1]), "db", $name, $labels);
		}
		if (substr($arg, 0, 2) === '0x') {
			$val = intval($arg, 0);
			switch ($type) {
				case 'db':
					return chr($val & 0xFF);
				case 'dw':
					return chr(($val >> 8) & 0xFF) . chr($val & 0xFF);
			}
		} elseif (substr($arg, 0, 1) === "'") {
			// quoted string, remove quote
			$result = '';
			for ($i = 1; $i < strlen($arg) - 1; $i++) {
				$result .= $this->processArgType("0x" . dechex(ord($arg[$i])), $type, $name, $labels);
			}
			return $result;
		}
		throw new \RuntimeException("Unknown arg format for $name: $arg");
	}

	protected function extractLabels(string $raw): array {
		$result = [];
		do {
			$raw = trim($raw);
			if (empty($raw)) {
				return $result;
			}
			list ($part, $raw) = explode(":", $raw, 2);
			$result[] = trim($part);
		} while(true);
		return $result;
	}


	public function disassemble(string $code, int $printOffset): string {
		$offset = 0;
		$result = "";
		while ($offset < strlen($code)) {
			$nibble = ord($code[$offset++]);
			$result .= sprintf("%016b (%04X) - %02X ", $printOffset + $offset - 1, $printOffset + $offset - 1, $nibble);
			if (!isset($this->instructionSet->instructions[$nibble])) {
				throw new \RuntimeException("Unknown instruction $nibble");
			}
			$instruction = $this->instructionSet->instructions[$nibble];
			$decodedArgs = [];
			$rawArg = '';
			foreach ($instruction->args as $arg) {
				$encodedArg = substr($code, $offset, $arg->numberOfBytes());
				$encodedLen = strlen($encodedArg);
				for ($i = $encodedLen - 1; $i >= 0; $i--) {
					$rawArg .= sprintf(" %02X", ord(substr($encodedArg, -1 - $i, 1)));
				}
				$offset += $arg->numberOfBytes();
				$decodedArgs = array_merge($decodedArgs, $arg->decode($encodedArg));
			}
			$result .= sprintf("%-12s", trim($rawArg));
			$result .= " - " . $instruction->name;
			if ($decodedArgs) {
				$result .= " " . implode(", ", $decodedArgs);
			}
			$result .= ";\n";
		}
		return $result;
	}

}