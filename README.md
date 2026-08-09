# Food-Spoilage-Detection-using-Arduino-AI
An IoT-based smart food spoilage detection system using Arduino, ESP32, sensors, and AI/ML for real-time food quality monitoring and spoilage prediction.

## IoT Based Food Spoilage Detection System using Arduino + AI

<div align="center">

![Status](https://img.shields.io/badge/Status-In_Development-orange)
![Platform](https://img.shields.io/badge/Platform-ESP32-blue)
![Arduino](https://img.shields.io/badge/Arduino-IoT-success)
![AI](https://img.shields.io/badge/AI-Machine_Learning-red)
![Python](https://img.shields.io/badge/Python-3.x-yellow)
![License](https://img.shields.io/badge/License-MIT-green)

An Intelligent IoT & AI-powered system for real-time food quality monitoring and food spoilage prediction.

</div>

---

## Overview

Food spoilage causes significant economic loss and health risks worldwide.
This project proposes an intelligent monitoring system that combines IoT sensors with Machine Learning to detect food spoilage before it becomes unsafe. The system continuously monitors environmental conditions, analyzes sensor data, predicts food quality, and sends real-time alerts to users.

---

## Features

- 🌡 Temperature Monitoring
- 💧 Humidity Monitoring
- 🌫 Gas Detection
- 🧪 pH Monitoring
- 🤖 AI-based Spoilage Prediction
- ☁ Cloud Data Logging
- 📱 Mobile Notification
- 🔔 Buzzer Alert
- 💡 LED Status Indicator
- 📊 Real-time Dashboard

---

## System Architecture

```
Sensors
│
├── MQ Gas Sensor
├── DHT11
├── pH Sensor
│
▼
ESP32
│
▼
PHP API (insert.php / get_data.php)
│
▼
MySQL Database
│
▼
Web Dashboard + AI Model (Random Forest)
│
▼
Prediction
│
▼
Fresh 🟢
Warning 🟡
Spoiled 🔴
```

---

## Machine Learning

**Algorithm**
- Random Forest Classifier (Scikit-learn)

**Output Classes**
- Fresh
- Warning
- Spoiled

---

## Repository Structure

```
Food-Spoilage-Detection-using-Arduino-AI/
│
├── Documentation/
│   └── (project report & proposal PDFs)
│
├── food-spoilage/
│   └── food-spoilage/
│       ├── ai/
│       │   ├── train_model.py
│       │   ├── predict.py
│       │   └── requirements.txt
│       ├── api/
│       │   ├── insert.php
│       │   └── get_data.php
│       ├── assets/
│       │   ├── css/style.css
│       │   └── js/script.js
│       ├── config/
│       │   └── database.php
│       ├── dashboard/
│       │   ├── index.php
│       │   └── charts.php
│       ├── esp32/
│       │   └── food_spoilage_esp32/
│       │       └── food_spoilage_esp32.ino
│       ├── sql/
│       │   └── schema.sql
│       └── README.md
│
├── .gitignore
└── README.md
```

---

## Project Documentation

The complete project report and proposal are available in the [`Documentation/`](./Documentation) folder.

---

## 📂 Project Output

Photos, videos, and other output files from the working prototype are available in this Google Drive folder:

🔗 **[View Project Output](https://drive.google.com/drive/folders/1jlOjuAvabEr9TWdR7yY2bnmZBXtW04KP?usp=sharing)**

---

## Author

**Tanzim Ahamed**
Information & Communication Engineering (ICE)
AI • Machine Learning • IoT Enthusiast

📧 tanzim.ahamed.bd@gmail.com
🔗 GitHub: https://github.com/tanzimahamed
🔗 LinkedIn: https://linkedin.com/in/tanzim-ahamed-

---

<div align="center">

⭐ If you found this project useful, consider giving it a star!

</div>
