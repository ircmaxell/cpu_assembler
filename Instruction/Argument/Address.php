<?php

declare(strict_types=1);

namespace TinyComputer\Argument;
use TinyComputer\Argument;
use TinyComputer\Register;

class Address extends Argument {

	public function numberOfBytes(): int {
		return 2;
	}

	public function encode(array $args): string {
		$val = intval($args[0], 0);
		return self::encodeRaw($val);
	}

	public static function encodeRaw(int $arg): string {
		return chr($arg >> 8) . chr($arg);
	}

	public function decode(string $value): array {
		return [
			sprintf("0x%02X%02X", ord($value[0]), ord($value[1])),
		];
	}
}