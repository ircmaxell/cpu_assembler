<?php

declare(strict_types=1);

require __DIR__ . "/vendor/autoload.php";

$set = new TinyComputer\InstructionSet;
$assembler = new TinyComputer\Assembler($set);
$builder = new TinyComputer\ROMBuilder($set);

$roms = $builder->buildRom();

$helloWorld = "
start:
	MEMCPY-I 0x807E, 0x48;
	MEMCPY-I 0x807E, 0x65;
	MEMCPY-I 0x807E, 0x6C;
	MEMCPY-I 0x807E, 0x6C;
	MEMCPY-I 0x807E, 0x6F;
	MEMCPY-I 0x807E, 0x20;
	MEMCPY-I 0x807E, 0x57;
	MEMCPY-I 0x807E, 0x6F;
	MEMCPY-I 0x807E, 0x72;
	MEMCPY-I 0x807E, 0x6C;
	MEMCPY-I 0x807E, 0x64;
	MEMCPY-I 0x807E, 0x0A;
	JUMP start;
";

if (isset($_SERVER['argv'][1])) {
	if ($_SERVER['argv'][1] === '-') {
		$program = file_get_contents("php://stdin");
	} else {
		$program = file_get_contents($_SERVER['argv'][1]);
	}
} else {
	$program = $helloWorld;
}

$program = $assembler->assemble($program, empty($_SERVER['argv'][2]));

echo $assembler->disassemble($program, 0xC000);

$vm = new TinyComputer\VM($roms, $set, $program);

$vm->run();