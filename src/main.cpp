#include <Arduino.h>
#include <WiFi.h>
#include <PubSubClient.h>
#include <Adafruit_Sensor.h>
#include <DHT.h>
#include <DHT_U.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <nlohmann/json.hpp>
#include <freertos/FreeRTOS.h>
#include <freertos/task.h>
#include <freertos/semphr.h>
using json = nlohmann::json;

#define DHTTYPE DHT22
#define DHTPIN 4
#define MQ2_PIN 34
#define LDR_PIN 35
#define TRIG_PIN 13
#define ECHO_PIN 12
#define POTE_PIN 32
#define PIR_PIN 14

const char *MQTT_Server = "broker.emqx.io";
const char *MQTT_Topic_Gui = "Nta_22004335_gui";
const char *MQTT_Topic_Nhan = "Nta_22004335_nhan";
const char *MQTT_ID = "ae56e8c6-447a-49a2-bcf9-704820402c40";
int Port = 1883;

WiFiClient espClient;
PubSubClient client(espClient);
DHT dht(DHTPIN, DHTTYPE);
LiquidCrystal_I2C lcd(0x27, 16, 2);

float nd[7], da[7], khi[7], as[7], dad[7];
float tnd = 0, tda = 0, tkhi = 0, tas = 0, tdad = 0;
int i = 0;
SemaphoreHandle_t xDataMutex;

void WIFIConnect() {
  Serial.println("Connecting to SSID: Wokwi-GUEST");
  WiFi.begin("Wokwi-GUEST", "");
  while (WiFi.status() != WL_CONNECTED) {
    vTaskDelay(500 / portTICK_PERIOD_MS);
    Serial.print(".");
  }
  Serial.println("");
  Serial.print("WiFi connected, IP address: ");
  Serial.println(WiFi.localIP());
}

void MQTT_Reconnect() {
  while (!client.connected()) {
    Serial.println("Đang kết nối MQTT...");
    if (client.connect(MQTT_ID)) {
      Serial.println("Kết nối thành công!");
      client.subscribe(MQTT_Topic_Nhan);
    } else {
      Serial.print("failed, rc=");
      Serial.print(client.state());
      Serial.println(" try again in 5 seconds");
      vTaskDelay(5000 / portTICK_PERIOD_MS);
    }
  }
}

void callback(char* topic, byte* message, unsigned int length) {
  String stMessage;
  for (int i = 0; i < length; i++) {
    stMessage += (char)message[i];
  }
  Serial.print("Message: ");
  Serial.println(stMessage);

  json doc = json::parse(stMessage);
  int motorbechuanuoc = doc.value("motorbechuanuoc", 0);
  int htphunsuong = doc.value("htphunsuong", 0);
  int quatthongio = doc.value("quatthongio", 0);
  int htdenchieusang = doc.value("htdenchieusang", 0);
  int htledcanhbao = doc.value("htledcanhbao", 0);
  int coibao = doc.value("coibao", 0);
  int htmangche = doc.value("htmangche", 0);
}

void TaskSensorRead(void *pvParameters) {
  (void)pvParameters;
  for (;;) {
    float nhietdo = dht.readTemperature();
    float doam = dht.readHumidity();
    float anhsang = analogRead(LDR_PIN);
    float khigas = analogRead(MQ2_PIN);
    float doamdat = analogRead(POTE_PIN);
    int hongngoai = digitalRead(PIR_PIN);

    digitalWrite(TRIG_PIN, LOW);
    delayMicroseconds(2);
    digitalWrite(TRIG_PIN, HIGH);
    delayMicroseconds(10);
    digitalWrite(TRIG_PIN, LOW);
    long duration = pulseIn(ECHO_PIN, HIGH);
    float sieuam = duration * 0.034 / 2;

    if (!isnan(nhietdo) && !isnan(doam)) {
      xSemaphoreTake(xDataMutex, portMAX_DELAY);
      if (i < 7) {
        nd[i] = nhietdo;
        da[i] = doam;
        khi[i] = khigas;
        as[i] = anhsang;
        dad[i] = doamdat;
        i++;
      }

      if (i >= 7) {
        i = 6;
        tnd = tda = tkhi = tas = tdad = 0;
        for (int j = 0; j < 7; j++) {
          tnd += nd[j];
          tda += da[j];
          tkhi += khi[j];
          tas += as[j];
          tdad += dad[j];
        }
        for (int j = 0; j < 6; j++) {
          nd[j] = nd[j + 1];
          da[j] = da[j + 1];
          khi[j] = khi[j + 1];
          as[j] = as[j + 1];
          dad[j] = dad[j + 1];
        }
      }
      xSemaphoreGive(xDataMutex);
    } else {
      Serial.println("Lỗi đọc từ cảm biến DHT22!");
    }

    Serial.println(hongngoai);
    vTaskDelay(5000 / portTICK_PERIOD_MS);
  }
}

void TaskMQTTPublish(void *pvParameters) {
  (void)pvParameters;
  for (;;) {
    if (!client.connected()) {
      MQTT_Reconnect();
    }
    client.loop();

    xSemaphoreTake(xDataMutex, portMAX_DELAY);
    if (i >= 7) {
      String jsonData = "{"
        "\"humidity\":" + String(tda/7, 2) +
        ", \"temperature\":" + String(tnd/7, 2) +
        ", \"anhsang\":" + String(tas/7, 2) +
        ", \"nongdokhi\":" + String(tkhi/7, 2) +
        ", \"doamdat\":" + String(tdad/7, 2) +
        ", \"sieuam\":" + String(0.0, 2) +
        "}";
      client.publish(MQTT_Topic_Gui, jsonData.c_str());
    }
    xSemaphoreGive(xDataMutex);

    vTaskDelay(500 / portTICK_PERIOD_MS);
  }
}

void setup() {
  Serial.begin(115200);

  pinMode(TRIG_PIN, OUTPUT);
  pinMode(ECHO_PIN, INPUT);
  pinMode(MQ2_PIN, INPUT);
  pinMode(LDR_PIN, INPUT);
  pinMode(POTE_PIN, INPUT);
  pinMode(PIR_PIN, INPUT);
  pinMode(17, OUTPUT);

  WIFIConnect();
  client.setServer(MQTT_Server, Port);
  client.setCallback(callback);
  dht.begin();
  lcd.init();
  lcd.backlight();

  Serial.println("Setup OK");

  xDataMutex = xSemaphoreCreateMutex();

  xTaskCreatePinnedToCore(
    TaskSensorRead,
    "SensorRead",
    4096,
    NULL,
    1,
    NULL,
    ARDUINO_RUNNING_CORE);

  xTaskCreatePinnedToCore(
    TaskMQTTPublish,
    "MQTTPublish",
    4096,
    NULL,
    1,
    NULL,
    ARDUINO_RUNNING_CORE);
}

void loop() {
  // Empty loop as tasks handle everythings
}