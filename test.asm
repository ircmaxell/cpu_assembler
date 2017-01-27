start:
    MOV RA, 0x01;
    MOV RB, 0x02;
    CALL addRaRb;
    MOV RA, RB;
    HALT;

addRaRb:
    ADD RB, RB;
    RETURN;