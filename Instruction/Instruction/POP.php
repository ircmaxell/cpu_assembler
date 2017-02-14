<?php

declare(strict_types=1);

namespace TinyComputer\Instruction;
use TinyComputer\Instruction;
use TinyComputer\Argument;
use TinyComputer\Flag;
use TinyComputer\MicroCode as M;

class POP extends Instruction {

	public function __construct() {
		parent::__construct("POP", 0x09, new Argument\WriteRegister);
	}

	protected function getInternalMicroCode(Flag $flag): array {
		return [
			$this->mux(M::PC_O, M::MEM_O, M::INC_W, M::RI_W),
			$this->mux(M::INCDEC_O, M::PC_W),
			$this->mux(M::SC_O, M::INC_W),
			$this->mux(M::INCDEC_O, M::MEM_O, M::SC_W, M::RIW_X),
		];
	}

}