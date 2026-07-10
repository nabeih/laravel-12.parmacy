// ── Analytics Page ────────────────────────────────────────
let revenueLineChart = null;
let topMedsChart     = null;
let currentPeriod    = '7d';
let analyticsData    = [];

async function initAnalytics() {
  setupPeriodButtons();
  await loadAnalytics('7d');
}

function setupPeriodButtons() {
  document.querySelectorAll('.period-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      currentPeriod = btn.dataset.period;
      loadAnalytics(currentPeriod);
    });
  });

  document.getElementById('btn-export-csv').addEventListener('click', exportCSV);
}

// ── Generate Mock Analytics Data ──────────────────────────
function generateData(period) {
  const today = new Date();
  const rows  = [];

  let days;
  if (period === '7d')  days = 7;
  if (period === '30d') days = 30;
  if (period === '3m')  days = 90;

  for (let i = days - 1; i >= 0; i--) {
    const d = new Date(today);
    d.setDate(d.getDate() - i);

    const seed    = d.getDate() + d.getMonth() * 31;
    const orders  = 5 + (seed % 18);
    const revenue = 120 + (seed * 37 % 600) + (orders * 28);

    rows.push({
      date: d.toISOString().split('T')[0],
      label: period === '3m'
        ? d.toLocaleDateString('en-GB', { month: 'short', day: 'numeric' })
        : d.toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric' }),
      orders,
      revenue
    });
  }
  return rows;
}

function getTopMedicines() {
  return [
    { name: 'Paracetamol',   sold: 312 },
    { name: 'Ibuprofen',     sold: 278 },
    { name: 'Amoxicillin',   sold: 245 },
    { name: 'Aspirin',       sold: 198 },
    { name: 'Omeprazole',    sold: 176 },
    { name: 'Metformin',     sold: 154 },
    { name: 'Loratadine',    sold: 143 },
    { name: 'Losartan',      sold: 128 },
    { name: 'Metronidazole', sold: 117 },
    { name: 'Vitamin C',     sold: 102 },
  ];
}

// ── Load / Render ─────────────────────────────────────────
async function loadAnalytics(period) {
  showAnalyticsLoading();
  await new Promise(r => setTimeout(r, 200)); // smooth transition

  analyticsData = generateData(period);
  const topMeds = getTopMedicines();

  renderSummaryCards();
  renderRevenueChart();
  renderTopMedsChart(topMeds);
  renderDataTable();
}

function renderSummaryCards() {
  const total    = analyticsData.reduce((s, r) => s + r.revenue, 0);
  const orders   = analyticsData.reduce((s, r) => s + r.orders,  0);
  const avgOrder = orders > 0 ? total / orders : 0;
  const topCat   = 'Antibiotics';

  document.getElementById('sum-revenue').textContent  = '₪' + Math.round(total).toLocaleString();
  document.getElementById('sum-orders').textContent   = orders.toLocaleString();
  document.getElementById('sum-avg').textContent      = '₪' + fmt(avgOrder);
  document.getElementById('sum-top-cat').textContent  = topCat;
}

function renderRevenueChart() {
  const ctx = document.getElementById('revenue-line-chart').getContext('2d');

  // Reduce label density for 30/90 day periods
  let labels, data;
  if (currentPeriod === '3m') {
    // Show every 7th point label
    labels = analyticsData.map((r, i) => i % 7 === 0 ? r.label : '');
    data   = analyticsData.map(r => r.revenue);
  } else {
    labels = analyticsData.map(r => r.label);
    data   = analyticsData.map(r => r.revenue);
  }

  if (revenueLineChart) revenueLineChart.destroy();
  revenueLineChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Revenue ₪',
        data,
        borderColor: '#1A7B4B',
        backgroundColor: 'rgba(26,123,75,0.07)',
        fill: true,
        tension: 0.4,
        pointRadius: currentPeriod === '3m' ? 0 : 3,
        pointHoverRadius: 5,
        borderWidth: 2.5,
        pointBackgroundColor: '#1A7B4B'
      }]
    },
    options: {
      responsive: true,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: { display: false },
        tooltip: { callbacks: { label: ctx => '₪' + ctx.parsed.y.toFixed(2) } }
      },
      scales: {
        x: { grid: { display: false }, ticks: { font: { size: 11 }, maxRotation: 0 } },
        y: { grid: { color: '#F3F4F6' }, ticks: { font: { size: 11 }, callback: v => '₪' + v } }
      }
    }
  });
}

function renderTopMedsChart(topMeds) {
  const ctx = document.getElementById('top-meds-chart').getContext('2d');
  if (topMedsChart) topMedsChart.destroy();

  topMedsChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: topMeds.map(m => m.name),
      datasets: [{
        label: 'Units Sold',
        data: topMeds.map(m => m.sold),
        backgroundColor: topMeds.map((_, i) => i === 0 ? '#1A7B4B' : (i < 3 ? '#4CAF82' : '#86EFAC')),
        borderRadius: 6,
        borderSkipped: false
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { color: '#F3F4F6' }, ticks: { font: { size: 11 } } },
        y: { grid: { display: false }, ticks: { font: { size: 12 } } }
      }
    }
  });
}

function renderDataTable() {
  const tbody = document.getElementById('analytics-tbody');
  tbody.innerHTML = analyticsData.map(r => `
    <tr>
      <td>${r.date}</td>
      <td>${r.orders}</td>
      <td><strong>₪${fmt(r.revenue)}</strong></td>
    </tr>`).join('');
}

// ── Export CSV ────────────────────────────────────────────
function exportCSV() {
  if (!analyticsData.length) return;
  const headers = ['Date', 'Orders', 'Revenue (₪)'];
  const rows    = analyticsData.map(r => [r.date, r.orders, r.revenue.toFixed(2)]);
  const csv     = [headers, ...rows].map(r => r.join(',')).join('\n');
  const blob    = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const url     = URL.createObjectURL(blob);
  const a       = document.createElement('a');
  a.href        = url;
  a.download    = `pharmacylink-analytics-${currentPeriod}-${new Date().toISOString().split('T')[0]}.csv`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
}

function showAnalyticsLoading() {
  // Just let the chart update; summary cards show "—"
  ['sum-revenue','sum-orders','sum-avg','sum-top-cat'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.textContent = '…';
  });
}
