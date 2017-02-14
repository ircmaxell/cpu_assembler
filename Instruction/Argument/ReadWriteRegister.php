<?php

declare(strict_types=1);

namespace TinyComputer\Argument;
use TinyComputer\Argument;
use TinyComputer\Register;

class ReadWriteRegister extends Argument {

	public function numberOfArguments(): int {
		return 2;
	}

	public function encode(array $args): string {
		return chr(Register::encodeReadWrite(
			Register::fromString($args[1]), // Read is the second arg always
			Register::fromString($args[0])  // Write is the first arg always
		));
	}

	public function decode(string $value): array {
		$encoded = ord($value);
		return [
			Register::toString(Register::decodeWrite($encoded)),
			Register::toString(Register::decodeRead($encoded)),
		];
	}
}