start:
    MOV RA, 249;


// 0x03 0x00 0x01 - MOV RA 0x01
// 0x03 0x10 0x02 - MOV RB 0x02
// 0x03 0x20 0x00 - MOV RC 0x00
// 0x07 0x02      - PUSH RC
// 0x03 0x20 0x13 - MOV RC 0x13
// 0x07 0x02      - PUSH RC
// 0x30 0x00 0x16 - JMP 0x16
// 0x01 0x01      - MOV RA, RB
// 0x00           - HALT
// 0x10 0x11      - ADD RB, RB
// 0x08 0x40      - POP RJ2
// 0x08 0x30      - POP RJ1
// 0x31           - JMP-I
// 0x00           - HALT