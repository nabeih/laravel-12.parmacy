let _revChart = null, _catChart = null;

async function initPharmacistDashboard() {
  const user = window.currentUser || {};
  const el = document.getElementById('dash-greeting');
  if (el) el.textContent = `مرحباً، ${user.name || 'الصيدلاني'} 👋`;
  const dateEl = document.getElementById('dash-date');
  if (dateEl) dateEl.textContent = new Date().toLocaleDateString('ar-EG', { weekday:'long', year:'numeric', month:'long', day:'numeric' });

  await Promise.all([_loadStats(), _loadCharts(), _loadRecentOrders(), _loadLowStock()]);
}

async function _loadStats() {
  let products = mockProducts;
  let orders   = mockPharmacistOrders;
  // TODO: Laravel API GET /api/pharmacist/dashboard-stats
  try {
    if (!DEMO_MODE) {
      const uid = window.currentUser?.uid;
      const [pSnap, oSnap] = await Promise.all([
        db.collection('products').where('pharmacyId','==',uid).get(),
        db.collection('orders').where('pharmacyId','==',uid).get()
      ]);
      products = pSnap.docs.map(d => d.data());
      orders   = oSnap.docs.map(d => d.data());
    }
  } catch(e) {}

  const today = new Date().toDateString();
  const todayOrders = orders.filter(o => new Date(o.date || (o.createdAt?.seconds*1000)).toDateString() === today);
  const todayRev = todayOrders.reduce((s,o) => s + (o.total||0), 0);
  const lowCount = products.filter(p => p.stock <= p.minStock).length;

  document.getElementById('s-revenue').textContent  = '₪' + fmt(todayRev);
  document.getElementById('s-orders').textContent   = todayOrders.length;
  document.getElementById('s-alerts').textContent   = lowCount;
  document.getElementById('s-products').textContent = products.length;
  const trend = document.getElementById('s-revenue-trend');
  if (trend) { trend.textContent = '↑ 12% من الأمس'; trend.className = 'stat-trend up'; }
}

function _loadCharts() {
  // Revenue area chart
  const r = document.getElementById('revenue-chart')?.getContext('2d');
  if (r) {
    if (_revChart) _revChart.destroy();
    _revChart = new Chart(r, {
      type: 'line',
      data: {
        labels:   mockWeeklyRevenue.map(d => d.day),
        datasets: [{ label:'الإيرادات ₪', data: mockWeeklyRevenue.map(d => d.amount),
          borderColor:'#1A7B4B', backgroundColor:'rgba(26,123,75,.1)',
          fill:true, tension:.4, pointBackgroundColor:'#1A7B4B', pointRadius:4, borderWidth:2.5 }]
      },
      options: { responsive:true, plugins:{ legend:{ display:false } },
        scales:{ x:{ grid:{ display:false }, ticks:{ font:{ size:12 } } },
                 y:{ grid:{ color:'#F3F4F6' }, ticks:{ callback: v => '₪'+v } } } }
    });
  }

  // Category doughnut
  const c = document.getElementById('category-chart')?.getContext('2d');
  if (c) {
    if (_catChart) _catChart.destroy();
    _catChart = new Chart(c, {
      type: 'doughnut',
      data: {
        labels:   ['مضادات حيوية','مسكنات','فيتامينات','أمراض مزمنة','أخرى'],
        datasets: [{ data:[35,25,20,12,8],
          backgroundColor:['#1A7B4B','#4CAF82','#86EFAC','#065F46','#A7F3D0'],
          borderColor:'white', borderWidth:3, hoverOffset:5 }]
      },
      options: { responsive:true, cutout:'65%',
        plugins:{ legend:{ position:'bottom', labels:{ padding:12, font:{ size:12 }, usePointStyle:true } } } }
    });
  }
}

async function _loadRecentOrders() {
  const tbody = document.getElementById('recent-orders-body');
  const orders = mockPharmacistOrders.slice(0,5);
  // TODO: Laravel API GET /api/pharmacist/orders?limit=5
  if (!orders.length) {
    tbody.innerHTML = `<tr><td colspan="5"><div class="empty-state"><span class="empty-icon">📋</span><p>لا توجد طلبات</p></div></td></tr>`;
    return;
  }
  tbody.innerHTML = orders.map(o => `
    <tr>
      <td><strong>${esc(o.id)}</strong></td>
      <td>${esc(o.customerName)}</td>
      <td><strong style="color:var(--primary)">₪${fmt(o.total)}</strong></td>
      <td>${statusBadge(o.status)}</td>
      <td class="text-muted" style="color:var(--text-muted)">${timeAgo(o.date)}</td>
    </tr>`).join('');
}

async function _loadLowStock() {
  const list = document.getElementById('low-stock-list');
  const alerts = mockProducts.filter(p => p.stock <= p.minStock);
  // TODO: Laravel API GET /api/pharmacist/products?lowStock=true
  if (!alerts.length) {
    list.innerHTML = `<li class="empty-state"><span class="empty-icon">✅</span><p>جميع المنتجات بمستوى كافٍ</p></li>`;
    return;
  }
  list.innerHTML = alerts.slice(0,6).map(p => `
    <li class="alert-item">
      <div class="alert-dot ${p.stock===0?'out':'low'}"></div>
      <div class="alert-info">
        <div class="alert-name">${esc(p.nameAr)} <span style="color:var(--text-muted);font-size:11px">(${esc(p.nameEn)})</span></div>
        <div class="alert-stock">المخزون: ${p.stock} / الحد الأدنى: ${p.minStock}</div>
      </div>
      <a href="inventory.html" class="btn btn-sm btn-outline">طلب إعادة</a>
    </li>`).join('');
}
