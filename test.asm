start:	mov RA, 1;
		mov RB, 1;
j1:		add RC, RB;
		mov RA, RB;
		mov RB, RC;
		jmpz start;
		jmp j1;
		halt;



// 0x03 0x00 0x01 
// 0x03 0x10 0x01 
// 0x10 0x21 
// 0x01 0x01 
// 0x01 0x12 
// 0x22 0x00 0x00 
// 0x20 0x00 0x06 
// 0x00 
// 0x00
