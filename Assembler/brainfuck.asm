.offset 0xC000

boot:
    MOV RJ1, 0x00;
    MOV RJ2, 0x00;
    MOV RA, 0xD0;
    STORE RA;
    MOV RJ2, 0x01;
    MOV RA, 0x00;
    STORE RA;
    // byte 0x0000 is initialized to 0xD0
    // byte 0x0001 is initialized to 0x00
    // These are the "program counters" (high and low)
    MOV RJ2, 0x02;
    MOV RA, 0x00;
    STORE RA;
    MOV RJ2, 0x03;
    MOV RA, 0x10;
    STORE RA;
    // byte 0x0002 is initialized to 0x00
    // byte 0x0003 is initialized to 0x10
    // This is the "memory pointer"
    JMP run;

run:
	MOV J1, 0x00;
	MOV J2, 0x00;
	LOAD RB;
	MOV J2, 0x01;
	LOAD RC;
	MOV J1, RB;
	MOV J2, RC;
	LOAD RA;
	// Load the next instruction into RA
	CMP 0x2B; // +
	JMPZ add;
	CMP 0x2D; // -
	JMPZ sub;
	CMP 0x3C; // <
	JMPZ left;
	CMP 0x3E; // >
	JMPZ right;
	CMP 0x5B; // [
	JMPZ openLoop;
	CMP 0x5D; // ]
	JMPZ closeLoop;
	CMP 0x2E; // .
	JMPZ output;
	CMP 0x2C; // ,
	JMPZ input;
	CMO 0x00; // null byte
	HALT;
next:
	MOV J1, 0x00;
	MOV J2, 0x00;
	LOAD RB;
	MOV J2, 0x01;
	LOAD RC;
	INC RC, RC;
	JMPNC storeAddress;
	INC RB, RB;
storeAddress:
	MOV J1, 0x00;
	MOV J2, 0x00;
	STORE RB;
	MOV J2, 0x01;
	STORE RC;
	JMP run;

output:
	HALT;
input:
	HALT;

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
	JMP next;

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
	JMP next;

left:
	MOV J1, 0x00;
	MOV J2, 0x02;
	LOAD RB;
	MOV J2, 0x03;
	LOAD RC;
	DEC RC, RC;
	JMPNC storeLeftAddress;
	DEC RB, RB;
storeLeftAddress:
	MOV J1, 0x00;
	MOV J2, 0x02;
	STORE RB;
	MOV J2, 0x03;
	STORE RC;
	JMP next;

right:
	MOV J1, 0x00;
	MOV J2, 0x02;
	LOAD RB;
	MOV J2, 0x03;
	LOAD RC;
	INC RC, RC;
	JMPNC storeRightAddress;
	INC RB, RB;
storeRightAddress:
	MOV J1, 0x00;
	MOV J2, 0x02;
	STORE RB;
	MOV J2, 0x03;
	STORE RC;
	JMP next;

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
	JMPZ skipLoop;
	PUSH RC;
	PUSH RB;
	JMP next;

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
	JMPNC storeSkipAddress;
	INC RB, RB;
storeSkipAddress:
	MOV J1, RB;
	MOV J2, RC;
	LOAD RA;
	CMP 0x5B; // [
	JMPZ skipInnerLoop;
	CMP 0x5D; // ]
	JMPZ closeLoop;
	JMP skipNext;

skipInnerLoop:
	MOV J1, 0x00;
	MOV J2, 0x04;
	LOAD RA;
	INC RA, RA;
	STORE RA;
	JMP skipNext;

closeLoop:
	MOV J1, 0x00;
	MOV J2, 0x04;
	LOAD RA;
	CMP 0x00;
	JMPZ resumeFromLoop;
	DEC RA, RA;
	STORE RA;
	JMP skipNext;

resumeFromLoop:
	MOV J1, 0x00;
	MOV J2, 0x00;
	STORE RB;
	MOV J2, 0x01;
	STORE RC;
	JMP next;


closeLoop:
	POP J1;
	POP J2;
	JMP; // Indirect jump back to the outer loop