<?php

declare(strict_types=1);

namespace TinyComputer\Instruction;
use TinyComputer\Instruction;
use TinyComputer\Argument;
use TinyComputer\Flag;
use TinyComputer\MicroCode as M;

class LOAD16 extends Instruction {

	public function __construct() {
		parent::__construct("LOAD-16", 0x52, new Argument\WriteRegister, new Argument\WriteRegister, new Argument\Address);
	}

	protected function getInternalMicroCode(Flag $flag): array {
		return [
			$this->mux(M::PC_O, M::INC_W, M::MEM_O, M::RI_W),
			$this->mux(M::INCDEC_O, M::PC_W),
			$this->mux(M::PC_O, M::INC_W),
			$this->mux(M::INCDEC_O, M::PC_W),
			$this->mux(M::PC_O, M::DEC_W, M::MEM_O, M::RIW_X),
			$this->mux(M::INCDEC_O, M::PC_W),
			$this->mux(M::PC_O, M::INC_W, M::MEM_O, M::RI_W),
			$this->mux(M::INCDEC_O, M::PC_W),
			$this->mux(M::PC_O, M::INC_W),
			$this->mux(M::INCDEC_O, M::PC_W),
			$this->mux(M::PC_O, M::INC_W, M::MEM_O, M::RIW_X),
			$this->mux(M::INCDEC_O, M::PC_W),
		];
	}

}