<?php

declare(strict_types=1);

namespace TinyComputer;

class MicroCode {

	const PC_O 		= 0b00100000;
	const SC_O 		= 0b01000000;
	const INCDEC_O 	= 0b01100000;
	const J_O		= 0b10000000;
	const x7FFF_O	= 0b11000000;
	const xC000_O 	= 0b11100000;
	const PC_W 		= 0b00000100;
	const SC_W 		= 0b00001000;
	const INC_W		= 0b00001100;
	const DEC_W 	= 0b00010000;
	const NEXT 		= 0b00000010;
	const INST_W	= 0b00000001;

	const ALU_ADD		= 0b00000000 << 8;
	const ALU_SUB		= 0b00001000 << 8;
	const ALU_INC		= 0b00010000 << 8;
	const ALU_DEC		= 0b00011000 << 8;
	const ALU_XOR		= 0b00100000 << 8;
	const ALU_NOT		= 0b00111000 << 8;
	const ALU_AND		= 0b01000000 << 8;
	const ALU_ZERO		= 0b01010000 << 8;
	const ALU_OR		= 0b01100000 << 8;
	const ALU_FF		= 0b01110000 << 8;
	const ALU_SELF		= 0b00110000 << 8;
	const ALU_O			= 0b00000100 << 8;
	const ALU_W			= 0b00000001 << 8;
	const ALU_FLAG_O	= 0b00000010 << 8;

	const J1_W	= 0b00000001 << 16;
	const J2_W	= 0b00000010 << 16;
	const MEM_O = 0b00000100 << 16;
	const MEM_W = 0b00001000 << 16;
	const RI_W 	= 0b00010000 << 16;
	const RIO_X	= 0b00100000 << 16;
	const RIW_X	= 0b01000000 << 16;
	const HALT 	= 0b10000000 << 16;



}