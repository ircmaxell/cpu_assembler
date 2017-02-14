<?php

declare(strict_types=1);

namespace TinyComputer\Instruction;
use TinyComputer\Instruction;
use TinyComputer\Argument;
use TinyComputer\Flag;
use TinyComputer\MicroCode as M;

class JUMP extends Instruction {

	protected $op = 0;

	public function __construct(string $type, int $code, int $op, bool $negated) {
		$this->op = $op;
		$this->negated = $negated;
		parent::__construct($type, $code, new Argument\Address);
	}

	public static function getOps(): array {
		return [
			["JUMP", 0x30, Flag::NONE, false],
			["JUMP-Z", 0x32, Flag::ZERO, false],
			["JUMP-NZ", 0x33, Flag::ZERO, true],
			["JUMP-S", 0x34, Flag::SIGN, false],
			["JUMP-NS", 0x35, Flag::SIGN, true],
			["JUMP-C", 0x36, Flag::CARRY, false],
			["JUMP-NC", 0x37, Flag::CARRY, true],
		];
	}

	public static function factory(): array {
		$ops = static::getOps();
		$result = [];
		foreach ($ops as $op) {
			$result[] = new static($op[0], $op[1], $op[2], $op[3]);
		}
		return $result;
	}

	protected function getInternalMicroCode(Flag $flag): array {
		if ($flag->is($this->op) XOR $this->negated) {
			return [
				$this->mux(M::PC_O, M::MEM_O, M::INC_W, M::J1_W),
				$this->mux(M::INCDEC_O, M::PC_W),
				$this->mux(M::PC_O, M::MEM_O, M::INC_W, M::J2_W),
				$this->mux(M::J_O, M::PC_W),
			];	
		}
		return [
			$this->mux(M::PC_O, M::INC_W),
			$this->mux(M::INCDEC_O, M::PC_W),
			$this->mux(M::PC_O, M::INC_W),
			$this->mux(M::INCDEC_O, M::PC_W),
		];
	}

}