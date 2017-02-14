<?php

declare(strict_types=1);

namespace TinyComputer\Instruction;
use TinyComputer\Instruction;
use TinyComputer\Argument;
use TinyComputer\Flag;
use TinyComputer\MicroCode as M;

class ALU_I extends Instruction {

	protected $op = 0;

	public function __construct(string $type, int $code, int $op) {
		$this->op = $op;
		parent::__construct($type . '-I', $code, new Argument\WriteRegister);
	}

	public static function factory(): array {
		$ops = ALU::getOps();
		$result = [];
		foreach ($ops as $op) {
			$result[] = new static($op[0], $op[1] + 0x10, $op[2]);
		}
		return $result;
	}

	protected function getInternalMicroCode(Flag $flag): array {
		return [
			$this->mux(M::PC_O, M::MEM_O, M::INC_W, M::RI_W),
			$this->mux(M::INCDEC_O, M::PC_W),
			$this->mux(M::PC_O, M::MEM_O, M::INC_W, M::ALU_W, $this->op),
			$this->mux(M::PC_O, M::MEM_O, $this->op),
			$this->mux(M::INCDEC_O, M::PC_W, M::ALU_O, M::RIW_X),
		];
	}

}