// ── Admin Reports ─────────────────────────────────────────────

let _charts        = {};
let _rangeDays     = 7;
let _dailyData     = [];
let _monthlyData   = [];
let _statusData    = {};
let _topPharmData  = [];
let _userGrowthData= [];

function initAdminReports() {
  _setDefaultDates();
  _quickRange(7, document.getElementById('qb-7'));
}

function _setDefaultDates() {
  const now  = new Date();
  const from = new Date(now - 7 * 86400000);
  document.getElementById('rpt-to').value   = now.toISOString().slice(0, 10);
  document.getElementById('rpt-from').value = from.toISOString().slice(0, 10);
}

function _quickRange(days, btn) {
  document.querySelectorAll('.admin-quick-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  _rangeDays = days;

  const now  = new Date();
  const from = new Date(now - days * 86400000);
  document.getElementById('rpt-to').value   = now.toISOString().slice(0, 10);
  document.getElementById('rpt-from').value = from.toISOString().slice(0, 10);

  _generateData(days);
  _renderSummary();
  _renderAllCharts();
}

function _applyDateRange() {
  const from = new Date(document.getElementById('rpt-from').value);
  const to   = new Date(document.getElementById('rpt-to').value);
  if (isNaN(from) || isNaN(to) || from > to) { alert('نطاق تاريخ غير صالح'); return; }
  const days = Math.ceil((to - from) / 86400000);
  document.querySelectorAll('.admin-quick-btn').forEach(b => b.classList.remove('active'));
  _rangeDays = days;
  _generateData(days);
  _renderSummary();
  _renderAllCharts();
}

function _generateData(days) {
  // Daily orders
  _dailyData = [];
  for (let i = days - 1; i >= 0; i--) {
    const date = new Date(Date.now() - i * 86400000);
    const base = 28 + Math.sin(i * 0.5) * 10 + Math.random() * 18;
    _dailyData.push({
      label: date.toLocaleDateString('ar-EG', { month:'numeric', day:'numeric' }),
      value: Math.round(base)
    });
  }

  // Monthly revenue (last 12 months)
  const months = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
  _monthlyData = [];
  const currentMonth = new Date().getMonth();
  for (let i = 11; i >= 0; i--) {
    const mIdx = (currentMonth - i + 12) % 12;
    const base = 4000 + Math.sin(i * 0.6) * 1500 + Math.random() * 2000;
    _monthlyData.push({ label: months[mIdx], value: Math.round(base) });
  }

  // Status distribution from real orders
  const statusAr = { delivered:'تم التسليم', confirmed:'مؤكد', shipping:'جارٍ التوصيل', pending:'قيد الانتظار', cancelled:'ملغى' };
  _statusData = {};
  mockAdminOrders.forEach(o => {
    const label = statusAr[o.status] || o.status;
    _statusData[label] = (_statusData[label] || 0) + 1;
  });

  // Top 5 pharmacies by revenue
  _topPharmData = [...mockAllPharmacies]
    .sort((a, b) => b.revenue - a.revenue)
    .slice(0, 5)
    .map(p => ({ label: p.name, value: p.revenue }));

  // User growth (last 6 months)
  _userGrowthData = [];
  for (let i = 5; i >= 0; i--) {
    const mIdx = (currentMonth - i + 12) % 12;
    _userGrowthData.push({ label: months[mIdx], value: Math.round(20 + Math.random() * 60 + (5-i) * 8) });
  }
}

function _renderSummary() {
  const totalOrders  = _dailyData.reduce((s, d) => s + d.value, 0);
  const avgDaily     = totalOrders ? Math.round(totalOrders / _dailyData.length) : 0;
  const totalRevenue = _monthlyData.slice(-Math.ceil(_rangeDays / 30) || 1).reduce((s, m) => s + m.value, 0);
  const newUsers     = _userGrowthData.reduce((s, u) => s + u.value, 0);

  document.getElementById('rpt-summary').innerHTML = `
    <div class="admin-stat-card blue">
      <div><div class="admin-stat-value">${fmtNum(totalOrders)}</div><div class="admin-stat-label">إجمالي الطلبات</div></div>
      <div class="admin-stat-icon">📦</div>
    </div>
    <div class="admin-stat-card green">
      <div><div class="admin-stat-value">₪${fmtMoney(totalRevenue)}</div><div class="admin-stat-label">الإيرادات التقديرية</div></div>
      <div class="admin-stat-icon">💰</div>
    </div>
    <div class="admin-stat-card orange">
      <div><div class="admin-stat-value">${fmtNum(avgDaily)}</div><div class="admin-stat-label">متوسط الطلبات اليومي</div></div>
      <div class="admin-stat-icon">📊</div>
    </div>
    <div class="admin-stat-card purple">
      <div><div class="admin-stat-value">${fmtNum(newUsers)}</div><div class="admin-stat-label">مستخدمون جدد</div></div>
      <div class="admin-stat-icon">👥</div>
    </div>`;
}

function _destroyChart(key) {
  if (_charts[key]) { _charts[key].destroy(); delete _charts[key]; }
}

function _renderAllCharts() {
  _renderDailyOrders();
  _renderMonthlyRevenue();
  _renderStatusDist();
  _renderTopPharm();
  _renderUserGrowth();
}

function _renderDailyOrders() {
  _destroyChart('daily');
  _charts.daily = new Chart(document.getElementById('dailyOrdersChart'), {
    type: 'line',
    data: {
      labels:   _dailyData.map(d => d.label),
      datasets: [{
        label:           'طلبات',
        data:            _dailyData.map(d => d.value),
        borderColor:     '#2563eb',
        backgroundColor: 'rgba(37,99,235,0.08)',
        fill: true, tension: 0.35, pointRadius: 2, borderWidth: 2
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: {
        x: { ticks: { maxTicksLimit: 8, font:{size:10} } },
        y: { beginAtZero: true, ticks:{font:{size:10}} }
      }
    }
  });
}

function _renderMonthlyRevenue() {
  _destroyChart('monthly');
  _charts.monthly = new Chart(document.getElementById('monthlyRevenueChart'), {
    type: 'bar',
    data: {
      labels:   _monthlyData.map(m => m.label),
      datasets: [{
        label:           'الإيرادات (₪)',
        data:            _monthlyData.map(m => m.value),
        backgroundColor: 'rgba(34,197,94,0.7)',
        borderColor:     '#22c55e',
        borderWidth:     1,
        borderRadius:    4
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: {
        x: { ticks:{font:{size:10}} },
        y: { beginAtZero:true, ticks:{font:{size:10}} }
      }
    }
  });
}

function _renderStatusDist() {
  _destroyChart('status');
  const colors = ['#22c55e','#3b82f6','#a855f7','#f97316','#ef4444'];
  _charts.status = new Chart(document.getElementById('statusDistChart'), {
    type: 'doughnut',
    data: {
      labels:   Object.keys(_statusData),
      datasets: [{
        data:            Object.values(_statusData),
        backgroundColor: colors,
        borderWidth:     0,
        hoverOffset:     6
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { position:'bottom', labels:{font:{size:11},boxWidth:12} } }
    }
  });
}

function _renderTopPharm() {
  _destroyChart('topPharm');
  _charts.topPharm = new Chart(document.getElementById('topPharmChart'), {
    type: 'bar',
    data: {
      labels:   _topPharmData.map(p => p.label),
      datasets: [{
        label:           'الإيرادات (₪)',
        data:            _topPharmData.map(p => p.value),
        backgroundColor: ['#2563eb','#22c55e','#f97316','#a855f7','#ef4444'],
        borderWidth:     0,
        borderRadius:    6
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      plugins: { legend: { display:false } },
      scales: {
        x: { beginAtZero:true, ticks:{font:{size:10}} },
        y: { ticks:{font:{size:11}} }
      }
    }
  });
}

function _renderUserGrowth() {
  _destroyChart('growth');
  _charts.growth = new Chart(document.getElementById('userGrowthChart'), {
    type: 'line',
    data: {
      labels:   _userGrowthData.map(u => u.label),
      datasets: [{
        label:           'مستخدم جديد',
        data:            _userGrowthData.map(u => u.value),
        borderColor:     '#a855f7',
        backgroundColor: 'rgba(168,85,247,0.08)',
        fill: true, tension: 0.35, pointRadius: 4, borderWidth: 2
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display:false } },
      scales: {
        x: { ticks:{font:{size:11}} },
        y: { beginAtZero:true, ticks:{font:{size:10}} }
      }
    }
  });
}

// ── CSV exports ───────────────────────────────────────────────

function _exportDailyOrders() {
  exportCsv('daily_orders.csv', ['التاريخ','عدد الطلبات'],
    _dailyData.map(d => [d.label, d.value]));
}

function _exportMonthlyRevenue() {
  exportCsv('monthly_revenue.csv', ['الشهر','الإيرادات (₪)'],
    _monthlyData.map(m => [m.label, m.value]));
}

function _exportStatusDist() {
  exportCsv('order_status.csv', ['الحالة','عدد الطلبات'],
    Object.entries(_statusData).map(([k,v]) => [k, v]));
}

function _exportTopPharm() {
  exportCsv('top_pharmacies.csv', ['الصيدلية','الإيرادات (₪)'],
    _topPharmData.map(p => [p.label, p.value]));
}

function _exportUserGrowth() {
  exportCsv('user_growth.csv', ['الشهر','مستخدمون جدد'],
    _userGrowthData.map(u => [u.label, u.value]));
}

function _exportAllReports() {
  _exportDailyOrders();
  setTimeout(_exportMonthlyRevenue, 300);
  setTimeout(_exportStatusDist, 600);
  setTimeout(_exportTopPharm, 900);
  setTimeout(_exportUserGrowth, 1200);
}
