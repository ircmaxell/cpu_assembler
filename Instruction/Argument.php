<?php

declare(strict_types=1);

namespace TinyComputer;

abstract class Argument {

	public function numberOfBytes(): int {
		return 1;
	}

	public function numberOfArguments(): int {
		return 1;
	}

	abstract public function encode(array $args): string;
	abstract public function decode(string $value): array;
}