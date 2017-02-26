<?php

declare(strict_types=1);

namespace TinyComputer;

class Register {
	const RA = 0x0;
	const RB = 0x1;
	const RC = 0x2;
	const RJ1 = 0x3;
	const RJ2 = 0x4;
	const RM1 = 0x5;
	const RM2 = 0x6;

	const LOOKUP = [
		self::RA => "RA",
		self::RB => "RB",
		self::RC => "RC",
		self::RJ1 => "RJ1",
		self::RJ2 => "RJ2",
		self::RM1 => "RM1",
		self::RM2 => "RM2",
	];

	const REVERSE_LOOKUP = [
		"RA" => self::RA,
		"RB" => self::RB,
		"RC" => self::RC,
		"RJ1" => self::RJ1,
		"RJ2" => self::RJ2,
		"RM1" => self::RM1,
		"RM2" => self::RM2,
	];

	public static function encodeRead(int $register): int {
		return $register & 0xF;
	}

	public static function encodeWrite(int $register): int {
		return ($register & 0xF) << 4;
	}

	public static function encodeReadWrite(int $read, int $write): int {
		return self::encodeRead($read) | self::encodeWrite($write);
	}

	public static function decodeRead(int $encoded): int {
		return $encoded & 0xF;
	}

	public static function decodeWrite(int $encoded): int {
		return ($encoded & 0xF0) >> 4;
	}

	public static function toString(int $register): string {
		return self::LOOKUP[$register] ?? 'unknown';
	}

	public static function fromString(string $name): int {
		return self::REVERSE_LOOKUP[$name] ?? -1;
	}
}