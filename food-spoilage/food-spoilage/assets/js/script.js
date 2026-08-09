// Food Spoilage Dashboard — live polling + Chart.js rendering
const API_BASE = "../api/";
const POLL_INTERVAL_MS = 5000;

// Normal ranges used only to size the little progress bars on each tile
const RANGES = {
  gas: { min: 0, max: 500 },
  ph: { min: 0, max: 14 },
  temperature: { min: 0, max: 50 },
  humidity: { min: 0, max: 100 },
};

const statusIcons = { Fresh: "🟢", Warning: "🟡", Spoiled: "🔴" };

let gasPhChart, tempHumidityChart;

function pct(value, key) {
  const r = RANGES[key];
  return Math.max(0, Math.min(100, ((value - r.min) / (r.max - r.min)) * 100));
}

function fmtTime(iso) {
  const d = new Date(iso.replace(" ", "T"));
  return d.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
}

function initCharts() {
  const commonOptions = {
    responsive: true,
    interaction: { mode: "index", intersect: false },
    plugins: { legend: { labels: { color: "#93a0bb" } } },
    scales: {
      x: { ticks: { color: "#93a0bb" }, grid: { color: "#2a3247" } },
      y: { ticks: { color: "#93a0bb" }, grid: { color: "#2a3247" } },
    },
  };

  gasPhChart = new Chart(document.getElementById("gasPhChart"), {
    type: "line",
    data: {
      labels: [],
      datasets: [
        { label: "Gas (ppm)", data: [], borderColor: "#5b8cff", backgroundColor: "rgba(91,140,255,0.15)", tension: 0.35, yAxisID: "y" },
        { label: "pH", data: [], borderColor: "#f5a623", backgroundColor: "rgba(245,166,35,0.15)", tension: 0.35, yAxisID: "y1" },
      ],
    },
    options: {
      ...commonOptions,
      scales: {
        x: commonOptions.scales.x,
        y: { position: "left", ticks: { color: "#93a0bb" }, grid: { color: "#2a3247" } },
        y1: { position: "right", ticks: { color: "#93a0bb" }, grid: { drawOnChartArea: false }, min: 0, max: 14 },
      },
    },
  });

  tempHumidityChart = new Chart(document.getElementById("tempHumidityChart"), {
    type: "line",
    data: {
      labels: [],
      datasets: [
        { label: "Temperature (°C)", data: [], borderColor: "#e74c3c", backgroundColor: "rgba(231,76,60,0.15)", tension: 0.35 },
        { label: "Humidity (%)", data: [], borderColor: "#2ecc71", backgroundColor: "rgba(46,204,113,0.15)", tension: 0.35 },
      ],
    },
    options: commonOptions,
  });
}

function updateTiles(d) {
  document.getElementById("gasValue").textContent = Math.round(d.gas_value);
  document.getElementById("phValue").textContent = Number(d.ph_value).toFixed(1);
  document.getElementById("tempValue").textContent = Number(d.temperature).toFixed(1);
  document.getElementById("humidityValue").textContent = Math.round(d.humidity);

  document.getElementById("gasBar").style.width = pct(d.gas_value, "gas") + "%";
  document.getElementById("phBar").style.width = pct(d.ph_value, "ph") + "%";
  document.getElementById("tempBar").style.width = pct(d.temperature, "temperature") + "%";
  document.getElementById("humidityBar").style.width = pct(d.humidity, "humidity") + "%";

  const banner = document.getElementById("statusBanner");
  banner.dataset.status = d.status;
  document.getElementById("statusIcon").textContent = statusIcons[d.status] || "⚪";
  document.getElementById("statusValue").textContent = d.status;

  document.getElementById("lastUpdated").textContent = "Updated " + fmtTime(d.created_at);
}

function updateCharts(history) {
  const labels = history.map((r) => fmtTime(r.created_at));
  gasPhChart.data.labels = labels;
  gasPhChart.data.datasets[0].data = history.map((r) => r.gas_value);
  gasPhChart.data.datasets[1].data = history.map((r) => r.ph_value);
  gasPhChart.update();

  tempHumidityChart.data.labels = labels;
  tempHumidityChart.data.datasets[0].data = history.map((r) => r.temperature);
  tempHumidityChart.data.datasets[1].data = history.map((r) => r.humidity);
  tempHumidityChart.update();
}

function updateAlerts(alerts) {
  const list = document.getElementById("alertsList");
  if (!alerts || alerts.length === 0) {
    list.innerHTML = '<li class="alert-empty">No alerts yet.</li>';
    return;
  }
  list.innerHTML = alerts
    .map(
      (a) => `
      <li class="${a.status === "Spoiled" ? "severity-spoiled" : ""}">
        ${a.message}
        <span class="alert-time">${fmtTime(a.created_at)}</span>
      </li>`
    )
    .join("");
}

async function fetchJSON(url) {
  const res = await fetch(url);
  if (!res.ok) throw new Error("Network error: " + res.status);
  return res.json();
}

async function refresh() {
  const dot = document.getElementById("liveDot");
  const text = document.getElementById("liveText");
  try {
    const [latestRes, historyRes, alertsRes] = await Promise.all([
      fetchJSON(API_BASE + "get_data.php?type=latest"),
      fetchJSON(API_BASE + "get_data.php?type=history&limit=30"),
      fetchJSON(API_BASE + "get_data.php?type=alerts&limit=8"),
    ]);

    if (latestRes.success && latestRes.data) updateTiles(latestRes.data);
    if (historyRes.success) updateCharts(historyRes.data);
    if (alertsRes.success) updateAlerts(alertsRes.data);

    dot.style.background = "#2ecc71";
    text.textContent = "Live";
  } catch (err) {
    console.error(err);
    dot.style.background = "#e74c3c";
    text.textContent = "Connection issue";
  }
}

document.addEventListener("DOMContentLoaded", () => {
  initCharts();
  refresh();
  setInterval(refresh, POLL_INTERVAL_MS);
});
