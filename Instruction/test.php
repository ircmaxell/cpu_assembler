<?php

declare(strict_types=1);

require __DIR__ . "/vendor/autoload.php";

$set = new TinyComputer\InstructionSet;
$assembler = new TinyComputer\Assembler($set);
$builder = new TinyComputer\ROMBuilder($set);

$builder->buildRom();

$code = "

start:
	LOAD-I RA, 0x01;
	LOAD-I RB, 0x01;
	CALL next;

next:
	ADD RC, RB;
	JUMP-C start;
	MOV RA, RB;
	MOV RB, RC;
	JUMP next;
	RETURN;
	HALT;

.bss
	vara: resb 1;
	varb: resb 2;

.rodata
    foo db 'this is a test', 0x0A;
    len equ $ - foo;

";


$encoded = $assembler->assemble($code);

var_dump(bin2hex($encoded));

var_dump($assembler->disassemble($encoded));