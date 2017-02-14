<?php

declare(strict_types=1);

require __DIR__ . "/vendor/autoload.php";

$set = new TinyComputer\InstructionSet;
$assembler = new TinyComputer\Assembler($set);
$builder = new TinyComputer\ROMBuilder($set);

if ($_SERVER['argc'] < 2) {
	die("Required: filename argument\n");
}

$code = file_get_contents($_SERVER['argv'][1]);

$encoded = $assembler->assemble($code);

$result = '';
$len = strlen($encoded);
for ($i = 0; $i < $len; $i++) {
	$result .= sprintf("0x%02X, ", ord($encoded[$i]));
}

echo $result . "\n";