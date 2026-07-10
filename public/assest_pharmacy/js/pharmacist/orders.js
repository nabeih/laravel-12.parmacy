// TODO: Laravel API — GET /api/pharmacist/orders  PUT /api/pharmacist/orders/{id}/status

const ORD_PAGE = 10;
let _orders = [], _ordFiltered = [], _ordPage = 1, _statusFilter = '', _statusChart = null;

async function initOrders() {
  await _loadOrders();
  _setupSearch();
  _setupTabs();
  _setupModal();
}

async function _loadOrders() {
  // TODO: Laravel API GET /api/pharmacist/orders
  if (!DEMO_MODE) {
    try {
      const uid  = window.currentUser?.uid;
      const snap = await db.collection('orders').where('pharmacyId','==',uid).orderBy('createdAt','desc').get();
      _orders    = snap.docs.map(d => ({ id: d.id, ...d.data() }));
    } catch(e) { _orders = [...mockPharmacistOrders]; }
  } else {
    _orders = [...mockPharmacistOrders];
  }
  _applyFilter();
  _renderStatusChart();
}

function _renderStatusChart() {
  const ctx = document.getElementById('orders-status-chart');
  if (!ctx) return;
  const statusAr = { pending:'في الانتظار', confirmed:'مؤكد', processing:'قيد التحضير', ready:'جاهز', delivered:'تم التسليم', cancelled:'ملغي' };
  const colorMap  = { pending:'#F59E0B', confirmed:'#3B82F6', processing:'#8B5CF6', ready:'#06B6D4', delivered:'#10B981', cancelled:'#EF4444' };
  const counts = {};
  _orders.forEach(o => { if (o.status) counts[o.status] = (counts[o.status] || 0) + 1; });
  const active = Object.entries(counts).filter(([, v]) => v > 0);

  document.getElementById('orders-status-legend').innerHTML = active
    .map(([k, v]) => `<div><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:${colorMap[k] || '#94a3b8'};margin-left:6px"></span>${statusAr[k] || k}: <strong>${v}</strong></div>`)
    .join('');

  if (_statusChart) _statusChart.destroy();
  _statusChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels:   active.map(([k]) => statusAr[k] || k),
      datasets: [{ data: active.map(([, v]) => v), backgroundColor: active.map(([k]) => colorMap[k] || '#94a3b8'), borderColor: 'white', borderWidth: 2, hoverOffset: 3 }]
    },
    options: { responsive: true, maintainAspectRatio: true, cutout: '75%', plugins: { legend: { display: false } } }
  });
}

function _applyFilter() {
  const q = document.getElementById('ord-search')?.value.toLowerCase() || '';
  _ordFiltered = _orders.filter(o => {
    const matchStatus = !_statusFilter || o.status === _statusFilter;
    const matchQ = !q || o.id?.toLowerCase().includes(q) || o.customerName?.toLowerCase().includes(q);
    return matchStatus && matchQ;
  });
  _ordPage = 1;
  _renderOrders();
}

function _renderOrders() {
  const start  = (_ordPage - 1) * ORD_PAGE;
  const page   = _ordFiltered.slice(start, start + ORD_PAGE);
  const total  = _ordFiltered.length;
  const pages  = Math.max(1, Math.ceil(total / ORD_PAGE));
  const tbody  = document.getElementById('ord-tbody');

  document.getElementById('page-info').textContent =
    total === 0 ? 'لا توجد طلبات' : `${start+1}–${Math.min(start+ORD_PAGE,total)} من ${total}`;
  document.getElementById('btn-prev').disabled = _ordPage <= 1;
  document.getElementById('btn-next').disabled = _ordPage >= pages;

  if (!page.length) {
    tbody.innerHTML = `<tr><td colspan="7"><div class="empty-state"><span class="empty-icon">🛒</span><p>لا توجد طلبات مطابقة</p></div></td></tr>`;
    return;
  }

  tbody.innerHTML = page.map(o => {
    const items = (o.items || []).map(i => esc(i.nameAr || i.name)).join('، ') || '—';
    return `
    <tr>
      <td><strong>${esc(o.id)}</strong></td>
      <td>${esc(o.customerName)}</td>
      <td style="font-size:12px;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${items}</td>
      <td><strong style="color:var(--primary)">₪${fmt(o.total)}</strong></td>
      <td>${statusBadge(o.status)}</td>
      <td class="text-muted">${timeAgo(o.date || o.createdAt)}</td>
      <td><button class="btn btn-sm btn-outline" onclick="openOrderDetail('${o.id}')">عرض</button></td>
    </tr>`;
  }).join('');
}

function _setupSearch() {
  document.getElementById('ord-search')?.addEventListener('input', debounce(_applyFilter, 300));
  document.getElementById('btn-prev')?.addEventListener('click', () => { _ordPage--; _renderOrders(); });
  document.getElementById('btn-next')?.addEventListener('click', () => { _ordPage++; _renderOrders(); });
}

function _setupTabs() {
  document.getElementById('status-tabs')?.querySelectorAll('.filter-tab').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      _statusFilter = btn.dataset.status;
      _applyFilter();
    });
  });
}

function _setupModal() {
  const modal = document.getElementById('order-modal');
  const close = () => modal.classList.remove('active');
  document.getElementById('modal-close')?.addEventListener('click', close);
  document.getElementById('modal-cancel')?.addEventListener('click', close);
  modal?.addEventListener('click', e => { if (e.target === modal) close(); });
}

function openOrderDetail(id) {
  const o = _orders.find(x => x.id === id);
  if (!o) return;
  document.getElementById('modal-order-id').textContent = `#${o.id}`;
  const items = (o.items || []).map(i => `
    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)">
      <span>${esc(i.nameAr || i.name)} × ${i.qty || 1}</span>
      <strong>₪${fmt((i.price||0) * (i.qty||1))}</strong>
    </div>`).join('') || '<p style="color:var(--text-muted)">لا تفاصيل متاحة</p>';

  document.getElementById('order-detail-body').innerHTML = `
    <div class="detail-grid">
      <div><span class="detail-label">العميل</span><span>${esc(o.customerName)}</span></div>
      <div><span class="detail-label">الحالة</span><span>${statusBadge(o.status)}</span></div>
      <div><span class="detail-label">التاريخ</span><span>${fmtDate(o.date || '')}</span></div>
      <div><span class="detail-label">العنوان</span><span>${esc(o.address || '—')}</span></div>
    </div>
    <div style="margin-top:16px">
      <div class="detail-label" style="margin-bottom:8px">المنتجات</div>
      ${items}
    </div>
    <div style="display:flex;justify-content:space-between;margin-top:12px;font-size:15px;font-weight:700">
      <span>الإجمالي</span><span style="color:var(--primary)">₪${fmt(o.total)}</span>
    </div>`;

  const actions = document.getElementById('status-actions');
  actions.innerHTML = _nextStatusBtns(o).map(({ label, status }) =>
    `<button class="btn btn-primary btn-sm" onclick="updateOrderStatus('${o.id}','${status}')">${label}</button>`
  ).join('');

  document.getElementById('order-modal').classList.add('active');
}

function _nextStatusBtns(o) {
  const map = {
    pending:    [{ label:'تأكيد الطلب', status:'confirmed' }],
    confirmed:  [{ label:'بدء التحضير', status:'processing' }],
    processing: [{ label:'جاهز للاستلام', status:'ready' }],
    ready:      [{ label:'تم التسليم', status:'delivered' }],
    delivered:  [],
    cancelled:  [],
  };
  const btns = map[o.status] || [];
  if (!['delivered','cancelled'].includes(o.status)) {
    btns.push({ label:'إلغاء', status:'cancelled' });
  }
  return btns;
}

async function updateOrderStatus(orderId, newStatus) {
  // TODO: Laravel API PUT /api/pharmacist/orders/{id}/status
  if (DEMO_MODE) {
    const o = _orders.find(x => x.id === orderId);
    if (o) o.status = newStatus;
    document.getElementById('order-modal').classList.remove('active');
    _applyFilter();
    return;
  }
  try {
    await db.collection('orders').doc(orderId).update({ status: newStatus });
    document.getElementById('order-modal').classList.remove('active');
    _loadOrders();
  } catch(e) { alert('فشل تحديث الحالة: ' + e.message); }
}
