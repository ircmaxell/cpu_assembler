<?php

declare(strict_types=1);

namespace TinyComputer\Instruction;
use TinyComputer\Instruction;
use TinyComputer\Argument;
use TinyComputer\Flag;
use TinyComputer\MicroCode as M;

class NOOP extends Instruction {

	public function __construct() {
		parent::__construct("NOOP", 0x00);
	}

}