-- Food Spoilage Detection System
-- Database schema for Laragon (MySQL)
-- Import this with phpMyAdmin, HeidiSQL, or:
--   mysql -u root -p < schema.sql

CREATE DATABASE IF NOT EXISTS food_spoilage
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE food_spoilage;

CREATE TABLE IF NOT EXISTS sensor_data (
  id INT AUTO_INCREMENT PRIMARY KEY,
  gas_value FLOAT NOT NULL,
  ph_value FLOAT NOT NULL,
  temperature FLOAT NOT NULL,
  humidity FLOAT NOT NULL,
  status VARCHAR(20) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_created_at (created_at)
);

-- Optional: table to log alert notifications
CREATE TABLE IF NOT EXISTS alerts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sensor_data_id INT NOT NULL,
  message VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (sensor_data_id) REFERENCES sensor_data(id) ON DELETE CASCADE
);

-- Sample seed data so the dashboard has something to show immediately
INSERT INTO sensor_data (gas_value, ph_value, temperature, humidity, status, created_at) VALUES
(180, 6.8, 26.5, 60, 'Fresh',   NOW() - INTERVAL 50 MINUTE),
(210, 6.6, 27.0, 62, 'Fresh',   NOW() - INTERVAL 40 MINUTE),
(260, 6.3, 27.8, 65, 'Warning', NOW() - INTERVAL 30 MINUTE),
(300, 6.0, 28.4, 70, 'Warning', NOW() - INTERVAL 20 MINUTE),
(420, 5.2, 29.1, 75, 'Spoiled', NOW() - INTERVAL 10 MINUTE),
(250, 6.8, 28.0, 75, 'Warning', NOW());
