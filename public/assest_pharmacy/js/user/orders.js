// My Orders — GET /api/orders, POST /api/orders (checkout.js)

let _userOrders = [], _statusFilt = '';

async function initUserOrders() {
  await _loadOrders();
  _setupTabs();
  _setupModal();
}

async function _loadOrders() {
  const { ok, data } = await phFetch('/api/orders');
  _userOrders = (ok && data?.orders) ? data.orders.map(_normalizeOrder) : [];
  _renderOrders();
}

function _normalizeOrder(o) {
  return {
    id: o.id,
    status: o.status,
    pharmacyName: o.pharmacy?.name_ar || '—',
    total: o.total,
    date: o.created_at,
    items: (o.items || []).map(i => ({ nameAr: i.name_ar, qty: i.qty, price: Number(i.price) })),
  };
}

function _renderOrders() {
  const filtered  = _statusFilt ? _userOrders.filter(o => o.status === _statusFilt) : _userOrders;
  const container = document.getElementById('orders-container');

  if (!filtered.length) {
    container.innerHTML = `<div class="empty-state" style="grid-column:1/-1"><span class="empty-icon">🛒</span><p>لا توجد طلبات مطابقة</p></div>`;
    return;
  }

  container.innerHTML = filtered.map(o => `
    <div class="order-card" onclick="openOrderDetail(${o.id})">
      <div class="oc-header">
        <span class="oc-id">#${o.id}</span>
        ${statusBadge(o.status)}
      </div>
      <div class="oc-pharmacy">🏪 ${esc(o.pharmacyName)}</div>
      <div class="oc-items" style="font-size:12px;color:var(--text-muted);margin:6px 0">
        ${o.items.map(i => esc(i.nameAr)).join('، ') || '—'}
      </div>
      <div class="oc-footer">
        <strong style="color:var(--primary)">₪${fmt(o.total)}</strong>
        <span style="font-size:12px;color:var(--text-muted)">${timeAgo(o.date)}</span>
      </div>
      <div class="order-stepper">${_buildStepper(o.status)}</div>
    </div>`).join('');
}

function _buildStepper(status) {
  const steps  = ['pending', 'confirmed', 'processing', 'ready', 'delivered'];
  const idx    = steps.indexOf(status);
  const labels = { pending: 'في الانتظار', confirmed: 'مؤكد', processing: 'قيد التحضير', ready: 'جاهز', delivered: 'مُسلَّم' };

  return steps.map((s, i) => {
    const done = i <= idx;
    return `<div class="step ${done ? 'done' : ''} ${status === 'cancelled' && i === 0 ? 'cancelled' : ''}">
      <div class="step-dot">${done ? '✓' : (i + 1)}</div>
      <div class="step-label">${labels[s]}</div>
    </div>`;
  }).join('<div class="step-line"></div>');
}

function _setupTabs() {
  document.getElementById('order-tabs')?.querySelectorAll('.filter-tab').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      _statusFilt = btn.dataset.status;
      _renderOrders();
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
  const o = _userOrders.find(x => x.id === id);
  if (!o) return;

  const items = o.items.map(i => `
    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)">
      <span>${esc(i.nameAr)} × ${i.qty}</span>
      <strong>₪${fmt(i.price * i.qty)}</strong>
    </div>`).join('') || '<p style="color:var(--text-muted)">لا تفاصيل متاحة</p>';

  document.getElementById('order-detail-body').innerHTML = `
    <div class="detail-grid">
      <div><span class="detail-label">رقم الطلب</span><span>#${o.id}</span></div>
      <div><span class="detail-label">الحالة</span><span>${statusBadge(o.status)}</span></div>
      <div><span class="detail-label">الصيدلية</span><span>${esc(o.pharmacyName)}</span></div>
      <div><span class="detail-label">التاريخ</span><span>${fmtDate(o.date)}</span></div>
    </div>
    <div style="margin-top:16px"><div class="detail-label" style="margin-bottom:8px">المنتجات</div>${items}</div>
    <div style="display:flex;justify-content:space-between;margin-top:12px;font-weight:700">
      <span>الإجمالي</span><span style="color:var(--primary)">₪${fmt(o.total)}</span>
    </div>
    <div style="margin-top:16px"><div class="order-stepper detail-stepper">${_buildStepper(o.status)}</div></div>`;
  document.getElementById('order-modal').classList.add('active');
}
