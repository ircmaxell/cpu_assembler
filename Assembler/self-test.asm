// This is a self-test, similar to a POST (Power-On Self Test)

// This lives in ROM, so we need to offset by 0xE000
.offset 0xE000

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
	MOV RA, 0x01;
	MOV RB, 0x01;
fiboLoop:
	ADD RC, RB;
	JMPC fibo;
	MOV RA, RB;
	MOV RC, RB;
	JMP fiboLoop;

pass:
	RETURN;

registerATest:
		// Load all 1's into RA
		MOV RA, 0xFF;
		// Compute the NOT of RA
		NOT RA, RA;
		// If ~A is 0 (passing) go to B test
		JMPZ pass;
		// We failed the test, halt here (memory address indicates error)
		HALT;

registerBTest:
		MOV RB, 0xFF;
		NOT RB, RB;
		JMPZ pass;
		HALT;

registerCTest:
		MOV RC, 0xFF;
		NOT RC, RC;
		JMPZ pass;
		HALT;

aluADDTest:	// Ensures it can count all the way up through carry
		MOV RA, 0x00;
	aluADDLoop:
		INC RA, RA;
		JMPNZ aluADDLoop; // Count up to 255
		JMPC pass;
		HALT;

aluADDTest2:
		MOV RA, 0x0F;
		MOV RB, 0xF0;
		ADD RC, RB;
		MOV RA, 0xFF;
		XOR RC, RC;
		JMPZ pass;
		HALT;

aluADDTest3:
		MOV RA, 0x01;
		MOV RB, 0x01;
		ADD RC, RB;
		MOV RA, 0x02;
		XOR RB, RC;
		JMPZ pass;
		HALT;

aluANDTest:
		MOV RA, 0xFF;
		MOV RB, 0x00;
		AND RC, RB;
		JMPZ pass;
		HALT;

aluANDTest2:
		MOV RA, 0xFF; // A = 11111111
		MOV RB, 0xFF; // B = 11111111
		AND RC, RB;   // C = A AND B
		NOT RC, RC;	  // C = NOT C
		JMPZ pass;
		HALT;

aluORTest:
		MOV RA, 0x00;
		MOV RB, 0x00;
		OR RC, RB;
		JMPZ pass;
		HALT;

aluORTest2:
		MOV RA, 0xFF; // A = 11111111
		MOV RB, 0xFF; // B = 11111111
		OR RC, RB;   // C = A OR B
		NOT RC, RC;	  // C = NOT C
		JMPZ pass;
		HALT;

aluORTest3:
		MOV RA, 0x00; // A = 00000000
		MOV RB, 0xFF; // B = 11111111
		OR RC, RB;   // C = A OR B
		NOT RC, RC;	  // C = NOT C
		JMPZ pass;
		HALT;

aluORTest4:
		MOV RA, 0xFF; // A = 11111111
		MOV RB, 0x00; // B = 00000000
		OR RC, RB;   // C = A OR B
		NOT RC, RC;	  // C = NOT C
		JMPZ pass;
		HALT;

testPush:
		MOV RA, 0x01;
		MOV RB, 0x02;
		PUSH RB;
		PUSH RA;
		POP RC;
		XOR RC, RC;
		JMPZ testPushNext;
		HALT;
	testPushNext:
		POP RC;
		MOV RA, RB;
		XOR RC, RC;
		JMPZ pass;
		HALT;		
reset:
		MOV RA, 0xFF;
		MOV RB, 0xFF;
		MOV RC, 0xFF;
		MOV RJ1, 0xFF;
		MOV RJ2, 0xFF;
		RETURN;




