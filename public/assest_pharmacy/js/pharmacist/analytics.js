// TODO: Laravel API — GET /api/pharmacist/analytics

let _revC = null, _statusC = null, _catC = null, _topC = null;

async function initAnalytics() {
  // TODO: fetch real analytics from Laravel
  const orders   = mockPharmacistOrders;
  const products = mockProducts;

  const monthRev = orders.reduce((s, o) => s + (o.total || 0), 0);
  const avgOrder = orders.length ? monthRev / orders.length : 0;
  const soldQty  = orders.reduce((s, o) => s + (o.items || []).reduce((ss, i) => ss + (i.qty || 1), 0), 0);

  document.getElementById('an-monthly').textContent     = '₪' + fmt(monthRev);
  document.getElementById('an-total-orders').textContent = orders.length;
  document.getElementById('an-sold').textContent         = soldQty;
  document.getElementById('an-avg').textContent          = '₪' + fmt(avgOrder);

  _renderRevChart();
  _renderStatusChart(orders);
  _renderCatChart(products);
  _renderTopChart(orders);
}

function _renderRevChart() {
  const ctx = document.getElementById('rev-chart')?.getContext('2d');
  if (!ctx) return;
  if (_revC) _revC.destroy();
  _revC = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: mockWeeklyRevenue.map(d => d.day),
      datasets: [{
        label: 'الإيرادات ₪',
        data: mockWeeklyRevenue.map(d => d.amount),
        backgroundColor: 'rgba(26,123,75,.75)',
        borderColor: '#1A7B4B',
        borderWidth: 1.5,
        borderRadius: 6,
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { display: false } },
        y: { grid: { color: '#F3F4F6' }, ticks: { callback: v => '₪' + v } }
      }
    }
  });
}

function _renderStatusChart(orders) {
  const ctx = document.getElementById('status-chart')?.getContext('2d');
  if (!ctx) return;
  const statusAr = { pending:'في الانتظار', confirmed:'مؤكد', processing:'قيد التحضير', ready:'جاهز', delivered:'تم التسليم', cancelled:'ملغي' };
  const colorMap  = { pending:'#F59E0B', confirmed:'#3B82F6', processing:'#8B5CF6', ready:'#06B6D4', delivered:'#10B981', cancelled:'#EF4444' };
  const counts = {};
  orders.forEach(o => {
    if (o.status) counts[o.status] = (counts[o.status] || 0) + 1;
  });
  const active = Object.entries(counts).filter(([, v]) => v > 0);
  if (_statusC) _statusC.destroy();
  _statusC = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels:   active.map(([k]) => statusAr[k] || k),
      datasets: [{ data: active.map(([, v]) => v), backgroundColor: active.map(([k]) => colorMap[k] || '#94a3b8'), borderColor: 'white', borderWidth: 3, hoverOffset: 4 }]
    },
    options: { responsive: true, cutout: '80%', plugins: { legend: { position: 'bottom', labels: { padding: 10, font: { size: 11 }, usePointStyle: true } } } }
  });
}

function _renderCatChart(products) {
  const ctx = document.getElementById('cat-chart')?.getContext('2d');
  if (!ctx) return;
  const catCount = {};
  products.forEach(p => { catCount[p.category] = (catCount[p.category] || 0) + 1; });
  const colors = ['#1A7B4B','#4CAF82','#86EFAC','#065F46','#A7F3D0'];
  if (_catC) _catC.destroy();
  _catC = new Chart(ctx, {
    type: 'pie',
    data: {
      labels: Object.keys(catCount),
      datasets: [{ data: Object.values(catCount), backgroundColor: colors, borderColor: 'white', borderWidth: 2, hoverOffset: 5 }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { padding: 10, font: { size: 11 }, usePointStyle: true } } } }
  });
}

function _renderTopChart(orders) {
  const ctx = document.getElementById('top-chart')?.getContext('2d');
  if (!ctx) return;
  const sales = {};
  orders.forEach(o => (o.items || []).forEach(i => {
    const name = i.nameAr || i.name || '؟';
    sales[name] = (sales[name] || 0) + (i.qty || 1);
  }));
  const sorted = Object.entries(sales).sort((a, b) => b[1] - a[1]).slice(0, 6);
  if (_topC) _topC.destroy();
  _topC = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: sorted.map(([n]) => n),
      datasets: [{ label: 'الكمية المباعة', data: sorted.map(([,v]) => v), backgroundColor: '#4CAF82', borderRadius: 6, borderColor: '#1A7B4B', borderWidth: 1 }]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      plugins: { legend: { display: false } },
      scales: { x: { grid: { color: '#F3F4F6' } }, y: { grid: { display: false } } }
    }
  });
}
