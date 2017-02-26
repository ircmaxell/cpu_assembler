#include "pins_arduino.h"
#include "SPI_USART.h"

uint8_t SPIUsartClass::ss[SPIUSART_COUNT];

void SPIUsartClass::begin(uint8_t _usart, uint8_t _ss) {
  if (_usart > SPIUSART_COUNT) {
    return;
  }
  ss[_usart] = _ss;
  
  digitalWrite(ss[_usart], HIGH);
  pinMode(ss[_usart], OUTPUT);

  #define SPIUSART_INIT_CASE(_n) \
      UBRR ## _n = 0; \
      UCSR ## _n ## A = _BV(TXC ## _n); \
      UCSR ## _n ## C = _BV(UMSEL ## _n ## 0) | _BV(UMSEL ## _n ## 1); \
      UCSR ## _n ## B = _BV(TXEN ## _n) | _BV(RXEN ## _n); \
      UBRR ## _n = 0; \

  SPIUSART_SWITCH(_usart, SPIUSART_INIT_CASE);

  SPIUsart.beginTransaction(_usart, SPIUsartSettings());
}

