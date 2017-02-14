<?php

declare(strict_types=1);

namespace TinyComputer\Instruction;
use TinyComputer\Instruction;
use TinyComputer\Argument;
use TinyComputer\Flag;
use TinyComputer\MicroCode as M;

class MOV extends Instruction {

	public function __construct() {
		parent::__construct("MOV", 0x01, new Argument\ReadWriteRegister);
	}

	protected function getInternalMicroCode(Flag $flag): array {
		return [
			$this->mux(M::PC_O, M::MEM_O, M::INC_W, M::RI_W),
			$this->mux(M::INCDEC_O, M::RIO_X, M::PC_W, M::RIW_X),
		];
	}

}