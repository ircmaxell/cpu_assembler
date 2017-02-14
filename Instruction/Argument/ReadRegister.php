<?php

declare(strict_types=1);

namespace TinyComputer\Argument;
use TinyComputer\Argument;
use TinyComputer\Register;

class ReadRegister extends Argument {

	public function encode(array $args): string {
		return chr(Register::encodeRead(
			Register::fromString($args[0]) 
		));
	}

	public function decode(string $value): array {
		$encoded = ord($value);
		return [
			Register::toString(Register::decodeRead($encoded))
		];
	}
}