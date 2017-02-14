

start:
	CALL registerATest;
	CALL registerBTest;
	CALL registerCTest;
	CALL aluADDTest;
	CALL aluADDTest2;
	CALL aluADDTest3;
	CALL aluANDTest;
	CALL aluANDTest2;
	CALL aluORTest;
	CALL aluORTest2;
	CALL aluORTest3;
	CALL aluORTest4;
	CALL testPush;
	CALL reset;
	CALL fibo;

fibo:
	LOAD-I RA, 0x01;
	LOAD-I RB, 0x01;
fiboLoop:
	ADD RC, RB;
	JUMP-C fibo;
	MOV RA, RB;
	MOV RB, RC;
	JUMP fiboLoop;

pass:
	RETURN;

registerATest:
		LOAD-I RA, 0xFF;
		NOT RA, RA;
		JUMP-Z pass;
		HALT;

registerBTest:
		LOAD-I RB, 0xFF;
		NOT RB, RB;
		JUMP-Z pass;
		HALT;

registerCTest:
		LOAD-I RC, 0xFF;
		NOT RC, RC;
		JUMP-Z pass;
		HALT;

aluADDTest:	
		LOAD-I RA, 0x00;
	aluADDLoop:
		INC RA, RA;
		JUMP-NZ aluADDLoop; // Count up to 255
		JUMP-C pass;
		HALT;

aluADDTest2:
		LOAD-I RA, 0x0F;
		LOAD-I RB, 0xF0;
		ADD RC, RB;
		LOAD-I RA, 0xFF;
		XOR RC, RC;
		JUMP-Z pass;
		HALT;

aluADDTest3:
		LOAD-I RA, 0x01;
		LOAD-I RB, 0x01;
		ADD RC, RB;
		LOAD-I RA, 0x02;
		XOR RB, RC;
		JUMP-Z pass;
		HALT;

aluANDTest:
		LOAD-I RA, 0xFF;
		LOAD-I RB, 0x00;
		AND RC, RB;
		JUMP-Z pass;
		HALT;

aluANDTest2:
		LOAD-I RA, 0xFF; // A = 11111111
		LOAD-I RB, 0xFF; // B = 11111111
		AND RC, RB;   // C = A AND B
		NOT RC, RC;	  // C = NOT C
		JUMP-Z pass;
		HALT;

aluORTest:
		LOAD-I RA, 0x00;
		LOAD-I RB, 0x00;
		OR RC, RB;
		JUMP-Z pass;
		HALT;

aluORTest2:
		LOAD-I RA, 0xFF; // A = 11111111
		LOAD-I RB, 0xFF; // B = 11111111
		OR RC, RB;   // C = A OR B
		NOT RC, RC;	  // C = NOT C
		JUMP-Z pass;
		HALT;

aluORTest3:
		LOAD-I RA, 0x00; // A = 00000000
		LOAD-I RB, 0xFF; // B = 11111111
		OR RC, RB;   // C = A OR B
		NOT RC, RC;	  // C = NOT C
		JUMP-Z pass;
		HALT;

aluORTest4:
		LOAD-I RA, 0xFF; // A = 11111111
		LOAD-I RB, 0x00; // B = 00000000
		OR RC, RB;   // C = A OR B
		NOT RC, RC;	  // C = NOT C
		JUMP-Z pass;
		HALT;

testPush:
		LOAD-I RA, 0x01;
		LOAD-I RB, 0x02;
		PUSH RB;
		PUSH RA;
		POP RC;
		XOR RC, RC;
		JUMP-Z testPushNext;
		HALT;
	testPushNext:
		POP RC;
		MOV RA, RB;
		XOR RC, RC;
		JUMP-Z pass;
		HALT;		
reset:
		LOAD-I RA, 0xFF;
		LOAD-I RB, 0xFF;
		LOAD-I RC, 0xFF;
		LOAD-I RJ1, 0xFF;
		LOAD-I RJ2, 0xFF;
		RETURN;




