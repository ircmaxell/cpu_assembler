<?php

declare(strict_types=1);

namespace TinyComputer\Instruction;
use TinyComputer\Instruction;
use TinyComputer\Argument;
use TinyComputer\Flag;
use TinyComputer\MicroCode as M;

class RESET extends Instruction {

	public function __construct() {
		parent::__construct("RESET", 0x7E);
	}

	protected function getInternalMicroCode(Flag $flag): array {
		return [
			$this->mux(M::x7FFF_O, M::SC_W),
		];
	}

}