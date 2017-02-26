#ifndef _SPIUSART_H_INCLUDED
#define _SPIUSART_H_INCLUDED

#include <Arduino.h>
#include <avr/pgmspace.h>
#include <SPI.h>

#define SPIUSART_SET_BIT(_dest, _bit) _dest |= _BV(_bit)
#define SPIUSART_CLEAR_BIT(_dest, _bit) _dest &= ~_BV(_bit)

#if defined(UBRR3)
#define SPIUSART0 0
#define SPIUSART1 1
#define SPIUSART2 2
#define SPIUSART3 3
#define SPIUSART_COUNT 4
#define SPIUSART_SWITCH(_v, _c) switch(_v) { \
  case 0: \
    _c(0); \
    break; \
  case 1: \
    _c(1); \
    break; \
  case 2: \
    _c(2); \
    break; \
  case 3: \
    _c(3); \
    break; \
  }

#elif defined(UBRR2)

#define SPIUSART0 0
#define SPIUSART1 1
#define SPIUSART2 2
#define SPIUSART_COUNT 3

#define SPIUSART_SWITCH(_v, _c) switch(_v) { \
  case 0: \
    _c(0); \
    break; \
  case 1: \
    _c(1); \
    break; \
  case 2: \
    _c(2); \
    break; \
  }

#elif defined(UBRR1)

#define SPIUSART0 0
#define SPIUSART1 1
#define SPIUSART_COUNT 2

#define SPIUSART_SWITCH(_v, _c) switch(_v) { \
  case 0: \
    _c(0); \
    break; \
  case 1: \
    _c(1); \
    break; \
  }

#elif defined(UBRR0)

#define SPIUSART0 0
#define SPIUSART_COUNT 1

#define SPIUSART_SWITCH(_v, _c) switch(_v) { \
  case 0: \
    _c(0); \
    break; \
  }

#else
#define SPIUSART_COUNT 0

#define SPIUSART_SWITCH(_v, _c) {}
#endif

#define SPIUSART_UCSRnC_MASK ~(_BV(UDORD0) | _BV(UCPOL0) | _BV(UCPHA0))

class SPIUsartSettings {
  public:
    SPIUsartSettings() {
      SPIUsartSettings(4000000, MSBFIRST, SPI_MODE0);
    }
    SPIUsartSettings(uint32_t clock, uint8_t bitOrder, uint8_t dataMode) {
      ubbr_value = (F_CPU / (2 * clock)) - 1;
      if (clock > F_CPU / 2) {
        ubbr_value = 0; // clamp to max
      }
      ucsrc_or_mask = 0;
      if (bitOrder == LSBFIRST) {
        ucsrc_or_mask |= _BV(UDORD0);
      }
      switch (dataMode) {
        case SPI_MODE0:
          break; // all pins are cleared
        case SPI_MODE1:
          ucsrc_or_mask |= _BV(UCPHA0);
          break;
        case SPI_MODE2:
          ucsrc_or_mask |= _BV(UCPOL0);
          break;
        case SPI_MODE3:
          ucsrc_or_mask |= _BV(UCPOL0) | _BV(UCPHA0);
          break;
      }
    }
  private:
    uint8_t ucsrc_or_mask = 0;
    uint16_t ubbr_value = 0;
    friend class SPIUsartClass;
};

class SPIUsartClass {
  public:


    inline uint8_t transfer16(uint8_t _usart, uint16_t _value) {
      uint16_t result = _value;
      digitalWrite(SPIUsartClass::ss[_usart], LOW);
      result = SPIUsartClass::transfer_raw(_usart, _value & 0xFF);
      result |= SPIUsartClass::transfer_raw(_usart, _value >> 8) << 8;
      digitalWrite(SPIUsartClass::ss[_usart], HIGH);
      return result;
    }
    
    template<typename T>
    static inline T transfer(uint8_t _usart, T _value) {
      T r = _value;
      SPIUsartClass::transfer(_usart, &r, sizeof(T));
      return r;
    }

    template<typename T>
    static inline T transferRev(uint8_t _usart, T _value) {
      T r = _value;
      SPIUsartClass::transferRev(_usart, &r, sizeof(T));
      return r;
    }

    inline static void transfer(uint8_t, void*, size_t);
    inline static void transferRev(uint8_t, void*, size_t);

    inline static void beginTransaction(uint8_t _usart, SPIUsartSettings settings) {
#define SPIUSART_BEGIN_TRANSACTION_CASE(_n) \
  UCSR ## _n ## C = (UCSR ## _n ## C & SPIUSART_UCSRnC_MASK) | settings.ucsrc_or_mask; \
  UBRR ## _n = settings.ubbr_value;

      SPIUSART_SWITCH(_usart, SPIUSART_BEGIN_TRANSACTION_CASE);
    }

    inline static void endTransaction(uint8_t _usart) {

    }

    static void begin(uint8_t, uint8_t); // SS
    static void end(uint8_t);
  private:
    static uint8_t ss[SPIUSART_COUNT];
    static inline uint8_t transfer_raw(uint8_t, uint8_t);

};

extern SPIUsartClass SPIUsart;

inline void SPIUsartClass::transfer(uint8_t _usart, void *buf, size_t count) {
  register uint8_t *p = (uint8_t *) buf;
  digitalWrite(SPIUsartClass::ss[_usart], LOW);
  // duff's device
  register size_t n = (count + 7) / 8;
  switch (count % 8) {
      do {
      case 0:
        *p = SPIUsartClass::transfer_raw(_usart, *p);
        p++;
      case 7:
        *p = SPIUsartClass::transfer_raw(_usart, *p);
        p++;
      case 6:
        *p = SPIUsartClass::transfer_raw(_usart, *p);
        p++;
      case 5:
        *p = SPIUsartClass::transfer_raw(_usart, *p);
        p++;
      case 4:
        *p = SPIUsartClass::transfer_raw(_usart, *p);
        p++;
      case 3:
        *p = SPIUsartClass::transfer_raw(_usart, *p);
        p++;
      case 2:
        *p = SPIUsartClass::transfer_raw(_usart, *p);
        p++;
      case 1:
        *p = SPIUsartClass::transfer_raw(_usart, *p);
        p++;
      } while (--n > 0);
  }
  digitalWrite(SPIUsartClass::ss[_usart], HIGH);
}

inline void SPIUsartClass::transferRev(uint8_t _usart, void *buf, size_t count) {
  register uint8_t *p = (uint8_t *) buf + count - 1;
  digitalWrite(SPIUsartClass::ss[_usart], LOW);
  // duff's device
  register size_t n = (count + 7) / 8;
  switch (count % 8) {
      do {
      case 0:
        *p = SPIUsartClass::transfer_raw(_usart, *p);
        p--;
      case 7:
        *p = SPIUsartClass::transfer_raw(_usart, *p);
        p--;
      case 6:
        *p = SPIUsartClass::transfer_raw(_usart, *p);
        p--;
      case 5:
        *p = SPIUsartClass::transfer_raw(_usart, *p);
        p--;
      case 4:
        *p = SPIUsartClass::transfer_raw(_usart, *p);
        p--;
      case 3:
        *p = SPIUsartClass::transfer_raw(_usart, *p);
        p--;
      case 2:
        *p = SPIUsartClass::transfer_raw(_usart, *p);
        p--;
      case 1:
        *p = SPIUsartClass::transfer_raw(_usart, *p);
        p--;
      } while (--n > 0);
  }
  digitalWrite(SPIUsartClass::ss[_usart], HIGH);
}

inline uint8_t SPIUsartClass::transfer_raw(uint8_t _usart, uint8_t _data) {

#define SPIUSART_TRANSFER_RAW_CASE(_n) \
  UDR ## _n = _data; \
  asm volatile("nop"); \
  do {} while(!(UCSR ## _n ## A & _BV(RXC ## _n))); \
  return UDR ## _n;

  SPIUSART_SWITCH(_usart, SPIUSART_TRANSFER_RAW_CASE);
  return 0;
}

#endif
