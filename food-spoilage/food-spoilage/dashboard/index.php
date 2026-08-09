<?php
require_once __DIR__ . "/../config/database.php";

// Initial server-side render of the latest reading (page then keeps itself
// live via script.js polling api/get_data.php every few seconds).
$latest = $pdo->query("SELECT * FROM sensor_data ORDER BY created_at DESC LIMIT 1")->fetch();
$latest = $latest ?: [
    "gas_value" => 0, "ph_value" => 0, "temperature" => 0,
    "humidity" => 0, "status" => "Fresh", "created_at" => date("Y-m-d H:i:s"),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Food Spoilage Detection Dashboard</title>
<link rel="stylesheet" href="../assets/css/style.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
</head>
<body>

<header class="topbar">
  <div class="brand">
    <span class="brand-icon">🥗</span>
    <div>
      <h1>Food Spoilage Detection</h1>
      <p class="subtitle">Real-time monitoring &middot; ESP32 + AI</p>
    </div>
  </div>
  <div class="live-indicator">
    <span class="dot" id="liveDot"></span>
    <span id="liveText">Live</span>
    <span class="last-updated" id="lastUpdated">Updated just now</span>
  </div>
</header>

<main class="dashboard">

  <!-- Status banner -->
  <section class="status-banner" id="statusBanner" data-status="<?= htmlspecialchars($latest['status']) ?>">
    <div class="status-icon" id="statusIcon">🟢</div>
    <div>
      <div class="status-label">Current Status</div>
      <div class="status-value" id="statusValue"><?= htmlspecialchars($latest['status']) ?></div>
    </div>
  </section>

  <!-- Sensor tiles -->
  <section class="tiles">
    <div class="tile">
      <div class="tile-label">Gas Sensor (MQ-135)</div>
      <div class="tile-value"><span id="gasValue"><?= (int)$latest['gas_value'] ?></span><span class="unit">ppm</span></div>
      <div class="tile-bar"><div class="tile-bar-fill" id="gasBar"></div></div>
    </div>
    <div class="tile">
      <div class="tile-label">pH Value</div>
      <div class="tile-value"><span id="phValue"><?= number_format($latest['ph_value'],1) ?></span></div>
      <div class="tile-bar"><div class="tile-bar-fill" id="phBar"></div></div>
    </div>
    <div class="tile">
      <div class="tile-label">Temperature</div>
      <div class="tile-value"><span id="tempValue"><?= number_format($latest['temperature'],1) ?></span><span class="unit">°C</span></div>
      <div class="tile-bar"><div class="tile-bar-fill" id="tempBar"></div></div>
    </div>
    <div class="tile">
      <div class="tile-label">Humidity</div>
      <div class="tile-value"><span id="humidityValue"><?= number_format($latest['humidity'],0) ?></span><span class="unit">%</span></div>
      <div class="tile-bar"><div class="tile-bar-fill" id="humidityBar"></div></div>
    </div>
  </section>

  <!-- Charts -->
  <section class="charts">
    <div class="chart-card">
      <h2>Gas &amp; pH over Time</h2>
      <canvas id="gasPhChart" height="110"></canvas>
    </div>
    <div class="chart-card">
      <h2>Temperature &amp; Humidity over Time</h2>
      <canvas id="tempHumidityChart" height="110"></canvas>
    </div>
  </section>

  <!-- Alerts -->
  <section class="alerts-card">
    <h2>Alerts</h2>
    <ul id="alertsList" class="alerts-list">
      <li class="alert-empty">No alerts yet.</li>
    </ul>
  </section>

</main>

<script src="../assets/js/script.js"></script>
</body>
</html>
