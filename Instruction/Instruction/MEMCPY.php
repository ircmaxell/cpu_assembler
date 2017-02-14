<?php

declare(strict_types=1);

namespace TinyComputer\Instruction;
use TinyComputer\Instruction;
use TinyComputer\Argument;
use TinyComputer\Flag;
use TinyComputer\MicroCode as M;

class MEMCPY extends Instruction {

	public function __construct() {
		parent::__construct("MEMCPY", 0x0D, new Argument\Value);
	}

	protected function getInternalMicroCode(Flag $flag): array {
		return [
			$this->mux(M::PC_O, M::MEM_O, M::INC_W, M::ALU_SELF, M::ALU_W),
			$this->mux(M::PC_O, M::MEM_O, M::ALU_SELF),
			$this->mux(M::INCDEC_O, M::PC_W),
			$this->mux(M::J_O, M::ALU_O, M::MEM_W),
			$this->mux(M::J_O, M::ALU_O),
		];
	}

}