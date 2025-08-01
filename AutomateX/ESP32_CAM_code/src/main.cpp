/*
 * SPDX-FileCopyrightText: 2024 Espressif Systems (Shanghai) CO LTD
 *
 * SPDX-License-Identifier: Apache-2.0
 *
 * MODIFIED AND FINALIZED BY GEMINI:
 * - This version is a robust, stable implementation for QR code scanning.
 * - Initializes Wi-Fi BEFORE the camera to ensure stable startup.
 * - 100% JPEG-free: Uses raw grayscale frames for QR decoding.
 * - Prevents stack overflows in the loop by using static structs.
 * - Includes comprehensive debugging feedback on both Serial and a web UI.
 * - ADDED: Live grayscale camera feed displayed on the web UI using an HTML5 Canvas.
 * - ADDED: Publishes successfully scanned QR code data to an MQTT broker.
 */

// =======================================================
// Includes
// =======================================================
#include "esp_camera.h"
#include <WiFi.h>
#include "esp_http_server.h"
#include "quirc/quirc.h"     // Using the raw quirc library for QR decoding
#include "string.h"
#include <PubSubClient.h>   // <<< NEW: For MQTT communication
#include <ArduinoJson.h>    // <<< NEW: For creating the JSON payload

// =======================================================
//   !!! CONFIGURE YOUR WIFI SETTINGS HERE !!!
// =======================================================
const char* ssid = "AHMAD";
const char* password = "12345678";
// =======================================================


// =======================================================
//   !!! CONFIGURE YOUR MQTT SETTINGS HERE !!!
// =======================================================
const char* mqtt_server = "192.168.1.104"; // <-- IMPORTANT: Enter your MQTT Broker IP or hostname
const int   mqtt_port = 1883;
const char* mqtt_topic = "esp-cam/scan/data";     // This matches your Laravel listener
// If your broker needs authentication, fill these in. Otherwise, leave them blank.
const char* mqtt_user = ""; 
const char* mqtt_password = "";
// =======================================================


// =======================================================
// Global variables
// =======================================================
struct quirc *qr_recognizer = NULL;
char qr_code_data[512] = "Point camera at a QR Code";
char system_status[128] = "Initializing...";


// <<< NEW: Variables for timed publishing
unsigned long lastPublishTime = 0;
const unsigned long publishInterval = 1000; // Interval in milliseconds (1000ms = 1 second)

// <<< NEW: Global objects for MQTT
WiFiClient espClient;
PubSubClient mqttClient(espClient);


// =======================================================
// --- CAMERA PINS - AI-THINKER MODEL ---
// =======================================================
#define PWDN_GPIO_NUM     32
#define RESET_GPIO_NUM    -1
#define XCLK_GPIO_NUM      0
#define SIOD_GPIO_NUM     26
#define SIOC_GPIO_NUM     27
#define Y9_GPIO_NUM       35
#define Y8_GPIO_NUM       34
#define Y7_GPIO_NUM       39
#define Y6_GPIO_NUM       36
#define Y5_GPIO_NUM       21
#define Y4_GPIO_NUM       19
#define Y3_GPIO_NUM       18
#define Y2_GPIO_NUM        5
#define VSYNC_GPIO_NUM    25
#define HREF_GPIO_NUM     23
#define PCLK_GPIO_NUM     22

// =======================================================
// Forward declarations
// =======================================================
static esp_err_t index_handler(httpd_req_t *req);
static esp_err_t qr_data_handler(httpd_req_t *req);
static esp_err_t status_handler(httpd_req_t *req);
static esp_err_t camera_feed_handler(httpd_req_t *req);
void startWebServer();
void reconnectMqtt(); // <<< NEW

// =======================================================
// setup() - Main initialization routine
// =======================================================
void setup() {
  Serial.begin(115200);
  Serial.setDebugOutput(true);
  Serial.println("--- ESP32-CAM QR Code Scanner (Final Version with Live Feed & MQTT) ---");
  snprintf(system_status, sizeof(system_status), "Booting up...");

  // STEP 1: Connect to Wi-Fi FIRST.
  Serial.printf("Connecting to Wi-Fi: %s\n", ssid);
  snprintf(system_status, sizeof(system_status), "Connecting to WiFi...");
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi connected!");
  Serial.print("IP Address: ");
  Serial.println(WiFi.localIP());

  // STEP 2: Initialize the Camera.
  camera_config_t config;
  config.ledc_channel = LEDC_CHANNEL_0;
  config.ledc_timer = LEDC_TIMER_0;
  config.pin_d0 = Y2_GPIO_NUM;
  config.pin_d1 = Y3_GPIO_NUM;
  config.pin_d2 = Y4_GPIO_NUM;
  config.pin_d3 = Y5_GPIO_NUM;
  config.pin_d4 = Y6_GPIO_NUM;
  config.pin_d5 = Y7_GPIO_NUM;
  config.pin_d6 = Y8_GPIO_NUM;
  config.pin_d7 = Y9_GPIO_NUM;
  config.pin_xclk = XCLK_GPIO_NUM;
  config.pin_pclk = PCLK_GPIO_NUM;
  config.pin_vsync = VSYNC_GPIO_NUM;
  config.pin_href = HREF_GPIO_NUM;
  config.pin_sccb_sda = SIOD_GPIO_NUM;
  config.pin_sccb_scl = SIOC_GPIO_NUM;
  config.pin_pwdn = PWDN_GPIO_NUM;
  config.pin_reset = RESET_GPIO_NUM;
  config.xclk_freq_hz = 16000000;
  config.pixel_format = PIXFORMAT_GRAYSCALE; 
  config.frame_size = FRAMESIZE_QVGA;       
  config.jpeg_quality = 12;                 
  config.fb_count = psramFound() ? 2 : 1;
  config.fb_location = psramFound() ? CAMERA_FB_IN_PSRAM : CAMERA_FB_IN_DRAM;
  config.grab_mode = CAMERA_GRAB_WHEN_EMPTY;

  Serial.println("Initializing camera...");
  snprintf(system_status, sizeof(system_status), "Initializing camera...");
  esp_err_t err = esp_camera_init(&config);
  if (err != ESP_OK) {
    Serial.printf("FATAL: Camera init failed with error 0x%x\n", err);
    snprintf(system_status, sizeof(system_status), "FATAL: Camera init failed. Check power/connections, then reset.");
    return;
  }
  Serial.println("Camera initialized successfully.");

  // STEP 3: Initialize the QR code recognizer.
  Serial.println("Initializing QR code recognizer...");
  qr_recognizer = quirc_new();
  if (!qr_recognizer) {
    Serial.println("FATAL: Failed to create quirc recognizer. Out of memory?");
    snprintf(system_status, sizeof(system_status), "FATAL: Failed to init QR recognizer.");
    return;
  }
  Serial.println("QR recognizer initialized.");

  // <<< NEW: STEP 4: Configure MQTT Client
  Serial.println("Configuring MQTT client...");
  mqttClient.setServer(mqtt_server, mqtt_port);
  
  // STEP 5: Start the Web Server.
  startWebServer();
  Serial.print("Web Server Ready! Go to: http://");
  Serial.println(WiFi.localIP());
  snprintf(system_status, sizeof(system_status), "Scanning for QR codes...");
}


// <<< NEW: Function to handle MQTT reconnection
void reconnectMqtt() {
  while (!mqttClient.connected()) {
    Serial.print("Attempting MQTT connection...");
    snprintf(system_status, sizeof(system_status), "Connecting to MQTT Broker...");
    
    // Create a random client ID to avoid collisions
    String clientId = "ESP32-CAM-Client-";
    clientId += String(random(0xffff), HEX);
    
    if (mqttClient.connect(clientId.c_str(), mqtt_user, mqtt_password)) {
      Serial.println("connected");
      snprintf(system_status, sizeof(system_status), "Scanning for QR codes...");
    } else {
      Serial.print("failed, rc=");
      Serial.print(mqttClient.state());
      Serial.println(" try again in 5 seconds");
      snprintf(system_status, sizeof(system_status), "MQTT Connection Failed. Retrying...");
      delay(5000); // Wait 5 seconds before retrying
    }
  }
}


// =======================================================
// loop() - Main operational loop (Timed to publish every 1 second)
// =======================================================
void loop() {
  // Ensure MQTT client is connected and maintain connection
  if (WiFi.status() == WL_CONNECTED) {
    if (!mqttClient.connected()) {
      reconnectMqtt();
    }
    mqttClient.loop();
  }

  camera_fb_t *fb = NULL;
  fb = esp_camera_fb_get();
  if (!fb) {
    Serial.println("ERROR: Camera frame buffer capture failed (for QR).");
    snprintf(system_status, sizeof(system_status), "Error: Failed to get frame for QR scan.");
    delay(1000);
    return;
  }

  if (quirc_resize(qr_recognizer, fb->width, fb->height) < 0) {
      Serial.println("ERROR: Failed to resize quirc buffer. Out of memory?");
      snprintf(system_status, sizeof(system_status), "Error: QR recognizer failed to resize.");
      esp_camera_fb_return(fb);
      delay(1000);
      return;
  }

  uint8_t *image = quirc_begin(qr_recognizer, NULL, NULL);
  memcpy(image, fb->buf, fb->len);
  quirc_end(qr_recognizer);

  esp_camera_fb_return(fb);

  int count = quirc_count(qr_recognizer);
  if (count > 0) {
    static struct quirc_code code;
    static struct quirc_data data;

    quirc_extract(qr_recognizer, 0, &code);
    quirc_decode_error_t err = quirc_decode(&code, &data);

    if (err) {
      Serial.printf("QR DECODE FAILED: %s\n", quirc_strerror(err));
      snprintf(system_status, sizeof(system_status), "Error decoding QR: %s", quirc_strerror(err));
    } else {
      // A QR code was successfully decoded.
      // Update the global qr_code_data buffer immediately for the web UI.
      snprintf(qr_code_data, sizeof(qr_code_data), "%.*s", data.payload_len, data.payload);
      snprintf(system_status, sizeof(system_status), "Last scan successful!");

      // <<< --- NEW TIMED PUBLISHING LOGIC --- >>>
      // Check if the publish interval (1 second) has passed.
      if (millis() - lastPublishTime >= publishInterval) {
        
        Serial.printf("DECODED DATA: '%s'. Time to publish.\n", qr_code_data);
        
        if (mqttClient.connected()) {
            StaticJsonDocument<256> doc;
            doc["qrData"] = qr_code_data;
            char jsonBuffer[512];
            size_t n = serializeJson(doc, jsonBuffer);
            
            Serial.printf("Publishing data to MQTT topic %s: %s\n", mqtt_topic, jsonBuffer);
            if (mqttClient.publish(mqtt_topic, jsonBuffer, n)) {
                Serial.println("MQTT Publish Success");
                // IMPORTANT: Update the timestamp to reset the timer
                lastPublishTime = millis();
            } else {
                Serial.println("MQTT Publish Failed");
            }
        } else {
            Serial.println("MQTT client not connected. Cannot publish.");
        }
      }
      // <<< --- END OF TIMED LOGIC --- >>>
    }
  }

  // The main loop delay is kept small to keep the video feed smooth
  delay(50);
}

// =======================================================
// Web Server Setup and Handlers (No changes below this line)
// =======================================================
void startWebServer(){
  httpd_handle_t server = NULL;
  httpd_config_t config = HTTPD_DEFAULT_CONFIG();
  config.max_uri_handlers = 8;

  httpd_uri_t index_uri = { .uri = "/", .method = HTTP_GET, .handler = index_handler, .user_ctx = NULL };
  httpd_uri_t qr_data_uri = { .uri = "/qr-data", .method = HTTP_GET, .handler = qr_data_handler, .user_ctx = NULL };
  httpd_uri_t status_uri = { .uri = "/status", .method = HTTP_GET, .handler = status_handler, .user_ctx = NULL };
  httpd_uri_t camera_feed_uri = { .uri = "/camera-feed", .method = HTTP_GET, .handler = camera_feed_handler, .user_ctx = NULL };

  Serial.println("Starting web server...");
  if (httpd_start(&server, &config) == ESP_OK) {
    httpd_register_uri_handler(server, &index_uri);
    httpd_register_uri_handler(server, &qr_data_uri);
    httpd_register_uri_handler(server, &status_uri);
    httpd_register_uri_handler(server, &camera_feed_uri);
    Serial.println("Web server started.");
  } else {
    Serial.println("Error starting web server!");
  }
}

static esp_err_t camera_feed_handler(httpd_req_t *req){
  camera_fb_t *fb = NULL;
  esp_err_t res = ESP_OK;
  
  fb = esp_camera_fb_get();
  if (!fb) {
    Serial.println("Camera capture failed for web feed");
    httpd_resp_send_500(req);
    return ESP_FAIL;
  }
  
  httpd_resp_set_type(req, "application/octet-stream");
  httpd_resp_set_hdr(req, "Content-Disposition", "inline; filename=capture.raw");
  httpd_resp_set_hdr(req, "Access-Control-Allow-Origin", "*");
  
  res = httpd_resp_send(req, (const char *)fb->buf, fb->len);
  
  esp_camera_fb_return(fb);
  
  return res;
}

static esp_err_t qr_data_handler(httpd_req_t *req){
  httpd_resp_set_type(req, "text/plain");
  httpd_resp_set_hdr(req, "Access-Control-Allow-Origin", "*");
  return httpd_resp_send(req, qr_code_data, strlen(qr_code_data));
}

static esp_err_t status_handler(httpd_req_t *req){
  httpd_resp_set_type(req, "text/plain");
  httpd_resp_set_hdr(req, "Access-Control-Allow-Origin", "*");
  return httpd_resp_send(req, system_status, strlen(system_status));
}

static esp_err_t index_handler(httpd_req_t *req){
  httpd_resp_set_type(req, "text/html");
  const char* html = R"rawliteral(
<!DOCTYPE html>
<html>
<head>
    <title>ESP32-CAM QR Scanner</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; text-align: center; margin: 0; padding: 20px; background-color: #f0f2f5; }
        .container { max-width: 600px; margin: auto; background-color: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        h1 { color: #1c1e21; }
        #camera-feed-canvas { display: block; width: 100%; max-width: 320px; height: auto; margin: 20px auto; border-radius: 8px; background-color: #e9ecef; border: 1px solid #dddfe2; }
        .result-box { width: 100%; padding: 20px; margin-top: 20px; border: 1px solid #dddfe2; border-radius: 8px; background-color: #f7f8fa; box-sizing: border-box; }
        h2 { margin-top: 0; color: #606770; border-bottom: 1px solid #dddfe2; padding-bottom: 10px; }
        #qr-data { font-size: 1.2em; color: #0056b3; font-weight: bold; word-wrap: break-word; min-height: 25px; }
        #status-box { margin-top: 20px; padding: 10px; font-size: 0.9em; color: #606770; background-color: #e9ecef; border-radius: 6px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>ESP32-CAM QR Code Scanner</h1>
        <canvas id="camera-feed-canvas" width="320" height="240"></canvas>
        <div class="result-box">
            <h2>Decoded QR Code Data</h2>
            <p id="qr-data">Initializing...</p>
        </div>
        <div id="status-box">
            <strong>System Status:</strong> <span id="system-status">Loading...</span>
        </div>
    </div>
    <script>
        const canvas = document.getElementById('camera-feed-canvas');
        const ctx = canvas.getContext('2d');
        const width = canvas.width;
        const height = canvas.height;
        let imageData = ctx.createImageData(width, height);
        function updateData() {
            fetch('/qr-data').then(response => response.text()).then(data => {
                document.getElementById('qr-data').innerText = data;
            }).catch(error => console.error('Error fetching QR data:', error));
            fetch('/status').then(response => response.text()).then(data => {
                document.getElementById('system-status').innerText = data;
            }).catch(error => console.error('Error fetching status:', error));
        }
        async function updateCameraFeed() {
            try {
                const response = await fetch('/camera-feed');
                if (!response.ok) {
                    console.error('Failed to fetch camera feed.');
                    requestAnimationFrame(updateCameraFeed);
                    return;
                }
                const buffer = await response.arrayBuffer();
                const grayscale = new Uint8Array(buffer);
                let data_pos = 0;
                for (let i = 0; i < grayscale.length; i++) {
                    imageData.data[data_pos++] = grayscale[i];
                    imageData.data[data_pos++] = grayscale[i];
                    imageData.data[data_pos++] = grayscale[i];
                    imageData.data[data_pos++] = 255;
                }
                ctx.putImageData(imageData, 0, 0);
            } catch (error) {
                console.error('Error fetching camera feed:', error);
            }
            requestAnimationFrame(updateCameraFeed);
        }
        setInterval(updateData, 1500);
        updateCameraFeed();
    </script>
</body>
</html>
)rawliteral";
  return httpd_resp_send(req, html, strlen(html));
}