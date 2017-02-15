#include <Adafruit_MCP23017.h>
#include "IO_Controller.h"
#include <LiquidCrystal_I2C.h>

struct {
  LiquidCrystal_I2C main = LiquidCrystal_I2C(MAIN_LCD_I2C_ADDRESS, 20, 4);
  struct {
    uint8_t row = 0;
    uint8_t column = 0;
  } mainCursorPosition;
  LiquidCrystal_I2C secondary = LiquidCrystal_I2C(SECONDARY_LCD_I2C_ADDRESS, 16, 2);
  uint8_t secondaryLineBuffer[16];
  uint8_t secondaryLineBufferPosition = 0;
} displays;

struct {
  Adafruit_MCP23017 a;
  Adafruit_MCP23017 b;
} bus;

volatile bool needsProcessing = false;
volatile bool needsDebugging = false;

void setup() {

  bus.a.begin(1);
  bus.b.begin(2);

  for (uint8_t i = 0; i < 16; i++) {
    bus.a.pinMode(i, INPUT);
    bus.b.pinMode(i, INPUT);
  }

  displays.main.begin();
  displays.secondary.begin();
  displays.main.backlight();
  displays.secondary.backlight();
  displays.main.noCursor();
  displays.secondary.noCursor();

  displays.main.clear();
  writeMain(String("Address: 0x0000"));
  displays.main.setCursor(0, 1);
  writeMain(String("Address:       "));
  displays.main.setCursor(0, 2);
  writeMain(String("Data: 0x00"));
  displays.main.setCursor(0, 3);
  writeMain(String("Data:     "));

  pinMode(READ_INTERRUPT_PIN, INPUT);
  pinMode(WRITE_INTERRUPT_PIN, INPUT);

  attachInterrupt(digitalPinToInterrupt(READ_INTERRUPT_PIN), readInterrupt, RISING);
  attachInterrupt(digitalPinToInterrupt(WRITE_INTERRUPT_PIN), writeInterrupt, RISING);
}

void loop() {
  // put your main code here, to run repeatedly:
  if (!needsProcessing && !needsDebugging) {
    return;
  }
  bool isWrite = needsProcessing;
  needsProcessing = false;
  needsDebugging = false;
  
  address addr = (bus.b.readGPIO(0) << 8) | bus.a.readGPIO(0);
  byte data = reverse(bus.a.readGPIO(1));

  if (isWrite && addr == 0x807E) {
    if (data == 0x0A) {
      // line feed
      handleSecondaryLineFeed();
      return;
    } else if (isWrite && displays.secondaryLineBufferPosition >= 15) {
      handleSecondaryLineFeed();
    }
    displays.secondary.write(data);
    displays.secondaryLineBuffer[displays.secondaryLineBufferPosition++] = data;
  } else if (isWrite && addr == 0x807F) {
    displays.secondary.clear();
  }

  debug(addr, data);
}

void debug(address addr, byte data) {
  size_t i;
  String addrHex = String(addr, HEX);
  String addrDec = String(addr, DEC);
  String dataHex = String(data, HEX);
  String dataDec = String(data, DEC);

  displays.main.setCursor(11, 0);
  writePaddedMain(addrHex, 4, '0');

  displays.main.setCursor(9, 1);
  writePaddedMain(addrDec, 6, ' ');
 
  
  displays.main.setCursor(8, 2);
  writePaddedMain(dataHex, 2, '0');
  
  displays.main.setCursor(6, 3);
  writePaddedMain(dataDec, 4, ' ');
}

byte reverse(byte b) {
  byte r = (b & 0xF0) >> 4 | (b & 0x0F) << 4;
  r = (r & 0xCC) >> 2 | (r & 0x33) << 2;
  return (r & 0xAA) >> 1 | (r & 0x55) << 1;
}

void writePaddedMain(String str, size_t pad_len, const char pad) {
  switch (pad_len - str.length()) {
    case 6:
      displays.main.write(pad);
    case 5:
      displays.main.write(pad);
    case 4:
      displays.main.write(pad);
    case 3:
      displays.main.write(pad);
    case 2:
      displays.main.write(pad);
    case 1:
      displays.main.write(pad);
    default:
      writeMain(str);
  }
}

void writeMain(String str) {
  size_t len = str.length();
  for (size_t i = 0; i < len; i++) {
    displays.main.write(str.charAt(i));
  }
}

void handleSecondaryLineFeed() {
  displays.secondary.clear();
  for (int i = 0; i < displays.secondaryLineBufferPosition; i++) {
    displays.secondary.write(displays.secondaryLineBuffer[i]);
  }
  displays.secondary.setCursor(0, 1);
  displays.secondaryLineBufferPosition = 0;
}

void resetCursor() {
  displays.main.setCursor(displays.mainCursorPosition.column, displays.mainCursorPosition.row);
}

uint8_t decodeRow(uint8_t coded) {
  return floor(coded / 40);
}

uint8_t decodeColumn(uint8_t coded) {
  return coded % 40;
}

void writeMainDisplay(uint8_t coded, byte data) {
  displays.main.setCursor(decodeColumn(coded), decodeRow(coded));
  displays.main.write(data);
  resetCursor();
}


void readInterrupt() {
  needsDebugging = true;
}

void writeInterrupt() {
  needsProcessing = true;
}

