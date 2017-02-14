<?php

declare(strict_types=1);

namespace TinyComputer\Instruction;
use TinyComputer\Instruction;
use TinyComputer\Argument;
use TinyComputer\Flag;
use TinyComputer\MicroCode as M;

class JUMP_I extends Instruction {

	protected $op = 0;

	public function __construct(string $type, int $code, int $op, bool $negated) {
		$this->op = $op;
		$this->negated = $negated;
		parent::__construct($type, $code);
	}

	public static function factory(): array {
		$ops = JUMP::getOps();
		$result = [];
		foreach ($ops as $op) {
			$result[] = new static($op[0] . '-I', $op[1] + 0x10, $op[2], $op[3]);
		}
		return $result;
	}

	protected function getInternalMicroCode(Flag $flag): array {
		if ($flag->is($this->op) XOR $this->negated) {
			return [
				$this->mux(M::J_O, M::PC_W),
			];	
		}
		return [
		];
	}

}