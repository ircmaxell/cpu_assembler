// This is a self-test, similar to a POST (Power-On Self Test)

registerATest:
		// Load all 1's into RA
		MOV RA, 0xFF;
		// Compute the NOT of RA
		NOT RA, RA;
		// If ~A is 0 (passing) go to B test
		JMPZ registerBTest;
		// We failed the test, halt here (memory address indicates error)
		HALT;
registerBTest:
		MOV RB, 0xFF;
		NOT RB, RB;
		JMPZ registerCTest;
		HALT;
registerCTest:
		MOV RC, 0xFF;
		NOT RC, RC;
		JMPZ aluADDTest;
		HALT;
aluADDTest:	// Ensures it can count all the way up through carry
		MOV RA, 0x00;
	aluADDLoop:
		INC RA, RA;
		JMPNZ aluADDLoop; // Count up to 255
		JMPC aluADDTest2;
		HALT;
aluADDTest2:
		MOV RA, 0x0F;
		MOV RB, 0xF0;
		ADD RC, RB;
		MOV RA, 0xFF;
		XOR RC, RC;
		JMPZ aluADDTest3;
		HALT;
aluADDTest3:
		MOV RA, 0x01;
		MOV RB, 0x01;
		ADD RC, RB;
		MOV RA, 0x02;
		XOR RB, RC;
		JMPZ aluANDTest;
		HALT;
aluANDTest:
		MOV RA, 0xFF;
		MOV RB, 0x00;
		AND RC, RB;
		JMPZ aluANDTest2;
		HALT;
aluANDTest2:
		MOV RA, 0xFF; // A = 11111111
		MOV RB, 0xFF; // B = 11111111
		AND RC, RB;   // C = A AND B
		NOT RC, RC;	  // C = NOT C
		JMPZ aluORTest;
		HALT;
aluORTest:
		MOV RA, 0x00;
		MOV RB, 0x00;
		OR RC, RB;
		JMPZ aluORTest2;
		HALT;
aluORTest2:
		MOV RA, 0xFF; // A = 11111111
		MOV RB, 0xFF; // B = 11111111
		OR RC, RB;   // C = A OR B
		NOT RC, RC;	  // C = NOT C
		JMPZ aluORTest3;
		HALT;
aluORTest3:
		MOV RA, 0x00; // A = 00000000
		MOV RB, 0xFF; // B = 11111111
		OR RC, RB;   // C = A OR B
		NOT RC, RC;	  // C = NOT C
		JMPZ aluORTest4;
		HALT;
aluORTest4:
		MOV RA, 0xFF; // A = 11111111
		MOV RB, 0x00; // B = 00000000
		OR RC, RB;   // C = A OR B
		NOT RC, RC;	  // C = NOT C
		JMPZ testPush;
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
		JMPZ end;
		HALT;		
end:
		MOV RA, 0xFF;
		MOV RB, 0xFF;
		MOV RC, 0xFF;
		MOV RJ1, 0xFF;
		MOV RJ2, 0xFF;
		HALT;




