<?php

declare(strict_types=1);

namespace TinyComputer\Instruction;
use TinyComputer\Instruction;
use TinyComputer\Argument;
use TinyComputer\Flag;
use TinyComputer\MicroCode as M;

class INC16 extends Instruction {

	public function __construct() {
		parent::__construct("INC16", 0x50, new Argument\ReadWriteRegister, new Argument\ReadWriteRegister);
	}

	protected function getInternalMicroCode(Flag $flag): array {
		$core = [
			$this->mux(M::PC_O, M::MEM_O, M::INC_W, M::RI_W),
			$this->mux(M::INCDEC_O, M::PC_W, M::RIO_X, M::ALU_INC, M::ALU_W),
			$this->mux(M::RIO_X, M::ALU_INC),
			$this->mux(M::ALU_O, M::RIW_X),
		];
		if ($flag->isCarry()) {
			return array_merge($core, [
				$this->mux(M::PC_O, M::MEM_O, M::INC_W, M::RI_W),
				$this->mux(M::INCDEC_O, M::PC_W, M::RIO_X, M::ALU_INC, M::ALU_W),
				$this->mux(M::RIO_X, M::ALU_INC),
				$this->mux(M::ALU_O, M::RIW_X),
			]);
		} else {
			return array_merge($core, [
				$this->mux(M::PC_O, M::INC_W),
				$this->mux(M::INCDEC_O, M::PC_W),
			]);
		}
	}

}