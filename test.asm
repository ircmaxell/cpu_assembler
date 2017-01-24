start:	MOV RA, 1;
		MOV RB, 1;
j1:		ADD RC, RB;
		MOV RA, RB;
		MOV RB, RC;
		JMPZ start;
		JMP j1;
		HALT;