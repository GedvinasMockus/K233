#include <lmic.h>
#include <hal/hal.h>
#include <SPI.h>
#include <Arduino.h>
#include <BLEDevice.h>
#include <BLEUtils.h>
#include <BLEScan.h>
#include <BLEAdvertisedDevice.h>
#include <BLEBeacon.h>
#include <ArduinoJson.h>
#include <map>
#include <ctime>

static const u1_t PROGMEM APPEUI[8] = { 0x39, 0x27, 0x5E, 0x85, 0x6A, 0xAC, 0xC9, 0x36 };
static const u1_t PROGMEM DEVEUI[8] = { 0x39, 0xB1, 0x05, 0xD0, 0x7E, 0xD5, 0xB3, 0x70 };
static const u1_t PROGMEM APPKEY[16] = { 0x95, 0x48, 0x33, 0xF0, 0x18, 0x2F, 0x0C, 0x58, 0xA4, 0x13, 0x3D, 0x1E, 0x98, 0xE9, 0xA9, 0x73 };

const int ledPin1 = 21;
const int ledPin2 = 13;
const int ledPin3 = 12;

const int servoPinA1 = 17;
const int servoPinA2 = 16;
const int servoPinB1 = 4;
const int servoPinB2 = 15;
const int parkingNum = 1;

const int relay = 23;

std::string trackingUuid = "";
double trackingRssi = 0;
double averageRssi = 0;
int countScan = 0;
std::map<std::string, time_t> openedBeacons;

dr_t drSF = DR_SF7;

unsigned int time_delay = 6;

bool joined = false;
bool founded = false;

void os_getArtEui(u1_t* buf) {
  memcpy_P(buf, APPEUI, 8);
}
void os_getDevEui(u1_t* buf) {
  memcpy_P(buf, DEVEUI, 8);
}
void os_getDevKey(u1_t* buf) {
  memcpy_P(buf, APPKEY, 16);
}

int scanTime = 1;
BLEScan* pBLEScan;

struct BeaconInfo {
  std::string uuid;
  int power;
  int rssi;
  int distance;
};

BeaconInfo beaconList[3];
int beaconCount = 0;

int calculateDistance(int txPower, int rssi) {
  double ratio = pow(10, ((txPower - (rssi)) / (10 * 2.5)));
  double distance = (ratio * 100.0) / 2.54;
  int roundedDistance = (int)(distance);

  return roundedDistance;
}

const unsigned TX_INTERVAL = 1;
static osjob_t sendjob;

const lmic_pinmap lmic_pins = {
  .nss = 18,
  .rxtx = LMIC_UNUSED_PIN,
  .rst = 14,
  .dio = { 26, 33, 32 },
};

void printHex2(unsigned v) {
  v &= 0xff;
  if (v < 16)
    Serial.print('0');
  Serial.print(v, HEX);
}

class MyAdvertisedDeviceCallbacks : public BLEAdvertisedDeviceCallbacks {
  void onResult(BLEAdvertisedDevice advertisedDevice) {
    if (advertisedDevice.haveManufacturerData()) {
      std::string strManufacturerData = advertisedDevice.getManufacturerData();

      uint8_t cManufacturerData[100];
      strManufacturerData.copy((char*)cManufacturerData, strManufacturerData.length(), 0);

      if (strManufacturerData.length() == 25 && cManufacturerData[0] == 0x4C && cManufacturerData[1] == 0x00) {
        BLEBeacon oBeacon = BLEBeacon();
        oBeacon.setData(strManufacturerData);

        uint16_t major = __builtin_bswap16(oBeacon.getMajor());
        uint16_t minor = __builtin_bswap16(oBeacon.getMinor());
        if (major == 10 && minor == 11) {
          if (beaconCount < 3 && trackingUuid.length() < 2) {
            std::string uuid = oBeacon.getProximityUUID().toString();
            std::map<std::string, time_t>::iterator it = openedBeacons.find(uuid);
            if (it != openedBeacons.end()) {
              return;
            }
            BeaconInfo beaconInfo;
            beaconInfo.uuid = oBeacon.getProximityUUID().toString();
            beaconInfo.power = oBeacon.getSignalPower();
            beaconInfo.rssi = advertisedDevice.getRSSI();
            beaconInfo.distance = calculateDistance(beaconInfo.power, beaconInfo.rssi);
            beaconList[beaconCount++] = beaconInfo;
          } else if (trackingUuid.compare(oBeacon.getProximityUUID().toString()) == 0) {
            averageRssi += abs(advertisedDevice.getRSSI());
          }
        }
      }
    }
  }
};
void do_send(osjob_t* j) {
  StaticJsonDocument<128> json;
  if (!joined) {
    json["Working"] = "true";
    String jsonString;
    serializeJson(json, jsonString);
    char buffer[jsonString.length() + 1];
    jsonString.toCharArray(buffer, jsonString.length() + 1);
    Serial.println(buffer);
    LMIC_setTxData2(1, (uint8_t*)buffer, strlen(buffer), 0);
    Serial.println(F("Packet queued"));
  } else {
    if (!founded) {
      BLEScanResults foundDevices = pBLEScan->start(scanTime, true);
      pBLEScan->clearResults();
      for (int i = 0; i < beaconCount; i++) {
        for (int j = i + 1; j < beaconCount; j++) {
          if (beaconList[j].distance < beaconList[i].distance) {
            BeaconInfo temp = beaconList[i];
            beaconList[i] = beaconList[j];
            beaconList[j] = temp;
          }
        }
      }
    }
    if (beaconCount != 0) {
      founded = true;
      trackingUuid = beaconList[0].uuid;
      trackingRssi = abs(beaconList[0].rssi) + 20;
      std::string str = beaconList[0].uuid;

      str.erase(std::remove_if(str.begin(), str.end(), [](char c) {
                  return c == '-';
                }),
                str.end());
      std::string combined = str + std::to_string(beaconList[0].distance);

      Serial.println(beaconList[0].uuid.c_str());
      Serial.println(beaconList[0].distance);

      digitalWrite(ledPin1, LOW);
      digitalWrite(ledPin2, HIGH);
      digitalWrite(ledPin3, LOW);
      LMIC_setTxData2(parkingNum, (uint8_t*)(combined.c_str()), combined.length(), 0);


      Serial.println(F("Packet queued"));
    } else {
      trackingUuid = "";
      trackingRssi = 0;
      averageRssi = 0;
      countScan = 0;
      Serial.println(F("Nera duomenu"));
      LMIC_sendAlive();
    }
  }
}

void servo1() {
  //A+,B+
  digitalWrite(servoPinA1, HIGH);
  digitalWrite(servoPinA2, LOW);
  digitalWrite(servoPinB1, HIGH);
  digitalWrite(servoPinB2, LOW);
  delay(time_delay);
}

void servo2() {
  //A+,B-
  digitalWrite(servoPinA1, HIGH);
  digitalWrite(servoPinA2, LOW);
  digitalWrite(servoPinB1, LOW);
  digitalWrite(servoPinB2, HIGH);
  delay(time_delay);
}

void servo3() {
  //A-,B-
  digitalWrite(servoPinA1, LOW);
  digitalWrite(servoPinA2, HIGH);
  digitalWrite(servoPinB1, LOW);
  digitalWrite(servoPinB2, HIGH);
  delay(time_delay);
}

void servo4() {
  //A-,B+
  digitalWrite(servoPinA1, LOW);
  digitalWrite(servoPinA2, HIGH);
  digitalWrite(servoPinB1, HIGH);
  digitalWrite(servoPinB2, LOW);
  delay(time_delay);
}
void closing(long st) {
  long i = 0;
  while (i < st) {
    servo1();
    servo2();
    servo3();
    servo4();
    i++;
  }
}
void opening(long st) {
  long i = 0;
  while (i < st) {
    servo1();
    servo4();
    servo3();
    servo2();
    i++;
  }
}
void onEvent(ev_t ev) {
  String status;
  String space;
  switch (ev) {
    case EV_JOINING:
      LMIC_setDrTxpow(drSF, 14);
      LMIC_setAdrMode(0);
      Serial.println(F("EV_JOINING"));
      break;
    case EV_JOINED:
      Serial.println(F("EV_JOINED"));
      {
        u4_t netid = 0;
        devaddr_t devaddr = 0;
        u1_t nwkKey[16];
        u1_t artKey[16];
        LMIC_getSessionKeys(&netid, &devaddr, nwkKey, artKey);
        Serial.print("netid: ");
        Serial.println(netid, DEC);
        Serial.print("devaddr: ");
        Serial.println(devaddr, HEX);
        Serial.print("AppSKey: ");
        for (size_t i = 0; i < sizeof(artKey); ++i) {
          if (i != 0)
            Serial.print("-");
          printHex2(artKey[i]);
        }
        Serial.println("");
        Serial.print("NwkSKey: ");
        for (size_t i = 0; i < sizeof(nwkKey); ++i) {
          if (i != 0)
            Serial.print("-");
          printHex2(nwkKey[i]);
        }
        Serial.println();
      }
      LMIC_setLinkCheckMode(0);
      break;
    case EV_JOIN_FAILED:
      Serial.println(F("EV_JOIN_FAILED"));
      break;
    case EV_REJOIN_FAILED:
      Serial.println(F("EV_REJOIN_FAILED"));
      break;
    case EV_TXCOMPLETE:
      joined = true;
      memset(beaconList, 0, sizeof(beaconList));
      beaconCount = 0;
      if (LMIC.dataLen) {
        StaticJsonDocument<128> json;
        deserializeJson(json, LMIC.frame + LMIC.dataBeg, LMIC.dataLen);
        status = json["status"].as<String>();
        Serial.print("Received status: ");
        Serial.println(status);
        if (strcasecmp(status.c_str(), "1") == 0 && trackingUuid.length() > 2) {
          space = json["nr"].as<String>();
          Serial.print("Vieta: ");
          Serial.println(space);

          digitalWrite(ledPin1, HIGH);
          digitalWrite(ledPin2, LOW);
          digitalWrite(ledPin3, LOW);
          servoWork();
        } else {
          digitalWrite(ledPin1, LOW);
          digitalWrite(ledPin2, LOW);
          digitalWrite(ledPin3, HIGH);
        }
        status = "";
      } else {
        digitalWrite(ledPin1, LOW);
        digitalWrite(ledPin2, LOW);
        digitalWrite(ledPin3, HIGH);
      }
      averageRssi = 0;
      countScan = 0;
      trackingRssi = 0;
      trackingUuid = "";
      founded = false;
      os_setTimedCallback(&sendjob, os_getTime() + sec2osticks(TX_INTERVAL), do_send);
      break;
    case EV_TXSTART:
      break;
    case EV_JOIN_TXCOMPLETE:
      digitalWrite(ledPin1, LOW);
      digitalWrite(ledPin2, LOW);
      digitalWrite(ledPin3, HIGH);
      Serial.println(F("EV_JOIN_TXCOMPLETE: no JoinAccept"));
      delay(100);
      ESP.restart();
      break;
    default:
      Serial.print(F("Unknown event: "));
      Serial.println((unsigned)ev);
      break;
  }
}

void servoWork() {
  digitalWrite(relay, HIGH);
  opening(14);
  bool openedBarrier = true;
  while (openedBarrier) {
    for (int i = 0; i < 3; i++) {
      BLEScanResults foundDevices = pBLEScan->start(scanTime, true);
      pBLEScan->clearResults();
      countScan++;
    }
    averageRssi /= countScan;
    Serial.print("Tracking: ");
    Serial.print(trackingRssi);
    Serial.print(" Average: ");
    Serial.println(averageRssi);
    if (averageRssi >= trackingRssi || averageRssi <= 1) {
      openedBarrier = false;
      openedBeacons[trackingUuid] = time(NULL);
    } else {
      trackingRssi = averageRssi + 2;
    }
    averageRssi = 0;
    countScan = 0;
  }

  delay(3000);
  digitalWrite(relay, LOW);
  closing(14);
}

void removeOldBeacons() {
  time_t now = time(NULL);
  for (auto it = openedBeacons.begin(); it != openedBeacons.end();) {
    if (difftime(now, it->second) > 20) {
      it = openedBeacons.erase(it);
    } else {
      ++it;
    }
  }
}

void setup() {
  Serial.begin(9600);
  BLEDevice::init("");
  pBLEScan = BLEDevice::getScan();
  pBLEScan->setAdvertisedDeviceCallbacks(new MyAdvertisedDeviceCallbacks());
  pBLEScan->setActiveScan(true);
  pBLEScan->setInterval(5);
  pBLEScan->setWindow(4);
  pinMode(ledPin1, OUTPUT);
  pinMode(ledPin2, OUTPUT);
  pinMode(ledPin3, OUTPUT);
  digitalWrite(ledPin1, LOW);
  digitalWrite(ledPin2, LOW);
  digitalWrite(ledPin3, LOW);
  pinMode(relay, OUTPUT);
  digitalWrite(relay, LOW);
  pinMode(servoPinA1, OUTPUT);
  pinMode(servoPinA2, OUTPUT);
  pinMode(servoPinB1, OUTPUT);
  pinMode(servoPinB2, OUTPUT);
  closing(20);
  delay(1000);
  os_init();
  LMIC_reset();
  LMIC_setClockError(MAX_CLOCK_ERROR * 1 / 100);
  LMIC_setLinkCheckMode(0);
  LMIC_setDrTxpow(drSF, 14);
  LMIC_setAdrMode(0);
  do_send(&sendjob);
}

unsigned long previousMillis = 0;
const long interval = 1000;
void loop() {
  unsigned long currentMillis = millis();
  if (currentMillis - previousMillis >= interval) {
    removeOldBeacons();
    previousMillis = currentMillis;
  }
  os_runloop_once();
}
