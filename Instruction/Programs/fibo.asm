start:
	LOAD-I RA, 0x01;
	LOAD-I RB, 0x01;
loop:
	ADD RC, RB;
	JUMP-C start;
	MOV RA, RB;
	MOV RB, RC;
	JUMP loop;
