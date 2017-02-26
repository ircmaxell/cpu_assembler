start:
	MEMCPY-I 0x8083, 0x62; // Filename[] = 'b'
	MEMCPY-I 0x8083, 0x6F; // Filename[] = 'o'
	MEMCPY-I 0x8083, 0x6F; // Filename[] = 'o'
	MEMCPY-I 0x8083, 0x74; // Filename[] = 't'
	MEMCPY-I 0x8084, 0x00; // OPEN
	CALL notReady; // Wait until we're ready and have an open and available sd file
	LOAD-16 RM1, RM2, 0x0000;

next-byte:
	LOAD-16 RJ1, RJ2, 0x8085;
	LOAD RA;
	CMP-I 0;
	JUMP-Z loaded;
	LOAD-M RB, RM1, RM2;
	MOV RJ1, RM1;
	MOV RJ2, RM2;
	STORE RB;
	INC-16 RM2, RM2, RM1, RM1;
	JUMP next-byte;

loaded:
	MEMCPY-I 0x8082, 0x00; // Close the booted file, resetting the SD card
	RESET; // clear the stack
	JUMP 0x0000; // Jump to the beginning of the loaded program

delay:
	LOAD-I RA, 0xFF;
	delayLoopBody:
		DEC RA, RA;
		JUMP-NZ delayLoopBody; // Loop through 255 cycles to give SD card a chance to boot
	RETURN;

notReady:
	LOAD-16 RJ1, RJ2, 0x8084;
	LOAD RA;
	CMP-I 0;
	JUMP-NZ ready;
	CALL delay;
	LOAD-16 RJ1, RJ2, 0x8084;
	LOAD RA;
	CMP-I 0;
	JUMP-NZ ready;
	HALT; // didn't get ready in time, abort

ready:
	RETURN;