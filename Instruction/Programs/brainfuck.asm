boot:

    LOAD-I RA, 0xD0;
    STORE-I RA, 0x0000;
    LOAD-I RA, 0x00;
    STORE-I RA, 0x0001;
    // byte 0x0000 is initialized to 0xD0
    // byte 0x0001 is initialized to 0x00
    // These are the "program counters" (high and low)
    STORE-I RA, 0x0002;
    LOAD-I RA, 0x10;
    STORE-I RA, 0x0003;
    // byte 0x0002 is initialized to 0x00
    // byte 0x0003 is initialized to 0x10
    // This is the "memory pointer"
    JUMP- run;

run:
	LOAD16 RB, RC, 0x0000;
	MOV J1, RB;
	MOV J2, RC;
	LOAD RA;
	// Load the next instruction into RA
	CMP 0x2B; // +
	JUMP-Z add;
	CMP 0x2D; // -
	JUMP-Z sub;
	CMP 0x3C; // <
	JUMP-Z left;
	CMP 0x3E; // >
	JUMP-Z right;
	CMP 0x5B; // [
	JUMP-Z openLoop;
	CMP 0x5D; // ]
	JUMP-Z closeLoop;
	CMP 0x2E; // .
	JUMP-Z output;
	CMP 0x2C; // ,
	JUMP-Z input;
	CMP 0x00; // null byte
	JUMP-Z _halt;

	// Fall through intentional
next:
	LOAD16 RB, RC, 0x0000;
	INC16 RC, RB;
	MOV J1, 0x00;
	MOV J2, 0x00;
	STORE-I RB, 0x0000;
	STORE-I RC, 0x0001;
	JUMP run;

_halt:
	HALT;

output:
	LOAD16 RB, RC, 0x0002;
	LOAD-M RA, RB, RC; 
	STORE-I RA, 0x807e;
	JUMP run;
input:
	HALT;

// TODO: stopped here

add:
	MOV J1, 0x00;
	MOV J2, 0x02;
	LOAD RB;
	MOV J2, 0x03;
	LOAD RC;
	MOV J1, RB;
	MOV J2, RC;
	LOAD RA;
	INC RA, RA;
	STORE RA;
	JUMP- next;

sub:
	MOV J1, 0x00;
	MOV J2, 0x02;
	LOAD RB;
	MOV J2, 0x03;
	LOAD RC;
	MOV J1, RB;
	MOV J2, RC;
	LOAD RA;
	DEC RA, RA;
	STORE RA;
	JUMP- next;

left:
	MOV J1, 0x00;
	MOV J2, 0x02;
	LOAD RB;
	MOV J2, 0x03;
	LOAD RC;
	DEC RC, RC;
	JUMP-NC storeLeftAddress;
	DEC RB, RB;
storeLeftAddress:
	MOV J1, 0x00;
	MOV J2, 0x02;
	STORE RB;
	MOV J2, 0x03;
	STORE RC;
	JUMP- next;

right:
	MOV J1, 0x00;
	MOV J2, 0x02;
	LOAD RB;
	MOV J2, 0x03;
	LOAD RC;
	INC RC, RC;
	JUMP-NC storeRightAddress;
	INC RB, RB;
storeRightAddress:
	MOV J1, 0x00;
	MOV J2, 0x02;
	STORE RB;
	MOV J2, 0x03;
	STORE RC;
	JUMP- next;

openLoop:
	MOV J1, 0x00;
	MOV J2, 0x02;
	LOAD RB;
	MOV J2, 0x03;
	LOAD RC;
	MOV J1, RB;
	MOV J2, RC;
	LOAD RA;
	CMP 0x00;
	JUMP-Z skipLoop;
	PUSH RC;
	PUSH RB;
	JUMP- next;

skipLoop:
	MOV RA, 0x00;
	MOV J1, 0x00;
	MOV J2, 0x04;
	STORE RA;
	MOV J1, 0x00;
	MOV J2, 0x00;
	LOAD RB;
	MOV J2, 0x01;
	LOAD RC;
skipNext:
	INC RC, RC;
	JUMP-NC storeSkipAddress;
	INC RB, RB;
storeSkipAddress:
	MOV J1, RB;
	MOV J2, RC;
	LOAD RA;
	CMP 0x5B; // [
	JUMP-Z skipInnerLoop;
	CMP 0x5D; // ]
	JUMP-Z closeLoop;
	JUMP- skipNext;

skipInnerLoop:
	MOV J1, 0x00;
	MOV J2, 0x04;
	LOAD RA;
	INC RA, RA;
	STORE RA;
	JUMP- skipNext;

closeLoop:
	MOV J1, 0x00;
	MOV J2, 0x04;
	LOAD RA;
	CMP 0x00;
	JUMP-Z resumeFromLoop;
	DEC RA, RA;
	STORE RA;
	JUMP- skipNext;

resumeFromLoop:
	MOV J1, 0x00;
	MOV J2, 0x00;
	STORE RB;
	MOV J2, 0x01;
	STORE RC;
	JUMP- next;


closeLoop:
	POP J1;
	POP J2;
	JUMP-; // Indirect jump back to the outer loop



.data
	program db '++++++++++[>+++++++>++++++++++>+++>+<<<<-]>++.>+.+++++++..+++.>++.<<+++++++++++++++.>.+++.------.--------.>+.>.';
	program_len equ $ - program;