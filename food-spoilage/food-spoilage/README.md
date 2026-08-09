# Food Spoilage Detection System — Laragon Project

Full stack: **ESP32 sensors → PHP/MySQL API → interactive dashboard → Python AI model.**

## 1. Install in Laragon

1. Start Laragon, click **Menu → www directory** (usually `C:\laragon\www`).
2. Copy this entire `food-spoilage` folder into `C:\laragon\www\`.
3. Start Laragon's **Apache** and **MySQL** services (click "Start All").

## 2. Create the database

Open Laragon → **Database → HeidiSQL** (or phpMyAdmin at `http://localhost/phpmyadmin`), then run the script in:

```
sql/schema.sql
```

This creates the `food_spoilage` database, the `sensor_data` and `alerts` tables, and a few sample rows so the dashboard isn't empty on first load.

If your MySQL root user has a password, update it in `config/database.php`.

## 3. Open the dashboard

With Apache running, visit:

```
http://localhost/food-spoilage/dashboard/index.php
```

- Live tiles for gas, pH, temperature, humidity
- Status banner (Fresh / Warning / Spoiled) that changes color
- Two live line charts (auto-refreshing every 5 seconds)
- Alerts feed
- `charts.php` — full history table + status distribution chart

## 4. Point the ESP32 at your server

1. Open `esp32/food_spoilage_esp32.ino` in the Arduino IDE.
2. Install these via Library Manager: **DHT sensor library** (Adafruit), **Adafruit Unified Sensor**, **LiquidCrystal I2C**.
3. Set `ssid`, `password`, and `serverUrl` (use your PC's LAN IP, e.g. `http://192.168.1.20/food-spoilage/api/insert.php` — find your IP with `ipconfig`).
4. Wire the hardware per the table below.
5. Flash the ESP32. It will POST a reading every 5 seconds.

No hardware yet? Test the pipeline with curl:

```bash
curl -X POST http://localhost/food-spoilage/api/insert.php \
  -d "gas=320&ph=6.1&temperature=27.5&humidity=68"
```

### Wiring

| Component      | Pin on component | Connects to ESP32 |
|-----------------|------------------|--------------------|
| MQ-135 gas sensor | VCC | 5V |
| | GND | GND |
| | AOUT | GPIO34 |
| pH sensor | VCC | 5V |
| | GND | GND |
| | AOUT | GPIO35 |
| DHT11 temp/humidity | VCC | 3.3V |
| | GND | GND |
| | DATA | GPIO4 |
| 1602 LCD (via I2C backpack) | VCC | 5V |
| | GND | GND |
| | SDA | GPIO21 |
| | SCL | GPIO22 |
| 5V Buzzer | + | GPIO25 |
| | − | GND |
| Green LED (through 220 ohm resistor) | anode | GPIO26 |
| Yellow LED (through 220 ohm resistor) | anode | GPIO27 |
| Red LED (through 220 ohm resistor) | anode | GPIO14 |
| All 3 LEDs | cathode | GND |

The invoice's 2nd buzzer and the extra red/yellow/green LEDs are spares — only one of each is wired above.

The pH probe's raw ADC value is converted with a placeholder linear formula in the sketch (`readPH()`). Recalibrate it against pH 4/7/10 buffer solutions for accurate readings.

## 5. (Optional) Train the AI model

```bash
cd ai
pip install -r requirements.txt
python train_model.py      # trains on rows currently in sensor_data
python predict.py --gas 320 --temperature 27.5 --humidity 68
```

`predict.py` automatically uses the trained `model.pkl` if present, and otherwise falls back to the same rule used in `api/insert.php`, so the system works end-to-end even before you've trained a model.

## Project structure

```
food-spoilage/
├── api/
│   ├── insert.php        # ESP32 -> DB (also computes status)
│   └── get_data.php       # Dashboard <- DB (latest/history/alerts)
├── dashboard/
│   ├── index.php          # Live dashboard
│   └── charts.php         # Historical table + distribution chart
├── assets/
│   ├── css/style.css
│   └── js/script.js       # AJAX polling + Chart.js
├── config/
│   └── database.php       # PDO connection
├── ai/
│   ├── train_model.py     # Random Forest training
│   ├── predict.py         # CLI prediction (model or rule fallback)
│   └── requirements.txt
├── esp32/
│   └── food_spoilage_esp32.ino
└── sql/
    └── schema.sql
```

## Status logic

```
IF gas > 400 AND pH < 5.5   -> Spoiled
IF gas > 250                -> Warning
ELSE                        -> Fresh
```

This rule lives in three places kept in sync: `api/insert.php`, `ai/predict.py` (fallback), and the ESP32 sketch (for local LED/buzzer response even if Wi-Fi drops).
