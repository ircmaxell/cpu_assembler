<?php
declare(strict_types=1);

class OpCode {
	public static function decode(string $name) {
		switch ($name) {
			case 'PC-O': // Program Counter - Output Value To Address Bus
				return 0b00100000;
			case 'SC-O': // Stack Counter - Output Value To Address Bus
				return 0b01000000;
			case 'INCDEC-O': // Inc-Dec Register - Output Value To Address Bus
				return 0b01100000;
			case 'J-O': // J Register - Output value to Address Bus
				return 0b10000000;
			case '0x7FFF-O': // Output top of stack to Address Bus
				return 0b11000000;
			case '0xC000-O': // Output hardcoded start of ROM to bus
				return 0b11100000;
			case 'PC-W': // Program Counter - Write Value From Address Bus
				return 0b00000100;
			case 'SC-W':
				return 0b00001000;
			case 'INC-W':
				return 0b00001100;
			case 'DEC-W':
				return 0b00010000;
			case 'NEXT':
				return 0b00000010;
			case 'INST-W':
				return 0b00000001;
			case 'J1-W':
				return 0b00000001 << 8;
			case 'J2-W':
				return 0b00000010 << 8;
			case 'MEM-O':
				return 0b00000100 << 8;
			case 'MEM-W':
				return 0b00001000 << 8;
			case 'RI-W':
				return 0b00010000 << 8;
			case 'RIO-X':
				return 0b00100000 << 8;
			case 'RIW-X':
				return 0b01000000 << 8;
			case 'HALT':
				return 0b10000000 << 8;
			case 'ALU-ADD':
				return 0b00000000 << 16;
			case 'ALU-SUB':
				return 0b00001000 << 16;
			case 'ALU-INC':
				return 0b00010000 << 16;
			case 'ALU-DEC':
				return 0b00011000 << 16;
			case 'ALU-XOR':
				return 0b00100000 << 16;
			case 'ALU-NOT':
				return 0b00111000 << 16;
			case 'ALU-AND':
				return 0b01000000 << 16;
			case 'ALU-ZERO':
				return 0b01010000 << 16;
			case 'ALU-OR':
				return 0b01100000 << 16;
			case 'ALU-FF':
				return 0b01110000 << 16;
			case 'ALU-SELF':
				return 0b00110000 << 16;
			case 'ALU-O':
				return 0b00000100 << 16;
			case 'ALU-W':
				return 0b00000001 << 16;
			case 'ALU-FLAG-O':
				return 0b00000010 << 16;
		}
		throw new \RuntimeException("Unknown Operation: '$name'");
	}

	public static function mux(Instruction $instruction): array {
		$lowAddress = 0x7F & $instruction->instructionCode;
		$result = [];
		for ($i = 0; $i < 8; $i++) {
			// permute sign results
			$zero = ($i & 1) === 1;
			$sign = ($i & 2) === 2;
			$carry = ($i & 4) === 4;
			$codes = $instruction->getOpcodes($zero, $sign, $carry);
			if (count($codes) > 16) {
				throw new \LogicException("Too many clock states for {$instruction->name}: " . count($codes));
			} else {
				var_dump(count($codes));
			}
			foreach ($codes as $clock => $microInstruction) {
				$address = ($i<<11) | ($clock << 7) | $lowAddress;
				$opCode = self::processMicroInstruction($microInstruction);
				$result[$address] = $opCode;
				// Ignore the 4th flag
				$result[$address | (1 << 14)] = $opCode;
			}
		}
		return $result;
	}

	public static function processMicroInstruction(array $microInstruction): int {
		$result = 0;
		foreach ($microInstruction as $instruction) {
			$result |= self::decode($instruction);
		}
		return $result;
	}
}

class Instruction {

	public $name = '';
	public $instructionCode = 0;
	public $after = [];
	public $optional = [];

	public function __construct(string $name, int $instructionCode, array $after, array $optional = []) {
		$this->name = $name;
		$this->instructionCode = $instructionCode;
		$this->after = $after;
		$this->optional = $optional;
	}
	
	public function getOpcodes(bool $zero, bool $sign, bool $carry): array {
		// default decode
		$result = array_merge([
			// LOAD
			["PC-O", "MEM-O", "INC-W", "INST-W"],
			["PC-O", "MEM-O"],
			// INC PC
			["INCDEC-O", "PC-W"],
		], $this->after);

		$flag = ($zero ? 1 : 0) | ($sign ? 2 : 0) | ($carry ? 4 : 0);

		if (isset($this->optional["mask"])) {
			if (($this->optional["mask"] & $flag) === $this->optional["mask"]) {
				$result = array_merge($result, $this->optional["match"]);
			} elseif (isset($this->optional["default"])) {
				$result = array_merge($result, $this->optional["default"]);
			}
		}

		$result[] = ["NEXT"];

		return $result;
	}
}

function a(Instruction ...$a): array {
	return $a;
}

function alu($name): array {
	$code = [
		"ADD" => 0x10,
		"SUB" => 0x11,
		"AND" => 0x12,
		"OR" => 0x13,
		"XOR" => 0x14,
		"INC" => 0x15,
		"DEC" => 0x16,
		"NOT" => 0x17,
		"ZERO" => 0x18,
		"FF" => 0x19,
		"SELF" => 0x1a,
	][$name] ?? 0;
	if ($code === 0) {
		throw new \RuntimeException("Unknown ALU op: $name");
	}

	return [
		// Normal
		new Instruction($name, $code, [
			["PC-O", "MEM-O", "INC-W", "RI-W",],
			["INCDEC-O", "PC-W", "RIO-X", "ALU-$name", "ALU-W"],
			["RIO-X", "ALU-$name"],
			["ALU-O", "RIW-X"],
		]),
		// Mem
		new Instruction("$name-I", $code + 0x10, [
			["PC-O", "MEM-O", "INC-W", "RI-W",],
			["INCDEC-O", "PC-W"],
			["PC-O", "MEM-O", "ALU-$name", "INC-W", "ALU-W"],
			["PC-O", "MEM-O", "ALU-$name"],
			["INCDEC-O", "PC-W", "ALU-O", "RIW-X"],
		]),
	];
}

function jump($name): array {
	list($code, $mask, $inverse) = [
		"JUMP" => [0x30, 0x00, false],
		"JUMP-Z" => [0x32, 0b001, false],
		"JUMP-NZ" => [0x33, 0b001, true],
		"JUMP-S" => [0x34, 0b010, false],
		"JUMP-NS" => [0x35, 0b010, true],
		"JUMP-C" => [0x36, 0b100, false],
		"JUMP-NC" => [0x37, 0b100, true],
	][$name] ?? 0;

	$maskPartsNormal = [
		[
			// default (not jumping)
			["INCDEC-O", "PC-W"],
		],
		[
			// match (jumping)
			["J-O", "PC-W"],
		]
	];

	$maskPartsImmediate = [
		[
			["PC-O", "MEM-O", "INC-W"],
			["INCDEC-O", "PC-W"],
		],
		[
			["J-O", "PC-W"],
		]
	];

	return [
		new Instruction("$name", $code, [
			["PC-O", "MEM-O", "INC-W", "J1-W"],
			["PC-O", "MEM-O"],
			["INCDEC-O", "PC-W"],
			["PC-O", "MEM-O", "INC-W", "J2-W"],
			["PC-O", "MEM-O"],
		], [
			"default" => $maskPartsNormal[$inverse ? 1 : 0],
			"mask" => $mask,
			"match" => $maskPartsNormal[$inverse ? 0 : 1],
		]),
		new Instruction("$name-I", $code + 0x10, [
		], [
			"default" => $maskPartsImmediate[$inverse ? 1 : 0],
			"mask" => $mask,
			"match" => $maskPartsImmediate[$inverse ? 0 : 1],
		]),
	];
}

$instructions = a(
	new Instruction("RESET", 0x00, [
		["0x7FFF-O", "SC-W"],
		["0xC000-O", "PC-W"],
		["NEXT"],
	]),
	new Instruction("HALT", 0x7F, [
		["HALT"],
	]),
	new Instruction("MOV", 0x01, [
		["PC-O", "MEM-O", "INC-W", "RI-W",],
		["INCDEC-O", "RIO-X", "PC-W", "RIW-X"],
	]),
	new Instruction("LOAD", 0x02, [
		["PC-O", "MEM-O", "INC-W", "RI-W",],
		["INCDEC-O", "PC-W"],
		["J-O", "MEM-O", "RIW-X"],
	]),
	new Instruction("LOAD-I", 0x03, [
		["PC-O", "MEM-O", "INC-W", "RI-W",],
		["INCDEC-O", "PC-W"],
		["PC-O", "MEM-O", "INC-W", "RIW-X"],
		["INCDEC-O", "PC-W"],
	]),
	new Instruction("STORE", 0x05, [
		["PC-O", "MEM-O", "INC-W", "RI-W",],
		["INCDEC-O", "PC-W"],
		["J-O", "RIO-X", "MEM-W"],
		["J-O", "RIO-X"],
	]),
	new Instruction("STORE-I", 0x06, [
		["PC-O", "MEM-O", "INC-W", "RI-W",],
		["INCDEC-O", "PC-W"],
		["PC-O", "MEM-O", "INC-W", "J1-W",],
		["PC-O", "MEM-O"],
		["INCDEC-O", "PC-W"],
		["PC-O", "MEM-O", "INC-W", "J2-W",],
		["PC-O", "MEM-O"],
		["INCDEC-O", "PC-W"],
		["J-O", "RIO-X", "MEM-W"],
		["J-O", "RIO-X"],
	]),
	new Instruction("PUSH", 0x07, [
		["PC-O", "MEM-O", "INC-W", "RI-W",],
		["INCDEC-O", "PC-W"],
		["SC-O", "RIO-X", "DEC-W", "MEM-W"],
		["SC-O", "RIO-X"],
		["INCDEC-O", "SC-W"],
	]),
	new Instruction("POP", 0x08, [
		["PC-O", "MEM-O", "INC-W", "RI-W",],
		["INCDEC-O", "PC-W"],
		["SC-O", "INC-W"],
		["INCDEC-O", "MEM-O", "SC-W", "RIW-X"],
	]),
	new Instruction("FLAG", 0x09, [
		["PC-O", "MEM-O", "INC-W", "RI-W",],
		["INCDEC-O", "ALU-FLAG-O", "PC-W", "RIW-X"],
	]),
	new Instruction("CMP", 0x0A, [
		["PC-O", "MEM-O", "INC-W", "RI-W",],
		["INCDEC-O", "PC-W", "RIO-X", "ALU-SUB", "ALU-W"],
		["RIO-X", "ALU-SUB"],
	]),
	new Instruction("CMP-I", 0x0B, [
		["PC-O", "MEM-O", "INC-W", "ALU-SUB", "ALU-W"],
		["PC-O", "MEM-O", "ALU-SUB"],
		["INCDEC-O", "PC-W"],
	]),
	...alu("ADD"),
	...alu("SUB"),
	...alu("INC"),
	...alu("DEC"),
	...alu("AND"),
	...alu("OR"),
	...alu("XOR"),
	...alu("NOT"),
	...alu("ZERO"),
	...alu("FF"),
	...alu("SELF"),
	...jump("JUMP"),
	...jump("JUMP-Z"),
	...jump("JUMP-NZ"),
	...jump("JUMP-S"),
	...jump("JUMP-NS"),
	...jump("JUMP-C"),
	...jump("JUMP-NC"),

	...[]
);

$roms = [
	"A-BUS" => str_repeat("\0", 1<<15),
	"D-BUS" => str_repeat("\0", 1<<15),
	"ALU" => str_repeat("\0", 1<<15),
];

$codes = [];

foreach ($instructions as $instruction) {
	$codes[$instruction->instructionCode] = true;
	foreach (OpCode::mux($instruction) as $address => $code) {
		$roms["A-BUS"][$address] = chr(($code) & 0xFF);
		$roms["D-BUS"][$address] = chr(($code >> 8) & 0xFF);
		$roms["ALU"][$address] = chr(($code >> 16) & 0xFF);
	}
}

for ($ic = 0; $ic < 128; $ic++) {
	if (isset($codes[$ic])) {
		continue;
	}
	$instruction = new Instruction("NOOP", $ic, []);
	foreach (OpCode::mux($instruction) as $address => $code) {
		$roms["A-BUS"][$address] = chr(($code) & 0xFF);
		$roms["D-BUS"][$address] = chr(($code >> 8) & 0xFF);
		$roms["ALU"][$address] = chr(($code >> 16) & 0xFF);
	}
}


$i = (int) ($argv[1] ?? 1);
$clock = (int) ($argv[2] ?? 0);
$address = $i | ($clock << 7);
printf("Instruction %016b : %08b\n", $address, ord($roms['A-BUS'][$address]));

foreach ($roms as $name => $rom) {
	list($low, $high) = output($rom);
	file_put_contents(__DIR__ . '/' . $name . '.low.rom', $low);
	file_put_contents(__DIR__ . '/' . $name . '.high.rom', $high);
}

//var_dump(output($roms['A-BUS']));

function output(string $codes): array {
	$result = ['', ''];
	$chunks = str_split($codes, 1 << 14);
	foreach ($chunks as $key => $chunk) {
		$len = strlen($chunk);
		for ($i = 0; $i < $len; $i++) {
			$result[$key] .= sprintf("0x%02X, ", ord($chunk[$i]));
		}
	}
	return $result;
}