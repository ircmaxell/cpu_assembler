<?php

declare(strict_types=1);

require __DIR__ . "/vendor/autoload.php";

$set = new TinyComputer\InstructionSet;
$builder = new TinyComputer\ROMBuilder($set);

$roms = $builder->buildRom();

foreach ($roms as $name => $rom) {
	$result = ['', ''];
	$chunks = str_split($rom, 1 << 14);
	foreach ($chunks as $key => $chunk) {
		$len = strlen($chunk);
		for ($i = 0; $i < $len; $i++) {
			$result[$key] .= sprintf("0x%02X, ", ord($chunk[$i]));
		}
	}
	file_put_contents(__DIR__ . '/Results/' . $name . '.low.rom', $result[0]);
	file_put_contents(__DIR__ . '/Results/' . $name . '.high.rom', $result[1]);
	
}