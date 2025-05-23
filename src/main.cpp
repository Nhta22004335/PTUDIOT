#include <Arduino.h>                // Thư viện cơ bản cho Arduino
#include <WiFi.h>                   // Thư viện để kết nối WiFi
#include <PubSubClient.h>           // Thư viện để giao tiếp MQTT
#include <Adafruit_Sensor.h>        // Thư viện hỗ trợ cảm biến
#include <DHT.h>                    // Thư viện cho cảm biến DHT
#include <DHT_U.h>                  // Thư viện bổ sung cho DHT
#include <Wire.h>                   // Thư viện giao tiếp I2C
#include <LiquidCrystal_I2C.h>      // Thư viện cho màn hình LCD I2C
#include <nlohmann/json.hpp>        // Thư viện xử lý JSON
#include <freertos/FreeRTOS.h>      // Thư viện FreeRTOS cho đa nhiệm
#include <freertos/task.h>          // Thư viện quản lý tác vụ FreeRTOS
#include <freertos/semphr.h>        // Thư viện semaphore cho đồng bộ hóa
using json = nlohmann::json;        // Định nghĩa alias cho thư viện JSON

// Định nghĩa loại cảm biến DHT và chân kết nối
#define DHTTYPE DHT22               // Loại cảm biến DHT là DHT22
#define DHTPIN 4                    // Chân GPIO 4 cho cảm biến DHT
#define MQ2_PIN 34                  // Chân GPIO 34 cho cảm biến khí MQ2
#define LDR_PIN 35                  // Chân GPIO 35 cho cảm biến ánh sáng LDR
#define TRIG_PIN 13                 // Chân GPIO 13 cho chân Trig của cảm biến siêu âm
#define ECHO_PIN 12                 // Chân GPIO 12 cho chân Echo của cảm biến siêu âm
#define POTE_PIN 32                 // Chân GPIO 32 cho cảm biến độ ẩm đất
#define PIR_PIN 14                  // Chân GPIO 14 cho cảm biến hồng ngoại PIR

// Định nghĩa các chân điều khiển thiết bị
const int PIN_RELAY_MOTOR_MANGCHE = 17; // Chân GPIO 17 điều khiển relay cho motor và màn che
const int PIN_PHUNSUONG_QUAT = 15;      // Chân GPIO 15 điều khiển phun sương và quạt (đổi từ 14 để tránh xung đột)
const int PIN_COIBAO = 25;              // Chân GPIO 25 điều khiển còi báo động
const int PIN_RGB_R = 5;                // Chân GPIO 5 cho màu đỏ của đèn RGB
const int PIN_RGB_G = 18;               // Chân GPIO 18 cho màu xanh lá của đèn RGB
const int PIN_RGB_B = 19;               // Chân GPIO 19 cho màu xanh dương của đèn RGB
const int PIN_DENCHIEUSANG = 4;         // Chân GPIO 4 điều khiển đèn chiếu sáng

// Cấu hình MQTT
const char *MQTT_Server = "broker.emqx.io"; // Địa chỉ server MQTT
const char *MQTT_Topic_Gui = "Nta_22004335_gui"; // Topic để gửi dữ liệu
const char *MQTT_Topic_Nhan = "Nta_22004335_nhan"; // Topic để nhận dữ liệu
const char *MQTT_ID = "ae56e8c6-447a-49a2-bcf9-704820402c40"; // ID client MQTT
int Port = 1883;                        // Cổng MQTT mặc định

// Khởi tạo đối tượng
WiFiClient espClient;                   // Đối tượng client WiFi
PubSubClient client(espClient);         // Đối tượng client MQTT
DHT dht(DHTPIN, DHTTYPE);              // Đối tượng cảm biến DHT
LiquidCrystal_I2C lcd(0x27, 20, 4);    // Đối tượng LCD I2C, địa chỉ 0x27, kích thước 20x4

// Mảng lưu trữ dữ liệu cảm biến (7 mẫu cho mỗi loại)
float nd[7], da[7], khi[7], as[7], dad[7], sa[7]; // Nhiệt độ, độ ẩm, khí, ánh sáng, độ ẩm đất, siêu âm
float tnd = 0, tda = 0, tkhi = 0, tas = 0, tdad = 0, tsa = 0; // Tổng các giá trị cảm biến
int i = 0;                             // Chỉ số mẫu hiện tại
bool bufferFull = false;               // Cờ báo mảng đã đầy
SemaphoreHandle_t xDataMutex;          // Semaphore để đồng bộ hóa dữ liệu

// Hàm kết nối WiFi
void WIFIConnect() {
  Serial.println("Connecting to SSID: Wokwi-GUEST"); // In thông báo kết nối WiFi
  WiFi.begin("Wokwi-GUEST", "");                     // Kết nối WiFi với SSID và không mật khẩu
  while (WiFi.status() != WL_CONNECTED) {            // Chờ đến khi kết nối thành công
    vTaskDelay(500 / portTICK_PERIOD_MS);            // Delay 500ms
    Serial.print(".");                               // In dấu chấm để biểu thị đang chờ
  }
  Serial.println("");                                // In dòng trống
  Serial.print("WiFi connected, IP address: ");      // In thông báo kết nối thành công
  Serial.println(WiFi.localIP());                    // In địa chỉ IP của ESP32
}

// Hàm kết nối lại MQTT
void MQTT_Reconnect() {
  while (!client.connected()) {                      // Lặp lại đến khi kết nối MQTT thành công
    Serial.print("Đang kết nối MQTT đến ");          // In thông báo kết nối
    Serial.print(MQTT_Server);
    Serial.println("...");
    if (client.connect(MQTT_ID)) {                    // Thử kết nối với server MQTT
      Serial.println("Kết nối thành công!");         // In thông báo thành công
      client.subscribe(MQTT_Topic_Nhan);             // Đăng ký nhận dữ liệu từ topic
      Serial.print("Đã subscribe vào impairments");      // In thông báo đã subscribe
      Serial.println(MQTT_Topic_Nhan);
    } else {
      Serial.print("Kết nối thất bại, rc=");         // In thông báo thất bại
      Serial.print(client.state());                  // In mã lỗi
      Serial.println(" - Thử lại sau 1 giây");
      vTaskDelay(1000 / portTICK_PERIOD_MS);         // Delay 1 giây trước khi thử lại
    }
  }
}

// Hàm callback xử lý dữ liệu nhận từ MQTT và hiển thị lên LCD
void callback(char* topic, byte* message, unsigned int length) {
  String stMessage;                                  // Chuỗi để lưu tin nhắn nhận được
  for (int i = 0; i < length; i++) {                 // Chuyển mảng byte thành chuỗi
    stMessage += (char)message[i];
  }
  Serial.print("Message: ");                         // In thông báo tin nhắn nhận được
  Serial.println(stMessage);

  try {
    json doc = json::parse(stMessage);               // Phân tích chuỗi JSON
    // Đọc giá trị từ JSON, mặc định là 0 nếu không tìm thấy
    int motorbechuanuoc = doc.value("motorbechuanuoc", 0);
    int htphunsuong = doc.value("htphunsuong", 0);
    int quatthongio = doc.value("quatthongio", 0);
    int htdenchieusang = doc.value("htdenchieusang", 0);
    int htledcanhbao = doc.value("htledcanhbao", 0);
    int coibao = doc.value("coibao", 0);
    int htmangche = doc.value("htmangche", 0);

    // Điều khiển chân 17 (motor và màn che)
    digitalWrite(PIN_RELAY_MOTOR_MANGCHE, (motorbechuanuoc == 1 || htmangche == 1) ? HIGH : LOW);

    // Điều khiển chân 15 (phun sương và quạt thông gió)
    digitalWrite(PIN_PHUNSUONG_QUAT, (htphunsuong == 1 || quatthongio == 1) ? HIGH : LOW);

    // Điều khiển chân 25 (còi báo động)
    digitalWrite(PIN_COIBAO, coibao == 1 ? HIGH : LOW);

    // Điều khiển đèn chiếu sáng (chân 4)
    digitalWrite(PIN_DENCHIEUSANG, htdenchieusang == 1 ? HIGH : LOW);

    // Điều khiển đèn RGB cho hệ thống cảnh báo (chân 5, 18, 19)
    if (htledcanhbao == 1) {
      digitalWrite(PIN_RGB_R, HIGH);                 // Bật màu đỏ
      digitalWrite(PIN_RGB_G, LOW);                  // Tắt màu xanh lá
      digitalWrite(PIN_RGB_B, LOW);                  // Tắt màu xanh dương
    } else {
      digitalWrite(PIN_RGB_R, LOW);                  // Tắt màu đỏ
      digitalWrite(PIN_RGB_G, LOW);                  // Tắt màu xanh lá
      digitalWrite(PIN_RGB_B, LOW);                  // Tắt màu xanh dương
    }

    // Hiển thị trạng thái lên LCD
    lcd.clear();                                    // Xóa màn hình LCD
    lcd.setCursor(0, 0);                            // Đặt con trỏ dòng 1, cột 0
    lcd.print("Device Status");                     // Hiển thị tiêu đề
    lcd.setCursor(0, 1);                            // Đặt con trỏ dòng 2, cột 0
    lcd.print("Mot:");                              // Hiển thị trạng thái Motor
    lcd.print(motorbechuanuoc ? "ON " : "OFF");
    lcd.print(" MC:");                              // Hiển thị trạng thái Màn che
    lcd.print(htmangche ? "ON " : "OFF");
    lcd.print(" PS:");                              // Hiển thị trạng thái Phun sương
    lcd.print(htphunsuong ? "ON " : "OFF");
    lcd.setCursor(0, 2);                            // Đặt con trỏ dòng 3, cột 0
    lcd.print("Quat:");                             // Hiển thị trạng thái Quạt
    lcd.print(quatthongio ? "ON " : "OFF");
    lcd.print(" DCS:");                             // Hiển thị trạng thái Đèn chiếu sáng
    lcd.print(htdenchieusang ? "ON " : "OFF");
    lcd.print(" LCB:");                             // Hiển thị trạng thái LED cảnh báo
    lcd.print(htledcanhbao ? "ON " : "OFF");
    lcd.setCursor(0, 3);                            // Đặt con trỏ dòng 4, cột 0
    lcd.print("Coi:");                              // Hiển thị trạng thái Còi
    lcd.print(coibao ? "ON " : "OFF");
    // Hiển thị thời gian cập nhật
    unsigned long seconds = millis() / 1000;        // Tính thời gian chạy (giây)
    int hours = seconds / 3600;                     // Tính giờ
    int minutes = (seconds % 3600) / 60;            // Tính phút
    int secs = seconds % 60;                        // Tính giây
    lcd.setCursor(8, 3);                            // Đặt con trỏ dòng 4, cột 8
    lcd.printf("T:%02d:%02d:%02d", hours, minutes, secs); // Hiển thị thời gian HH:MM:SS
  } catch (const json::exception& e) {               // Xử lý lỗi khi phân tích JSON
    Serial.print("Lỗi phân tích JSON: ");            // In thông báo lỗi
    Serial.println(e.what());
    lcd.clear();                                    // Xóa LCD nếu có lỗi
    lcd.setCursor(0, 0);                            // Đặt con trỏ dòng 1
    lcd.print("JSON Error");                        // Hiển thị thông báo lỗi
  }
}

// Tác vụ đọc dữ liệu cảm biến
void TaskSensorRead(void *pvParameters) {
  (void)pvParameters;                                // Bỏ qua tham số truyền vào
  for (;;) {                                         // Vòng lặp vô hạn
    float nhietdo = dht.readTemperature();           // Đọc nhiệt độ từ DHT22
    float doam = dht.readHumidity();                 // Đọc độ ẩm từ DHT22
    float anhsang = analogRead(LDR_PIN);             // Đọc giá trị ánh sáng từ LDR
    float khigas = analogRead(MQ2_PIN);              // Đọc giá trị khí từ MQ2
    float doamdat = analogRead(POTE_PIN);            // Đọc giá trị độ ẩm đất
    int hongngoai = digitalRead(PIR_PIN);            // Đọc giá trị từ cảm biến hồng ngoại

    // Đo khoảng cách bằng cảm biến siêu âm
    digitalWrite(TRIG_PIN, LOW);                     // Đặt chân Trig LOW
    delayMicroseconds(2);                            // Đợi 2 microseconds
    digitalWrite(TRIG_PIN, HIGH);                    // Đặt chân Trig HIGH
    delayMicroseconds(10);                           // Đợi 10 microseconds
    digitalWrite(TRIG_PIN, LOW);                     // Đặt chân Trig LOW
    long duration = pulseIn(ECHO_PIN, HIGH);         // Đo thời gian phản hồi từ Echo
    float sieuam = duration * 0.034 / 2;             // Tính khoảng cách (cm)

    if (!isnan(nhietdo) && !isnan(doam)) {           // Kiểm tra dữ liệu DHT hợp lệ
      xSemaphoreTake(xDataMutex, portMAX_DELAY);     // Lấy semaphore để truy cập dữ liệu
      if (i < 7) {                                   // Nếu mảng chưa đầy
        nd[i] = nhietdo;                            // Lưu nhiệt độ
        da[i] = doam;                               // Lưu độ ẩm
        khi[i] = khigas;                            // Lưu giá trị khí
        as[i] = anhsang;                            // Lưu giá trị ánh sáng
        dad[i] = doamdat;                           // Lưu giá trị độ ẩm đất
        sa[i] = sieuam;                             // Lưu giá trị siêu âm
        i++;                                        // Tăng chỉ số mẫu
        Serial.print("Sample stored, i = ");         // In thông báo lưu mẫu
        Serial.println(i);
      }

      if (i >= 7) {                                  // Nếu mảng đầy
        bufferFull = true;                           // Đặt cờ bufferFull
        i = 6;                                       // Giữ chỉ số ở vị trí cuối
        tnd = tda = tkhi = tas = tdad = tsa = 0;     // Đặt lại tổng
        for (int j = 0; j < 7; j++) {                // Tính tổng các mẫu
          tnd += nd[j];
          tda += da[j];
          tkhi += khi[j];
          tas += as[j];
          dad[j] = dad[j];
          tsa += sa[j];
        }
        for (int j = 0; j < 6; j++) {                // Dịch chuyển dữ liệu trong mảng
          nd[j] = nd[j + 1];
          da[j] = da[j + 1];
          khi[j] = khi[j + 1];
          as[j] = as[j + 1];
          dad[j] = dad[j + 1];
          sa[j] = sa[j + 1];
        }
      }
      xSemaphoreGive(xDataMutex);                    // Giải phóng semaphore
    } else {
      Serial.println("Lỗi đọc từ cảm biến DHT22!");   // In lỗi nếu đọc DHT thất bại
    }
    vTaskDelay(2000 / portTICK_PERIOD_MS);           // Delay 2 giây giữa các lần đọc
  }
}

// Tác vụ gửi dữ liệu qua MQTT
void TaskMQTTPublish(void *pvParameters) {
  (void)pvParameters;                                // Bỏ qua tham số truyền vào
  for (;;) {                                         // Vòng lặp vô hạn
    if (!client.connected()) {                       // Nếu mất kết nối MQTT
      MQTT_Reconnect();                             // Thử kết nối lại
    }
    client.loop();                                   // Xử lý các sự kiện MQTT

    xSemaphoreTake(xDataMutex, portMAX_DELAY);       // Lấy semaphore để truy cập dữ liệu
    if (bufferFull) {                                // Nếu mảng cảm biến đầy
      float avg_nd = tnd / 7;                        // Tính trung bình nhiệt độ
      float avg_da = tda / 7;                        // Tính trung bình độ ẩm
      float avg_khi = tkhi / 7;                      // Tính trung bình khí
      float avg_as = tas / 7;                        // Tính trung bình ánh sáng
      float avg_dad = tdad / 7;                      // Tính trung bình độ ẩm đất
      float avg_sa = tsa / 7;                        // Tính trung bình siêu âm

      // Tạo chuỗi JSON chứa dữ liệu trung bình
      String jsonData = "{"
        "\"humidity\":" + String(avg_da, 2) +
        ", \"temperature\":" + String(avg_nd, 2) +
        ", \"anhsang\":" + String(avg_as, 2) +
        ", \"nongdokhi\":" + String(avg_khi, 2) +
        ", \"doamdat\":" + String(avg_dad, 2) +
        ", \"sieuam\":" + String(avg_sa, 2) +
        "}";

      Serial.println("Chuỗi JSON sắp gửi:");          // In chuỗi JSON
      Serial.println(jsonData);

      bool success = client.publish(MQTT_Topic_Gui, jsonData.c_str()); // Gửi dữ liệu qua MQTT
      if (success) {
        Serial.println("MQTT publish thành công!");   // In thông báo thành công
      } else {
        Serial.print("MQTT publish thất bại! rc=");   // In thông báo thất bại
        Serial.println(client.state());
      }
      bufferFull = false;                            // Đặt lại cờ bufferFull
    }
    xSemaphoreGive(xDataMutex);                      // Giải phóng semaphore
    vTaskDelay(100 / portTICK_PERIOD_MS);            // Delay 100ms giữa các lần gửi
  }
}

// Hàm khởi tạo
void setup() {
  Serial.begin(115200);                             // Khởi tạo Serial với baud rate 115200

  // Cấu hình các chân cảm biến
  pinMode(TRIG_PIN, OUTPUT);                        // Chân Trig của siêu âm là OUTPUT
  pinMode(ECHO_PIN, INPUT);                         // Chân Echo của siêu âm là INPUT
  pinMode(MQ2_PIN, INPUT);                          // Chân MQ2 là INPUT
  pinMode(LDR_PIN, INPUT);                          // Chân LDR là INPUT
  pinMode(POTE_PIN, INPUT);                         // Chân độ ẩm đất là INPUT
  pinMode(PIR_PIN, INPUT);                          // Chân PIR là INPUT

  // Cấu hình các chân điều khiển thiết bị
  pinMode(PIN_RELAY_MOTOR_MANGCHE, OUTPUT);         // Chân relay motor/màn che là OUTPUT
  pinMode(PIN_PHUNSUONG_QUAT, OUTPUT);              // Chân phun sương/quạt là OUTPUT
  pinMode(PIN_COIBAO, OUTPUT);                      // Chân còi báo động là OUTPUT
  pinMode(PIN_RGB_R, OUTPUT);                       // Chân RGB đỏ là OUTPUT
  pinMode(PIN_RGB_G, OUTPUT);                       // Chân RGB xanh lá là OUTPUT
  pinMode(PIN_RGB_B, OUTPUT);                       // Chân RGB xanh dương là OUTPUT
  pinMode(PIN_DENCHIEUSANG, OUTPUT);                // Chân đèn chiếu sáng là OUTPUT

  // Khởi tạo trạng thái ban đầu cho các thiết bị
  digitalWrite(PIN_RELAY_MOTOR_MANGCHE, LOW);       // Tắt relay motor/màn che
  digitalWrite(PIN_PHUNSUONG_QUAT, LOW);            // Tắt phun sương/quạt
  digitalWrite(PIN_COIBAO, LOW);                    // Tắt còi báo động
  digitalWrite(PIN_RGB_R, LOW);                     // Tắt màu đỏ RGB
  digitalWrite(PIN_RGB_G, LOW);                     // Tắt màu xanh lá RGB
  digitalWrite(PIN_RGB_B, LOW);                     // Tắt màu xanh dương RGB
  digitalWrite(PIN_DENCHIEUSANG, LOW);              // Tắt đèn chiếu sáng

  WIFIConnect();                                    // Kết nối WiFi
  client.setServer(MQTT_Server, Port);              // Thiết lập server MQTT
  client.setCallback(callback);                     // Thiết lập hàm callback cho MQTT
  dht.begin();                                      // Khởi tạo cảm biến DHT
  vTaskDelay(2000 / portTICK_PERIOD_MS);            // Delay 2 giây để cảm biến ổn định
  lcd.init();                                       // Khởi tạo LCD
  lcd.backlight();                                  // Bật đèn nền LCD
  lcd.clear();                                      // Xóa màn hình LCD
  lcd.setCursor(0, 0);                              // Đặt con trỏ dòng 1
  lcd.print("Initializing...");                     // Hiển thị thông báo khởi tạo
  vTaskDelay(2000 / portTICK_PERIOD_MS);            // Delay 2 giây
  lcd.clear();                                      // Xóa màn hình sau khởi tạo

  Serial.println("Setup OK");                       // In thông báo hoàn tất setup

  xDataMutex = xSemaphoreCreateMutex();              // Tạo semaphore để đồng bộ hóa dữ liệu

  // Tạo tác vụ đọc cảm biến
  xTaskCreatePinnedToCore(
    TaskSensorRead,                                 // Hàm tác vụ
    "SensorRead",                                   // Tên tác vụ
    4096,                                           // Kích thước stack
    NULL,                                           // Tham số truyền vào
    1,                                              // Độ ưu tiên
    NULL,                                           // Handle của tác vụ
    ARDUINO_RUNNING_CORE);                          // Chạy trên core mặc định

  // Tạo tác vụ gửi dữ liệu MQTT
  xTaskCreatePinnedToCore(
    TaskMQTTPublish,                                // Hàm tác vụ
    "MQTTPublish",                                  // Tên tác vụ
    4096,                                           // Kích thước stack
    NULL,                                           // Tham số truyền vào
    1,                                              // Độ ưu tiên
    NULL,                                           // Handle của tác vụ
    ARDUINO_RUNNING_CORE);                          // Chạy trên core mặc định
}

// Vòng lặp chính (không sử dụng vì dùng FreeRTOS)
void loop() {
  // Để trống vì các tác vụ được xử lý bởi FreeRTOS
}