start:
	LOAD-I RJ1, 0x80;
	LOAD-I RJ2, 0x80;
	LOAD RA;
	CMP-I 0x00;
	JUMP-Z start;
	LOAD-I RJ1, 0x80;
	LOAD-I RJ2, 0x81;
	LOAD RB;
	STORE-I RB, 0x807E;
	JUMP start;