<?php

declare(strict_types=1);

namespace TinyComputer;
use TinyComputer\MicroCode as M;

class ROMBuilder {
	
	protected $rom = [];
	protected $instructionSet;

	public function __construct(InstructionSet $instructionSet) {
		$this->instructionSet = $instructionSet;
	}

	public function buildRom(): array {
		// Prefill the ROM with HALT instructions, in case of oddness
		$this->rom = array_fill(0, 1<<15, 0x7F);
		foreach ($this->instructionSet->instructions as $instruction) {
			$this->parseInstruction($instruction);
		}
		$result = [
			"ABUS_ROM" => "",
			"DBUS_ROM" => "",
			"ALU_ROM" => "",
		];
		for ($i = 0; $i < 1<<15; $i++) {
			$result['ABUS_ROM'] .= chr($this->rom[$i] & 0xFF);
			$result['DBUS_ROM'] .= chr(($this->rom[$i] >> 16) & 0xFF);
			$result['ALU_ROM'] .= chr(($this->rom[$i] >> 8) & 0xFF);
		}
		return $result;
	}

	protected function parseInstruction(Instruction $instruction) {
		$lowAddress = 0x7F & $instruction->code;
		foreach (Flag::permute() as $flag) {
			$codes = $instruction->getMicroCode($flag);
			if (count($codes) > 16) {
				throw new \LogicException("Too many clock states for {$instruction->name}: " . count($codes));
			}
			foreach ($codes as $clock => $microInstruction) {
				$address = ($flag->value << 11) | ($clock << 7) | $lowAddress;
				$this->rom[$address] = $microInstruction;
			}
		}
	}
}