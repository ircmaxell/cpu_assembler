<?php

declare(strict_types=1);

namespace TinyComputer\Instruction;
use TinyComputer\Instruction;
use TinyComputer\Argument;
use TinyComputer\Flag;
use TinyComputer\MicroCode as M;

class CMP extends Instruction {

	public function __construct() {
		parent::__construct("CMP", 0x0B, new Argument\ReadRegister);
	}

	protected function getInternalMicroCode(Flag $flag): array {
		return [
			$this->mux(M::PC_O, M::MEM_O, M::INC_W, M::RI_W),
			$this->mux(M::INCDEC_O, M::PC_W, M::RIO_X, M::ALU_SUB, M::ALU_W),
			$this->mux(M::RIO_X, M::ALU_SUB),
		];
	}

}