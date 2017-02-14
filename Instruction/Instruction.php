<?php

declare(strict_types=1);

namespace TinyComputer;
use TinyComputer\MicroCode as M;

class Instruction {
	public $name = '';
	public $code = 0;
	public $args = [];

	protected function __construct(string $name, int $code, Argument ... $args) {
		$this->name = $name;
		$this->code = $code;
		$this->args = $args;
	}

	public static function factory(): array {
		return [new static];
	}

	public function getMicroCode(Flag $flag): array {
		return (function(int ...$codes): array {
			return $codes;
		})(
			$this->mux(M::PC_O, M::MEM_O, M::INC_W, M::INST_W),
			$this->mux(M::INCDEC_O, M::PC_W),
			...$this->getInternalMicroCode($flag),
			...[MicroCode::NEXT]
		);
	}

	protected function getInternalMicroCode(Flag $flag): array {
		return [];
	}

	protected function mux(int ...$codes): int {
		$result = 0;
		foreach ($codes as $code) {
			$result |= $code;
		}
		return $result;
	}

}