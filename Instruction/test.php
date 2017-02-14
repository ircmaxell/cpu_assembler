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

$fibo = "
start:
	LOAD-I RA, 0x01;
	LOAD-I RB, 0x01;
loop:
	ADD RC, RB;
	JUMP-C start;
	MOV RA, RB;
	MOV RB, RC;
	JUMP loop;
";

$program = $assembler->assemble($helloWorld);

echo $assembler->disassemble($program, 0xC000);

$vm = new TinyComputer\VM($roms, $set, $program);

$vm->run();