<?php
require_once __DIR__ . "/../config/database.php";
$rows = $pdo->query(
    "SELECT * FROM sensor_data ORDER BY created_at DESC LIMIT 200"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Historical Data — Food Spoilage Detection</title>
<link rel="stylesheet" href="../assets/css/style.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
</head>
<body>

<header class="topbar">
  <div class="brand">
    <span class="brand-icon">📈</span>
    <div>
      <h1>Historical Data</h1>
      <p class="subtitle">Last <?= count($rows) ?> readings</p>
    </div>
  </div>
  <a href="index.php" class="back-link">&larr; Back to live dashboard</a>
</header>

<main class="dashboard">
  <section class="chart-card full-width">
    <h2>Status Distribution</h2>
    <canvas id="statusChart" height="90"></canvas>
  </section>

  <section class="table-card">
    <h2>Reading Log</h2>
    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>Time</th><th>Gas (ppm)</th><th>pH</th><th>Temp (°C)</th><th>Humidity (%)</th><th>Status</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr class="row-<?= strtolower($r['status']) ?>">
            <td><?= htmlspecialchars($r['created_at']) ?></td>
            <td><?= (int)$r['gas_value'] ?></td>
            <td><?= number_format($r['ph_value'],1) ?></td>
            <td><?= number_format($r['temperature'],1) ?></td>
            <td><?= number_format($r['humidity'],0) ?></td>
            <td><span class="badge badge-<?= strtolower($r['status']) ?>"><?= htmlspecialchars($r['status']) ?></span></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</main>

<script>
const rows = <?= json_encode(array_reverse($rows)) ?>;
const counts = { Fresh: 0, Warning: 0, Spoiled: 0 };
rows.forEach(r => { counts[r.status] = (counts[r.status] || 0) + 1; });

new Chart(document.getElementById('statusChart'), {
  type: 'bar',
  data: {
    labels: ['Fresh', 'Warning', 'Spoiled'],
    datasets: [{
      label: 'Number of readings',
      data: [counts.Fresh, counts.Warning, counts.Spoiled],
      backgroundColor: ['#2ecc71', '#f5a623', '#e74c3c']
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
  }
});
</script>
</body>
</html>
