<?php

declare(strict_types=1);

require __DIR__ . "/vendor/autoload.php";

$set = new TinyComputer\InstructionSet;
$assembler = new TinyComputer\Assembler($set);

if ($_SERVER['argc'] < 2) {
	die("Required: filename argument\n");
}


if ($_SERVER['argv'][1] === '-') {
	// get from stdin
	$code = file_get_contents("php://stdin");
} else {
	$code = file_get_contents($_SERVER['argv'][1]);
}

$offset = $_SERVER['argc'] > 2 ? intval($_SERVER['argv'][2], 0) : 0;

if (substr($code, 0, 2) === "0x") {
	$code = eval("return [" . $code . "];");
	$code = implode("", array_map("chr", $code));
}

$decoded = $assembler->disassemble($code, $offset);

echo $decoded . "\n";