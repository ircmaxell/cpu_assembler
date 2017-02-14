<?php

declare(strict_types=1);

namespace TinyComputer;

class Flag {

	const NONE = 0;
	const ZERO = 1;
	const SIGN = 2;
	const CARRY = 4;
	const RESERVED = 8;

	public $value = 0;

	public function __construct(int $value) {
		$this->value = $value;
	}

	public function is(int $flag): bool {
		return ($this->value & $flag) === $flag;
	}

	public function isZero(): bool {
		return ($this->value & self::ZERO) === self::ZERO;
	}

	public function isSign(): bool {
		return ($this->value & self::SIGN) === self::SIGN;
	}

	public function isCarry(): bool {
		return ($this->value & self::CARRY) === self::CARRY;
	}

	public static function permute(): array {
		return [
			new static(self::NONE),
			new static(self::ZERO),
			new static(self::SIGN),
			new static(self::ZERO | self::SIGN),
			new static(self::CARRY),
			new static(self::CARRY | self::ZERO),
			new static(self::CARRY | self::SIGN),
			new static(self::CARRY | self::ZERO | self::SIGN),
			new static(self::RESERVED),
			new static(self::RESERVED | self::ZERO),
			new static(self::RESERVED | self::SIGN),
			new static(self::RESERVED | self::ZERO | self::SIGN),
			new static(self::RESERVED | self::CARRY),
			new static(self::RESERVED | self::CARRY | self::ZERO),
			new static(self::RESERVED | self::CARRY | self::SIGN),
			new static(self::RESERVED | self::CARRY | self::ZERO | self::SIGN),
		];
	}

}