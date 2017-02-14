<?php

declare(strict_types=1);

namespace TinyComputer\Instruction;
use TinyComputer\Instruction;
use TinyComputer\Argument;
use TinyComputer\Flag;
use TinyComputer\MicroCode as M;

class ALU extends Instruction {

	protected $op = 0;

	public function __construct(string $type, int $code, int $op) {
		$this->op = $op;
		parent::__construct($type, $code, new Argument\ReadWriteRegister);
	}

	public static function getOps(): array {
		return [
			["ADD", 0x10, M::ALU_ADD],
			["SUB", 0x11, M::ALU_SUB],
			["AND", 0x12, M::ALU_AND],
			["OR", 0x13, M::ALU_OR],
			["XOR", 0x14, M::ALU_XOR],
			["INC", 0x15, M::ALU_INC],
			["DEC", 0x16, M::ALU_DEC],
			["NOT", 0x17, M::ALU_NOT],
			["ZERO", 0x18, M::ALU_ZERO],
			["FF", 0x19, M::ALU_FF],
			["SELF", 0x1A, M::ALU_SELF],
		];
	}

	public static function factory(): array {
		$ops = static::getOps();
		$result = [];
		foreach ($ops as $op) {
			$result[] = new static($op[0], $op[1], $op[2]);
		}
		return $result;
	}

	protected function getInternalMicroCode(Flag $flag): array {
		return [
			$this->mux(M::PC_O, M::MEM_O, M::INC_W, M::RI_W),
			$this->mux(M::INCDEC_O, M::RIO_X, M::PC_W, $this->op, M::ALU_W),
			$this->mux(M::RIO_X, $this->op),
			$this->mux(M::ALU_O, M::RIW_X),
		];
	}

}