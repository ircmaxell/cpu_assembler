<?php

declare(strict_types=1);

namespace TinyComputer\Instruction;
use TinyComputer\Instruction;
use TinyComputer\Argument;
use TinyComputer\Flag;
use TinyComputer\MicroCode as M;

class LOAD_M extends Instruction {

	public function __construct() {
		parent::__construct("LOAD-M", 0x0F, new Argument\ReadWriteRegister, new Argument\ReadRegister);
	}

	protected function getInternalMicroCode(Flag $flag): array {
		return [
			$this->mux(M::PC_O, M::MEM_O, M::INC_W, M::RI_W),
			$this->mux(M::INCDEC_O, M::PC_W, M::RIO_X, M::J1_W),
			$this->mux(M::RIW_X, M::ALU_SELF, M::ALU_W),
			$this->mux(M::RIW_X, M::ALU_SELF),
			$this->mux(M::PC_O, M::MEM_O, M::INC_W, M::RI_W),
			$this->mux(M::INCDEC_O, M::PC_W, M::RIO_X, M::J2_W),
			$this->mux(M::ALU_O, M::RI_W),
			$this->mux(M::J_O, M::MEM_O, M::RIW_X),
		];
	}

}