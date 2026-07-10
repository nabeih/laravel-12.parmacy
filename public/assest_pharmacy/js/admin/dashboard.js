// ── Admin Dashboard ───────────────────────────────────────────

function initAdminDashboard() {
  document.getElementById('dash-date').textContent =
    new Date().toLocaleDateString('ar-EG', { weekday:'long', year:'numeric', month:'long', day:'numeric' });

  _renderStatCards();
  _renderOrdersChart();
  _renderCitiesChart();
  _renderStatusDistChart();
  _renderRecentOrders();
  _renderPendingPharmacies();
}

function _renderStatCards() {
  const s = mockAdminStats;
  const cards = [
    { label:'إجمالي الصيدليات',   value: fmtNum(s.totalPharmacies),    icon:'🏪', color:'blue',   trend:`+${s.pendingPharmacies} بانتظار الموافقة`, up:true },
    { label:'صيدليات نشطة',       value: fmtNum(s.activePharmacies),   icon:'✅', color:'green',  trend:'من الإجمالي', up:true },
    { label:'إجمالي المستخدمين',  value: fmtNum(s.totalUsers),         icon:'👥', color:'purple', trend:`${s.activeUsers} نشط`, up:true },
    { label:'الطلبات اليوم',      value: fmtNum(s.todayOrders),        icon:'📦', color:'orange', trend:`إجمالي: ${fmtNum(s.totalOrders)}`, up:true },
    { label:'إيرادات هذا الشهر', value:`₪${fmtMoney(s.thisMonthRevenue)}`,icon:'💰',color:'green', trend:`إجمالي: ₪${fmtMoney(s.totalRevenue)}`, up:true },
    { label:'عناصر الكتالوج',    value: fmtNum(s.catalogItems),        icon:'💊', color:'blue',   trend:'دواء مسجل', up:true },
  ];

  document.getElementById('stat-cards').innerHTML = cards.map(c => `
    <div class="admin-stat-card ${c.color}">
      <div>
        <div class="admin-stat-value">${c.value}</div>
        <div class="admin-stat-label">${c.label}</div>
        <div class="admin-stat-trend ${c.up?'up':'down'}">${c.up?'↑':'↓'} ${c.trend}</div>
      </div>
      <div class="admin-stat-icon">${c.icon}</div>
    </div>`).join('');
}

function _renderOrdersChart() {
  const labels = [];
  const data   = [];
  for (let i = 29; i >= 0; i--) {
    const d = new Date(Date.now() - i * 86400000);
    labels.push(d.toLocaleDateString('ar-EG', { month:'numeric', day:'numeric' }));
    // Realistic curve peaking mid-week
    const base = 30 + Math.sin(i * 0.4) * 12 + Math.random() * 15;
    data.push(Math.round(base));
  }

  new Chart(document.getElementById('ordersChart'), {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label:           'عدد الطلبات',
        data,
        borderColor:     '#2563eb',
        backgroundColor: 'rgba(37,99,235,0.08)',
        fill:            true,
        tension:         0.35,
        pointRadius:     2,
        borderWidth:     2,
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: {
        x: { ticks: { maxTicksLimit: 7, font: { size: 10 } } },
        y: { beginAtZero: true, ticks: { font: { size: 10 } } }
      }
    }
  });
}

function _renderCitiesChart() {
  const cityCount = {};
  mockAllPharmacies.forEach(p => {
    cityCount[p.city] = (cityCount[p.city] || 0) + 1;
  });

  const colors = ['#2563eb','#22c55e','#f97316','#a855f7','#ef4444','#06b6d4'];

  new Chart(document.getElementById('citiesChart'), {
    type: 'doughnut',
    data: {
      labels:   Object.keys(cityCount),
      datasets: [{
        data:            Object.values(cityCount),
        backgroundColor: colors,
        borderWidth:     0,
        hoverOffset:     6,
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'bottom', labels: { font: { size: 12 }, boxWidth: 14 } }
      }
    }
  });
}

function _renderStatusDistChart() {
  const statusAr = { delivered:'تم التسليم', confirmed:'مؤكد', shipping:'جارٍ التوصيل', pending:'قيد الانتظار', cancelled:'ملغى' };
  const counts = {};
  mockAdminOrders.forEach(o => {
    const label = statusAr[o.status] || o.status;
    counts[label] = (counts[label] || 0) + 1;
  });

  new Chart(document.getElementById('statusDistChart'), {
    type: 'doughnut',
    data: {
      labels:   Object.keys(counts),
      datasets: [{
        data:            Object.values(counts),
        backgroundColor: ['#22c55e','#3b82f6','#a855f7','#f97316','#ef4444'],
        borderWidth:     0,
        hoverOffset:     6,
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'bottom', labels: { font: { size: 12 }, boxWidth: 14 } }
      }
    }
  });
}

function _renderRecentOrders() {
  const tbody = document.querySelector('#recent-orders-tbl tbody');
  const orders = [...mockAdminOrders].sort((a, b) => b.createdAt - a.createdAt).slice(0, 6);
  tbody.innerHTML = orders.map(o => `
    <tr>
      <td><span style="font-weight:600">${esc(o.id)}</span></td>
      <td>${esc(o.userName)}</td>
      <td>${esc(o.pharmacyName)}</td>
      <td>₪${fmtMoney(o.total)}</td>
      <td>${orderStatusBadge(o.status)}</td>
    </tr>`).join('');
}

function _renderPendingPharmacies() {
  const tbody   = document.querySelector('#pending-pharm-tbl tbody');
  const pending = mockAllPharmacies.filter(p => p.status === 'pending');

  if (!pending.length) {
    tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:20px">لا توجد طلبات معلقة</td></tr>`;
    return;
  }

  tbody.innerHTML = pending.map(p => `
    <tr>
      <td><strong>${esc(p.name)}</strong></td>
      <td>${esc(p.city)}</td>
      <td>${fmtDate(p.joinedAt)}</td>
      <td>
        <button class="admin-btn admin-btn-success admin-btn-sm"
                onclick="_approvePharm('${p.id}')">موافقة</button>
      </td>
    </tr>`).join('');
}

function _approvePharm(id) {
  const p = mockAllPharmacies.find(x => x.id === id);
  if (!p) return;
  p.status = 'active';
  mockAdminStats.pendingPharmacies = Math.max(0, mockAdminStats.pendingPharmacies - 1);
  mockAdminStats.activePharmacies++;
  _renderPendingPharmacies();
  _renderStatCards();
}
