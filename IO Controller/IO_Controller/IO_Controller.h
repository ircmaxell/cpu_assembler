
#define INTERRUPT_PIN 19

#define MAIN_LCD_I2C_ADDRESS 0x20
#define SECONDARY_LCD_I2C_ADDRESS 0x3f

#define ADDRESS_LOW(r) (ADDRESS_00(r) | ADDRESS_01(r) | ADDRESS_02(r) | ADDRESS_03(r) | ADDRESS_04(r) | ADDRESS_05(r) | ADDRESS_06(r) | ADDRESS_07(r))
#define ADDRESS_HIGH(r) (ADDRESS_08(r) | ADDRESS_09(r) | ADDRESS_10(r) | ADDRESS_11(r) | ADDRESS_12(r) | ADDRESS_13(r) | ADDRESS_14(r) | ADDRESS_15(r))

// pin22
#define ADDRESS_00(r) (r.portA & 0x01)
// pin24
#define ADDRESS_01(r) ((r.portA & 0x04) >> 1)
// pin26
#define ADDRESS_02(r) ((r.portA & 0x10) >> 2)
// pin28
#define ADDRESS_03(r) ((r.portA & 0x40) >> 3)
// pin30
#define ADDRESS_04(r) ((r.portC & 0x80) >> 3)
// pin32
#define ADDRESS_05(r) (r.portC & 0x20)
// pin34
#define ADDRESS_06(r) ((r.portC & 0x08) << 3)
// pin36
#define ADDRESS_07(r) ((r.portC & 0x02) << 6)

// pin38
#define ADDRESS_08(r) ((r.portD & 0x80) >> 7)
// pin40
#define ADDRESS_09(r) (r.portG & 0x02)
// pin42
#define ADDRESS_10(r) ((r.portL & 0x80) >> 5)
// pin44
#define ADDRESS_11(r) ((r.portL & 0x20) >> 2)
// pin46
#define ADDRESS_12(r) ((r.portL & 0x08) << 1)
// pin48
#define ADDRESS_13(r) ((r.portL & 0x02) << 4)
// pin50
#define ADDRESS_14(r) ((r.portB & 0x08) << 3)
// pin52
#define ADDRESS_15(r) ((r.portB & 0x02) << 6)

#define DATA(r) (DATA_0(r) | DATA_1(r) | DATA_2(r) | DATA_3(r) | DATA_4(r) | DATA_5(r) | DATA_7(r) | DATA_7(r))

// pin23
#define DATA_0(r) ((r.portA & 0x02) >> 1)
// pin25
#define DATA_1(r) ((r.portA & 0x08) >> 2)
// pin27
#define DATA_2(r) ((r.portA & 0x20) >> 3)
// pin29
#define DATA_3(r) ((r.portA & 0x80) >> 4)
// pin31
#define DATA_4(r) ((r.portC & 0x40) >> 2)
// pin33
#define DATA_5(r) ((r.portC & 0x10) << 1)
// pin35
#define DATA_6(r) ((r.portC & 0x04) << 4)
// pin37
#define DATA_7(r) ((r.portC & 0x01) << 7)





typedef unsigned short address;


