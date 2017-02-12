.offset 0xC000
start:
    MOV RA, 0x01;
    MOV RB, 0x10;
    MOV RC, 0xFF;
    MOV RJ1, 0x55;
    MOV RJ2, 0xAA;
    HALT;