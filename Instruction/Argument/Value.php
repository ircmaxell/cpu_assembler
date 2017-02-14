<?php

declare(strict_types=1);

namespace TinyComputer\Argument;
use TinyComputer\Argument;
use TinyComputer\Register;

class Value extends Argument {

	public function encode(array $args): string {
		return chr(intval($args[0], 0) & 0xFF);
	}

	public function decode(string $value): array {
		return [
			sprintf("0x%02X", ord($value)),
		];
	}
}