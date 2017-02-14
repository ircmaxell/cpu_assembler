<?php

declare(strict_types=1);

namespace TinyComputer\Instruction;
use TinyComputer\Instruction;
use TinyComputer\Argument;
use TinyComputer\Flag;
use TinyComputer\MicroCode as M;

class CMP_I extends Instruction {

	public function __construct() {
		parent::__construct("CMP-I", 0x0C, new Argument\Value);
	}

	protected function getInternalMicroCode(Flag $flag): array {
		return [
			$this->mux(M::PC_O, M::MEM_O, M::INC_W, M::ALU_SUB, M::ALU_W),
			$this->mux(M::PC_O, M::MEM_O, M::ALU_SUB),
			$this->mux(M::INCDEC_O, M::PC_W),
		];
	}

}