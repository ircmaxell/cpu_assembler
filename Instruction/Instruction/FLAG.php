<?php

declare(strict_types=1);

namespace TinyComputer\Instruction;
use TinyComputer\Instruction;
use TinyComputer\Argument;
use TinyComputer\Flag as FlagOption;
use TinyComputer\MicroCode as M;

class FLAG extends Instruction {

	public function __construct() {
		parent::__construct("FLAG", 0x0A, new Argument\WriteRegister);
	}

	protected function getInternalMicroCode(FlagOption $flag): array {
		return [
			$this->mux(M::PC_O, M::MEM_O, M::INC_W, M::RI_W),
			$this->mux(M::INCDEC_O, M::PC_W, M::ALU_FLAG_O, M::RIW_X),
		];
	}

}