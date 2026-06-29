#include <WiFi.h>
#include <HTTPClient.h>

const char* ssid = "";
const char* password = "";

const char* serverUrl = "http://192.168.1.66:8888/api/door/signal";
const char* apiKey = "";
const char* deviceName = "esp32-front-door";

const int doorPin = 18;
const int ledPin = 2;

bool lastRawState;
bool stableState;
bool pendingNotify = false;

unsigned long lastChangeMs = 0;
unsigned long lastWifiTryMs = 0;
unsigned long lastPostTryMs = 0;

const unsigned long debounceMs = 75;
const unsigned long wifiRetryMs = 5000;
const unsigned long postRetryMs = 3000;

void tryConnectWiFi() {
  if (WiFi.status() == WL_CONNECTED) return;
  if (millis() - lastWifiTryMs < wifiRetryMs) return;

  lastWifiTryMs = millis();

  Serial.println("Trying WiFi...");
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);
}

bool notifyBackend() {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("No WiFi, cannot POST");
    return false;
  }

  HTTPClient http;
  http.begin(serverUrl);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("X-API-Key", apiKey);
  http.setTimeout(3000);

  String body = "{\"esp32_device_name\":\"";
  body += deviceName;
  body += "\"}";

  Serial.print("POST body: ");
  Serial.println(body);

  int code = http.POST(body);
  String response = http.getString();

  Serial.print("HTTP code: ");
  Serial.println(code);
  Serial.print("Response: ");
  Serial.println(response);

  http.end();

  return code >= 200 && code < 300;
}

void setup() {
  Serial.begin(115200);
  delay(1000);

  Serial.println("Booting door sensor...");

  pinMode(doorPin, INPUT_PULLUP);
  pinMode(ledPin, OUTPUT);

  lastRawState = digitalRead(doorPin);
  stableState = lastRawState;

  digitalWrite(ledPin, stableState == LOW ? HIGH : LOW);

  Serial.print("Initial state: ");
  Serial.println(stableState == LOW ? "Door Closed" : "Door Open");

  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);
}

void loop() {
  tryConnectWiFi();

  if (WiFi.status() == WL_CONNECTED) {
    static bool printedIp = false;
    if (!printedIp) {
      printedIp = true;
      Serial.print("WiFi connected. IP: ");
      Serial.println(WiFi.localIP());
    }
  }

  bool raw = digitalRead(doorPin);

  // LED always follows sensor, even if WiFi/HTTP is broken
  digitalWrite(ledPin, raw == LOW ? HIGH : LOW);

  if (raw != lastRawState) {
    lastRawState = raw;
    lastChangeMs = millis();

    Serial.print("Raw changed: ");
    Serial.println(raw == LOW ? "Closed" : "Open");
  }

  if ((millis() - lastChangeMs) > debounceMs && raw != stableState) {
    stableState = raw;

    Serial.print("Stable changed: ");
    Serial.println(stableState == LOW ? "Door Closed" : "Door Open");

    pendingNotify = true;
  }

  if (pendingNotify && millis() - lastPostTryMs > postRetryMs) {
    lastPostTryMs = millis();

    if (notifyBackend()) {
      Serial.println("Backend notified OK");
      pendingNotify = false;
    } else {
      Serial.println("Backend notify failed, will retry");
    }
  }

  delay(10);
}