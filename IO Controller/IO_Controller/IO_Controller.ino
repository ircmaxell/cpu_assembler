#include "IO_Controller.h"
#include <LiquidCrystal_I2C.h>

struct {
  LiquidCrystal_I2C main = LiquidCrystal_I2C(MAIN_LCD_I2C_ADDRESS, 40, 3);
  struct {
    uint8_t row = 0;
    uint8_t column = 0;
  } mainCursorPosition;
  LiquidCrystal_I2C secondary = LiquidCrystal_I2C(SECONDARY_LCD_I2C_ADDRESS, 16, 2);
  uint8_t secondaryLineBuffer[16];
  uint8_t secondaryLineBufferPosition = 0;
} displays;



volatile struct {
  byte portA = 0;
  byte portB = 0;
  byte portC = 0;
  byte portD = 0;
  byte portG = 0;
  byte portL = 0;
  bool needsProcessing = false;
} registerState;


void setup() {
  displays.main.begin();
  displays.secondary.begin();
  displays.main.backlight();
  displays.secondary.backlight();
  displays.main.noCursor();
  displays.secondary.noCursor();

  // bottom 8 address bits
  pinMode(22, INPUT);
  pinMode(24, INPUT);
  pinMode(26, INPUT);
  pinMode(28, INPUT);
  pinMode(30, INPUT);
  pinMode(32, INPUT);
  pinMode(34, INPUT);
  pinMode(36, INPUT);
  // top 8 address bits
  pinMode(38, INPUT);
  pinMode(40, INPUT);
  pinMode(42, INPUT);
  pinMode(44, INPUT);
  pinMode(46, INPUT);
  pinMode(48, INPUT);
  pinMode(50, INPUT);
  pinMode(52, INPUT);
  // data
  pinMode(23, INPUT);
  pinMode(25, INPUT);
  pinMode(27, INPUT);
  pinMode(29, INPUT);
  pinMode(31, INPUT);
  pinMode(33, INPUT);
  pinMode(35, INPUT);
  pinMode(37, INPUT);

  attachInterrupt(digitalPinToInterrupt(INTERRUPT_PIN), readInterrupt, RISING);
}

void loop() {
  // put your main code here, to run repeatedly:
  if (!registerState.needsProcessing) {
    return;
  }
  registerState.needsProcessing = false;
  address current = (ADDRESS_HIGH(registerState) << 8) | ADDRESS_LOW(registerState);
  byte data = DATA(registerState);
  if (current < 0x8000 || current > 0x807F) {
    return;
  }
  if (current == 0x807E) {
    if (data == 0x0A) {
      // line feed
      handleSecondaryLineFeed();
      return;
    } else if (displays.secondaryLineBufferPosition >= 15) {
      handleSecondaryLineFeed();
    }
    displays.secondary.write(data);
    displays.secondaryLineBuffer[displays.secondaryLineBufferPosition++] = data;
  } else if (current == 0x807F) {
    displays.secondary.clear();
  } else if (current < 0x8078) {
    writeMainDisplay(current - 0x8000, data);
  } else if (current == 0x8078) {
    displays.main.clear();
  } else if (current == 0x8079) {
    if (data) {
      displays.main.cursor();
      displays.main.blink();
    } else {
      displays.main.noCursor();
      displays.main.noBlink();
    }
  } else if (current == 0x807A) {
    displays.mainCursorPosition.row = decodeRow(data);
    displays.mainCursorPosition.column = decodeColumn(data);
    resetCursor();
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
  registerState.portA = PINA;
  registerState.portB = PINB;
  registerState.portC = PINC;
  registerState.portD = PIND;
  registerState.portG = PING;
  registerState.portL = PINL;
  registerState.needsProcessing = true;
}

