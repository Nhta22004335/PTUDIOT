#include <Arduino.h>
#include <WiFi.h>
#include "PubSubClient.h"
#include <Adafruit_Sensor.h>
#include <DHT.h>
#include <DHT_U.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <nlohmann/json.hpp>
using json = nlohmann::json;
#define DHTTYPE DHT22  // Loại cảm biến: DHT22
#define DHTPIN 4       // Chân DATA nối với GPIO4 nd và độ ẩm

#define MQ2_PIN 34  // Chân A0 của MQ2 nối với GPIO5 của ESP32 khí gas
#define LDR_PIN 35  // Chân kết nối LDR ánh sáng

// Cảm biến siêu âm
#define TRIG_PIN 13
#define ECHO_PIN 12

#define POTE_PIN 32 //cảm biến độ ẩm đất

#define PIR_PIN 14 //cảm biến hồng ngoại

const char * MQTT_Server = "broker.emqx.io";
const char * MQTT_Topic_Gui = "Nta_22004335_gui";
const char * MQTT_Topic_Nhan = "Nta_22004335_nhan";  // Chủ đề nhận dữ liệu
const char * MQTT_ID = "ae56e8c6-447a-49a2-bcf9-704820402c40"; // Tạo ID ngẫu nhiên tại: https://www.guidgen.com/

int i = 0;
float nd[7], da[7], khi[7], as[7], dad[7];
float tnd = 0, tda = 0, tkhi = 0, tas = 0, tdad = 0;

int Port = 1883;
WiFiClient espClient;
PubSubClient client(espClient);
DHT dht(DHTPIN, DHTTYPE);
// LiquidCrystal_I2C lcd(0x27, 16, 2);


void WIFIConnect() {
  Serial.println("Connecting to SSID: Wokwi-GUEST");
  WiFi.begin("Wokwi-GUEST", "");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("");
  Serial.print("WiFi connected");
  Serial.print(", IP address: ");
  Serial.println(WiFi.localIP());
}

void MQTT_Reconnect() {
  while (!client.connected()) {
    Serial.println("Đang kết nối MQTT...");
    if (client.connect(MQTT_ID)) {
      Serial.println("Kết nối thành công!");
      client.subscribe(MQTT_Topic_Nhan); // Đăng ký nhận dữ liệu từ web
    } else {
      Serial.print("failed, rc=");
      Serial.print(client.state());
      Serial.println(" try again in 5 seconds");
      delay(5000);
    }
  }
}

// Hàm để nhận dữ liệu được gừi từ web
void callback(char* topic, byte* message, unsigned int length) {
  Serial.print("Message: ");
  String stMessage, st;
  for (int i = 0; i < length; i++) {
    Serial.print((char)message[i]);
    stMessage += (char)message[i];
  }

  // Phân tích chuỗi JSON
  // json doc = json::parse(stMessage);

  // int motorbechuanuoc = doc.value("motorbechuanuoc", 0);
  // int htphunsuong    = doc.value("htphunsuong", 0);
  // int quatthongio    = doc.value("quatthongio", 0);
  // int htdenchieusang = doc.value("htdenchieusang", 0);
  // int htledcanhbao   = doc.value("htledcanhbao", 0);
  // int coibao         = doc.value("coibao", 0);
  // int htmangche      = doc.value("htmangche", 0);  

  // st = stMessage;
  // if (stMessage.length() <= 16) {
  //   lcd.setCursor(0, 0);
  //   lcd.print(stMessage);  // Không cần cuộn nếu chuỗi ngắn
  // } else {
  //   while (st == stMessage) {
  //     st = stMessage;
  //     for (int i = 0; i <= stMessage.length() - 16; i++) {
  //       lcd.setCursor(0, 0);
  //       lcd.print(stMessage.substring(i, i + 16));
  //       delay(300);
  //     }
  //   }
  // }
}

void setup() {
  Serial.begin(115200);
  WIFIConnect();
  client.setServer(MQTT_Server, Port);
  // client.setCallback(callback);
  dht.begin();
  // lcd.init();
  // delay(100);
  lcd.backlight();
  pinMode(TRIG_PIN, OUTPUT);
  pinMode(ECHO_PIN, INPUT);
  pinMode(MQ2_PIN, INPUT);
  pinMode(LDR_PIN, INPUT);
  pinMode(POTE_PIN, INPUT);
  pinMode(PIR_PIN, INPUT);
  Serial.println("Setup OK");
}

void loop() {
  delay(10);
  if (!client.connected()) {
    MQTT_Reconnect();
  }
  client.loop();
  //CẢM BIẾN DHT22 NHIỆT ĐỘ, ĐỘ ẨM
  float nhietdo = dht.readTemperature(); 
  float doam = dht.readHumidity();      
  if (isnan(nhietdo) || isnan(doam)) {
    Serial.println("Lỗi đọc từ cảm biến DHT22!");
    return;
  }
  
  //CẢM BIẾN ÁNH SÁNG LDR
  float anhsang = analogRead(LDR_PIN);
  
  //CẢM BIẾN KHÍ GAS
  float khigas = analogRead(MQ2_PIN);  // Đọc giá trị từ MQ2 khí
  //CẢM BIẾN SIÊU ÂM
  digitalWrite(TRIG_PIN, LOW);
  delayMicroseconds(2);
  digitalWrite(TRIG_PIN, HIGH);
  delayMicroseconds(10);
  digitalWrite(TRIG_PIN, LOW);
  long duration = pulseIn(ECHO_PIN, HIGH);
  float sieuam = duration * 0.034 / 2;  // Chuyển đổi sang cm siêu âm
  //CẢM BIẾN ĐỘ ẨM ĐẤT
  float doamdat = analogRead(POTE_PIN);
  int hongngoai = digitalRead(PIR_PIN);
  Serial.println(hongngoai);

  if (i<7) {
    nd[i] = nhietdo;
    da[i] = doam;
    khi[i] = khigas;
    as[i] = anhsang;
    dad[i] = doamdat;
  }

  i++;
  if (i>=7) {
    i=6;
    for (int j=0;j<7;j++) {
      tnd += nd[j];
      tda += da[j];
      tkhi += khi[j];
      tas += as[j];
      tdad += dad[j];
    }
    for (int j=0;j<6;j++) {
      nd[j] = nd[j+1];
      da[j] = da[j+1];
      khi[j] = khi[j+1];
      as[j] = as[j+1];
      dad[j] = dad[j+1];
    }

    String jsonData = "{"
    "\"humidity\":" + String(tda/7, 2) + 
    ", \"temperature\":" + String(tnd/7, 2) + 
    ", \"anhsang\":" + String(tas/7, 2) +
    ", \"nongdokhi\":" + String(tkhi/7, 2) + 
    ", \"doamdat\":" + String(tdad/7, 2) +
    ", \"sieuam\":" + String(sieuam/7, 2) +
    "}";

    client.publish(MQTT_Topic_Gui, jsonData.c_str());
    tnd=0, tda=0, tkhi=0, tas=0;
  }
  delay(5000);
}

