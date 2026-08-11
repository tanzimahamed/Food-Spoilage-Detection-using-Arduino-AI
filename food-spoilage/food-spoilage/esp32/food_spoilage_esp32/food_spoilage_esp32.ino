/*
  Food Spoilage Detection — ESP32 Firmware

  Components:
  - ESP32
  - MQ-135 Gas Sensor
  - pH Sensor Module
  - DHT11 Temperature/Humidity Sensor
  - 1602 LCD with I2C
  - Buzzer
  - Green / Yellow / Red LEDs

  Data is sent to Laragon API every 5 seconds.

  WIRING
  ------------------------------------------------
  MQ-135:
    VCC  -> 5V
    GND  -> GND
    AOUT -> GPIO34

  pH Sensor:
    VCC  -> 5V
    GND  -> GND
    AOUT -> GPIO35

  DHT11:
    VCC  -> 3.3V
    GND  -> GND
    DATA -> GPIO4

  LCD1602 + I2C:
    VCC -> 5V
    GND -> GND
    SDA -> GPIO21
    SCL -> GPIO22

  Buzzer:
    + -> GPIO25
    - -> GND

  Green LED:
    Anode   -> GPIO26 through 220 ohm resistor
    Cathode -> GND

  Yellow LED:
    Anode   -> GPIO27 through 220 ohm resistor
    Cathode -> GND

  Red LED:
    Anode   -> GPIO14 through 220 ohm resistor
    Cathode -> GND
*/

#include <WiFi.h>
#include <HTTPClient.h>
#include <DHT.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
 

// =================================================
// Wi-Fi Configuration
// =================================================

const char* ssid     = "DIU ICE";
const char* password = "*****";


// =================================================
// Laragon Server
// =================================================

const char* serverUrl =
  "http://IP/food-spoilage/api/insert.php";


// =================================================
// Pin Definitions
// =================================================

#define GAS_PIN     34
#define PH_PIN      35
#define DHT_PIN     4

#define BUZZER_PIN  25

#define GREEN_LED   26
#define YELLOW_LED  27
#define RED_LED     14


// =================================================
// DHT11
// =================================================

#define DHT_TYPE DHT11

DHT dht(DHT_PIN, DHT_TYPE);


// =================================================
// LCD
// =================================================

LiquidCrystal_I2C lcd(0x27, 16, 2);


// =================================================
// Send Interval
// =================================================

const unsigned long SEND_INTERVAL_MS = 5000;


// =================================================
// pH Calibration
// =================================================
//
// IMPORTANT:
// 0.960 V should only be used here if your pH 7
// buffer solution actually produced 0.960 V.
//
// The 0.18 value is currently an assumed slope.
// For accurate calibration, measure pH 4, pH 7
// and pH 10 buffer solutions.
//
// =================================================

float readPH(int raw)
{
  float voltage = raw * (3.3 / 4095.0);

  // Current calibration
  float ph = 7.0 + ((3.30 - voltage) / 0.18);

  return ph;
}


// =================================================
// Status LED + Buzzer
// =================================================

void setStatusLEDs(String status)
{
  digitalWrite(GREEN_LED,  status == "Fresh");
  digitalWrite(YELLOW_LED, status == "Warning");
  digitalWrite(RED_LED,    status == "Spoiled");

  if (status == "Spoiled")
  {
    tone(BUZZER_PIN, 1000);
  }
  else
  {
    noTone(BUZZER_PIN);
  }
}


// =================================================
// Wi-Fi Connection
// =================================================

void connectWiFi()
{
  WiFi.begin(ssid, password);

  Serial.print("Connecting to WiFi");

  unsigned long startTime = millis();

  while (
    WiFi.status() != WL_CONNECTED &&
    millis() - startTime < 20000
  )
  {
    delay(500);
    Serial.print(".");
  }

  Serial.println();

  if (WiFi.status() == WL_CONNECTED)
  {
    Serial.println("WiFi Connected!");

    Serial.print("ESP32 IP: ");
    Serial.println(WiFi.localIP());

    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("WiFi Connected");

    lcd.setCursor(0, 1);
    lcd.print(WiFi.localIP().toString());

    delay(1500);
  }
  else
  {
    Serial.println("WiFi Connection Failed!");

    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("WiFi Failed");

    delay(1500);
  }
}


// =================================================
// SETUP
// =================================================

void setup()
{
  Serial.begin(115200);

  // Output pins
  pinMode(BUZZER_PIN, OUTPUT);

  pinMode(GREEN_LED, OUTPUT);
  pinMode(YELLOW_LED, OUTPUT);
  pinMode(RED_LED, OUTPUT);

  // Start DHT11
  dht.begin();

  // Start LCD
  lcd.init();
  lcd.backlight();

  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Food Spoilage");
  lcd.setCursor(0, 1);
  lcd.print("System Starting");

  delay(1500);

  // Wi-Fi
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Connecting WiFi");

  connectWiFi();
}


// =================================================
// LOOP
// =================================================

void loop()
{
  // ------------------------------------------------
  // Read MQ-135
  // ------------------------------------------------

  int gasRaw = analogRead(GAS_PIN);


  // ------------------------------------------------
  // Read pH Sensor
  // ------------------------------------------------

  int phRaw = analogRead(PH_PIN);

  float phVoltage = phRaw * (3.3 / 4095.0);

  float ph = readPH(phRaw);


  // ------------------------------------------------
  // Read DHT11
  // ------------------------------------------------

  float temperature = dht.readTemperature();
  float humidity    = dht.readHumidity();


  // ------------------------------------------------
  // DHT11 Error Handling
  // ------------------------------------------------

  if (isnan(temperature) || isnan(humidity))
  {
    Serial.println("Failed to read DHT11 sensor!");

    temperature = 0;
    humidity = 0;
  }


  // ------------------------------------------------
  // Determine Food Status
  // ------------------------------------------------

  String status;

  if (gasRaw > 400 && ph < 5.5)
  {
    status = "Spoiled";
  }
  else if (gasRaw > 250)
  {
    status = "Warning";
  }
  else
  {
    status = "Fresh";
  }


  // ------------------------------------------------
  // LEDs + Buzzer
  // ------------------------------------------------

  setStatusLEDs(status);


  // ------------------------------------------------
  // LCD Display
  // ------------------------------------------------

  lcd.clear();

  lcd.setCursor(0, 0);

  lcd.print("Gas:");
  lcd.print(gasRaw);

  lcd.print(" pH:");
  lcd.print(ph, 1);


  lcd.setCursor(0, 1);

  lcd.print("T:");
  lcd.print(temperature, 1);

  lcd.print("C ");
  lcd.print(status);


  // ------------------------------------------------
  // Serial Monitor
  // ------------------------------------------------

  Serial.println("--------------------------------");

  Serial.print("Gas Raw: ");
  Serial.println(gasRaw);

  Serial.print("pH Raw: ");
  Serial.println(phRaw);

  Serial.print("pH Voltage: ");
  Serial.print(phVoltage, 3);
  Serial.println(" V");

  Serial.print("pH: ");
  Serial.println(ph, 2);

  Serial.print("Temperature: ");
  Serial.print(temperature, 1);
  Serial.println(" C");

  Serial.print("Humidity: ");
  Serial.print(humidity, 1);
  Serial.println(" %");

  Serial.print("Status: ");
  Serial.println(status);


  // ------------------------------------------------
  // Send Data to Laragon
  // ------------------------------------------------

  if (WiFi.status() == WL_CONNECTED)
  {
    HTTPClient http;

    http.begin(serverUrl);

    http.addHeader(
      "Content-Type",
      "application/x-www-form-urlencoded"
    );


    String postData =
      "gas=" + String(gasRaw) +
      "&ph=" + String(ph, 2) +
      "&temperature=" + String(temperature, 1) +
      "&humidity=" + String(humidity, 1);


    int httpCode = http.POST(postData);


    Serial.print("POST response: ");
    Serial.println(httpCode);


    if (httpCode > 0)
    {
      String response = http.getString();

      Serial.print("Server response: ");
      Serial.println(response);
    }
    else
    {
      Serial.println("HTTP request failed!");
    }


    http.end();
  }
  else
  {
    Serial.println("WiFi disconnected!");

    // Try reconnecting
    connectWiFi();
  }


  // ------------------------------------------------
  // Wait 5 Seconds
  // ------------------------------------------------

  delay(SEND_INTERVAL_MS);
}
