// ── Dashboard Page ────────────────────────────────────────
let revenueChart = null;
let categoryChart = null;

async function initDashboard() {
  setDashboardDate();
  await Promise.all([loadStats(), loadCharts(), loadRecentOrders(), loadLowStockAlerts()]);
}

function setDashboardDate() {
  const el = document.getElementById('dashboard-date');
  if (el) {
    el.textContent = new Date().toLocaleDateString('en-GB', {
      weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
    });
  }
}

// ── Stats ─────────────────────────────────────────────────
async function loadStats() {
  let products = [];
  let orders   = [];

  try {
    if (!DEMO_MODE) {
      const [pSnap, oSnap] = await Promise.all([
        db.collection('products').get(),
        db.collection('orders').get()
      ]);
      products = pSnap.docs.map(d => d.data());
      orders   = oSnap.docs.map(d => d.data());
    } else {
      products = mockProducts;
      orders   = mockOrders;
    }
  } catch (e) {
    console.warn('Stats: using mock data', e);
    products = mockProducts;
    orders   = mockOrders;
  }

  const today = new Date().toDateString();
  const todayOrders = orders.filter(o => {
    const d = o.createdAt instanceof Date ? o.createdAt : new Date((o.createdAt?.seconds||0)*1000);
    return d.toDateString() === today;
  });

  const todayRevenue = todayOrders.reduce((s, o) => s + (o.total || 0), 0);
  const lowStock     = products.filter(p => p.stock <= p.minStock).length;

  document.getElementById('stat-revenue').textContent   = '₪' + fmt(todayRevenue);
  document.getElementById('stat-orders').textContent    = todayOrders.length;
  document.getElementById('stat-low-stock').textContent = lowStock;
  document.getElementById('stat-products').textContent  = products.length;
}

// ── Charts ────────────────────────────────────────────────
function loadCharts() {
  // Weekly Revenue — Line/Area
  const revCtx = document.getElementById('revenue-chart').getContext('2d');
  if (revenueChart) revenueChart.destroy();
  revenueChart = new Chart(revCtx, {
    type: 'line',
    data: {
      labels: ['Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
      datasets: [{
        label: 'Revenue ₪',
        data: [1850, 2200, 1750, 2800, 2400, 3100, 2600],
        borderColor: '#1A7B4B',
        backgroundColor: 'rgba(26,123,75,0.08)',
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#1A7B4B',
        pointRadius: 4,
        pointHoverRadius: 6,
        borderWidth: 2.5
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => '₪' + ctx.parsed.y.toFixed(2) } } },
      scales: {
        x: { grid: { display: false }, ticks: { font: { size: 12 } } },
        y: { grid: { color: '#F3F4F6' }, ticks: { font: { size: 12 }, callback: v => '₪' + v } }
      }
    }
  });

  // Category Doughnut
  const catCtx = document.getElementById('category-chart').getContext('2d');
  if (categoryChart) categoryChart.destroy();
  categoryChart = new Chart(catCtx, {
    type: 'doughnut',
    data: {
      labels: ['Antibiotics', 'Pain Relief', 'Vitamins', 'Chronic', 'Other'],
      datasets: [{
        data: [35, 25, 20, 12, 8],
        backgroundColor: ['#1A7B4B','#4CAF82','#86EFAC','#065F46','#A7F3D0'],
        borderColor: 'white',
        borderWidth: 3,
        hoverOffset: 6
      }]
    },
    options: {
      responsive: true,
      cutout: '65%',
      plugins: {
        legend: { position: 'bottom', labels: { padding: 14, font: { size: 12 }, usePointStyle: true } },
        tooltip: { callbacks: { label: ctx => ctx.label + ': ' + ctx.parsed + '%' } }
      }
    }
  });
}

// ── Recent Orders ─────────────────────────────────────────
async function loadRecentOrders() {
  const tbody = document.getElementById('recent-orders-tbody');
  tbody.innerHTML = '<tr><td colspan="5"><div class="loading-state"><div class="spinner"></div></div></td></tr>';

  let orders = [];
  try {
    if (!DEMO_MODE) {
      const snap = await db.collection('orders').orderBy('createdAt','desc').limit(5).get();
      orders = snap.docs.map(d => ({ id: d.id, ...d.data() }));
    } else {
      orders = mockOrders.slice(0, 5);
    }
  } catch (e) {
    console.warn('Recent orders: using mock', e);
    orders = mockOrders.slice(0, 5);
  }

  if (!orders.length) {
    tbody.innerHTML = '<tr><td colspan="5" class="loading-state">No orders found</td></tr>';
    return;
  }

  tbody.innerHTML = orders.map((o, i) => `
    <tr>
      <td><span class="fw-600">${o.orderNum || 'ORD-100' + (i+1)}</span></td>
      <td dir="auto">${o.customerName}</td>
      <td><span class="fw-600 text-success">₪${fmt(o.total)}</span></td>
      <td>${statusBadge(o.status)}</td>
      <td class="text-muted">${relativeTime(o.createdAt)}</td>
    </tr>
  `).join('');
}

// ── Low Stock Alerts ──────────────────────────────────────
async function loadLowStockAlerts() {
  const list = document.getElementById('low-stock-list');
  list.innerHTML = '<li class="loading-state"><div class="spinner spinner-sm" style="margin:auto"></div></li>';

  let products = [];
  try {
    if (!DEMO_MODE) {
      const snap = await db.collection('products').get();
      products = snap.docs.map(d => ({ id: d.id, ...d.data() }));
    } else {
      products = mockProducts;
    }
  } catch (e) {
    products = mockProducts;
  }

  const alerts = products.filter(p => p.stock <= p.minStock);

  if (!alerts.length) {
    list.innerHTML = '<li class="empty-state"><span class="empty-icon">✅</span><p>All products are well-stocked</p></li>';
    return;
  }

  list.innerHTML = alerts.slice(0, 6).map(p => {
    const isOut = p.stock === 0;
    return `
      <li class="alert-item">
        <div class="alert-dot ${isOut ? 'out' : ''}"></div>
        <div class="alert-info">
          <div class="alert-name" dir="auto">${p.nameEn} <span class="text-muted" style="font-weight:400;font-size:12px">(${p.nameAr})</span></div>
          <div class="alert-stock">Stock: ${p.stock} / Min: ${p.minStock}</div>
        </div>
        <a href="inventory.html" class="btn btn-sm btn-outline">Reorder</a>
      </li>`;
  }).join('');
}
