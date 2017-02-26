#include <SPI.h>
#include <SD.h>
#include "SPI_USART.h"

File fd;

char filename[128] = "";
char fileIndex = 0;

word seekAddress = 0;

const uint8_t chipSelect = 8;
const uint8_t cardDetect = 9;
const uint8_t clk2Pin = 4;
const uint8_t chipSelect2Pin = 5;

const uint8_t load8Pin = 3;
const uint8_t output8Pin = 6;
const uint8_t load16Pin = A0;

#define DATA_OUTPUT_ON() (PORTD &= ~_BV(6))
#define DATA_OUTPUT_OFF() (PORTD |= _BV(6))

#define DISABLE_LOAD() (PORTD &= ~_BV(3))
#define ENABLE_LOAD() (PORTD |= _BV(3))

#define DISABLE_ADDR_LOAD() (PORTC |= _BV(0))
#define ENABLE_ADDR_LOAD() (PORTC &= ~_BV(0))

const uint8_t readPin = A5;
const uint8_t writePin = A4;

#define READ_FLAG()  B00100000
#define WRITE_FLAG() B00010000

#define READ_ENABLED()  (PINC & READ_FLAG())
#define WRITE_ENABLED() (PINC & WRITE_FLAG())
#define READ_OR_WRITE_ENABLED() (PINC & (READ_FLAG() | WRITE_FLAG()))

#define WAIT_UNTIL(c) while(!c) {}

#define WAIT_UNTIL_NOT(c) while(c) {}

enum states : uint8_t {
  UNINITIALIZED,
  READY,
  NORMAL,
};
states state = UNINITIALIZED;

typedef struct busData {
  word address;
  byte data;
} busData;

void setup() {
  pinMode(cardDetect, INPUT);
  pinMode(readPin, INPUT);
  pinMode(writePin, INPUT);
  pinMode(clk2Pin, OUTPUT);

  pinMode(load8Pin, OUTPUT);
  pinMode(load16Pin, OUTPUT);
  pinMode(output8Pin, OUTPUT);
  
  digitalWrite(load8Pin, HIGH);
  digitalWrite(load16Pin, LOW);
  
  digitalWrite(output8Pin, HIGH);


  SPIUsart.begin(SPIUSART0, chipSelect2Pin);
  SPIUsart.beginTransaction(SPIUSART0, SPIUsartSettings(4000000000,MSBFIRST, SPI_MODE0));
//  initializeCard();
//  resetSD();
}

void loop() {
  start:
  switch (READ_OR_WRITE_ENABLED()) {
    case READ_FLAG():
      readCMD(getAddress());
      goto start;
    case WRITE_FLAG():
      writeCMD(readWriteBus());
      goto start;
    default:
      goto start;
  }
  
}

inline busData readWriteBus(busData data) {
  busData result = {0, 0};
  DISABLE_ADDR_LOAD();
  result = SPIUsart.transferRev(SPIUSART0, data);
  ENABLE_ADDR_LOAD();
  return result;
}

inline word getAddress() {
  busData data = {0, 0};
  data = readWriteBus(data);
  return data.address;
}

inline byte readFromDataBus() {
  busData data = {0, 0};
  data = readWriteBus(data);
  return data.data;
}

inline void dataBusWrite(byte data) {
  busData toWrite = {0, 0};
  toWrite.data = data;
  
  readWriteBus(toWrite);
  DISABLE_LOAD();
  DATA_OUTPUT_ON();
  WAIT_UNTIL_NOT(READ_ENABLED());
  DATA_OUTPUT_OFF();
  ENABLE_LOAD();
}

inline void writeCMD(busData bus) {
  byte data;
  byte next = bus.data;
  while (WRITE_ENABLED());
    data = next;
    next = readFromDataBus(); // We always ditch the data sampled between when write is high and when it drops low
  } while (WRITE_ENABLED());
  
  switch (bus.address) {
    case 0x8082:
      resetSD();
      break;
    case 0x8083:
      setFilename(data);
      break;
    case 0x8084:
      openSD();
      break;
    case 0x8086:
      writeSD(data);
      break;
    case 0x8087:
      seekHigh(data);
      break;
    case 0x8088:
      seekLow(data);
      break;
  }
}

inline void readCMD(word address) {
  byte data = 0x00;
  bool writeValid = false;
  switch (address) {
    case 0x8084:
      data = state == NORMAL ? 0xFF : 0x00;
      break;
    case 0x8085:
      data = availableSD() ? 0xFF : 0x00;
      break;
    case 0x8086:
      data = readSD();
      break;
    case 0x8087:
      data = (seekAddress & 0xFF00) >> 8;
      break;
    case 0x8088:
      data = seekAddress & 0xFF;
      break;
    default:
      WAIT_UNTIL_NOT(READ_ENABLED());
      return;
  }
  dataBusWrite(data);
}


void resetSD() {
  if (fd) {
    fd.close();
  }
  for (size_t i = 0; i < sizeof(filename); i++) {
    filename[i] = 0;
  }
  fileIndex = 0;
  seekAddress = 0;
  if (state == NORMAL) {
    state = READY;
  }
}

void setFilename(byte data) {
  if (fileIndex < sizeof(filename) - 1) {
    filename[fileIndex++] = (char) data;
  }
  // ignore longer filenames
}

void openSD() {
  if (state == NORMAL) {
    resetSD();
  }
  if (SD.exists(filename)) {
    fd = SD.open(filename, FILE_WRITE);
    if (fd) {
      fd.seek(0);
      fd.peek(); // needed to fill the read buffer
      state = NORMAL;
    }
  }
}

bool availableSD() {
  if (state != NORMAL) {
    return false;
  }
  return 0 < fd.available();
}

byte readSD() {
  if (state != NORMAL) {
    return 0x00;
  }
  seekAddress++;
  return (byte) fd.read();
}

void writeSD(byte data) {
  if (state != NORMAL) {
    return;
  }
  fd.write(data);
}

void seekHigh(byte data) {
  seekAddress = (data << 8) & (seekAddress & 0xFF);
}

void seekLow(byte data) {
  seekAddress = (seekAddress & 0xFF00) & data;
  if (state != NORMAL) {
      return;
  }
  fd.seek(seekAddress);
  fd.peek();
}

void initializeCard() {
  while (!digitalRead(cardDetect)) {
    delay(250);
  }

  if (state == UNINITIALIZED && !SD.begin(chipSelect)) {
    initializeCard();
  }

  state = READY;
}

