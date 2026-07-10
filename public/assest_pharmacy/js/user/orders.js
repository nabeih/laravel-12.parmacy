// TODO: Laravel API — GET /api/user/orders

let _userOrders = [], _statusFilt = '', _userStatusChart = null;

async function initUserOrders() {
  await _loadOrders();
  _setupTabs();
  _setupModal();
}

async function _loadOrders() {
  // TODO: Laravel API GET /api/user/orders
  if (!DEMO_MODE) {
    try {
      const uid  = window.currentUser?.uid;
      const snap = await db.collection('orders').where('userId','==',uid).orderBy('createdAt','desc').get();
      _userOrders = snap.docs.map(d => ({ id: d.id, ...d.data() }));
    } catch(e) { _userOrders = [...mockUserOrders]; }
  } else {
    _userOrders = [...mockUserOrders];
  }
  _renderOrders();
  _renderUserStatusChart();
}

function _renderUserStatusChart() {
  const ctx = document.getElementById('user-status-chart');
  if (!ctx) return;
  const statusAr = { pending:'في الانتظار', confirmed:'مؤكد', processing:'قيد التحضير', ready:'جاهز', delivered:'مُسلَّم', cancelled:'ملغي' };
  const colorMap  = { pending:'#F59E0B', confirmed:'#3B82F6', processing:'#8B5CF6', ready:'#06B6D4', delivered:'#10B981', cancelled:'#EF4444' };
  const counts = {};
  _userOrders.forEach(o => { if (o.status) counts[o.status] = (counts[o.status] || 0) + 1; });
  const active = Object.entries(counts).filter(([, v]) => v > 0);

  document.getElementById('user-status-legend').innerHTML = active
    .map(([k, v]) => `<div><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:${colorMap[k] || '#94a3b8'};margin-left:6px"></span>${statusAr[k] || k}: <strong>${v}</strong></div>`)
    .join('');

  if (_userStatusChart) _userStatusChart.destroy();
  _userStatusChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels:   active.map(([k]) => statusAr[k] || k),
      datasets: [{ data: active.map(([, v]) => v), backgroundColor: active.map(([k]) => colorMap[k] || '#94a3b8'), borderColor: 'white', borderWidth: 2, hoverOffset: 3 }]
    },
    options: { responsive: true, maintainAspectRatio: true, cutout: '75%', plugins: { legend: { display: false } } }
  });
}

function _renderOrders() {
  const filtered = _statusFilt ? _userOrders.filter(o => o.status === _statusFilt) : _userOrders;
  const container = document.getElementById('orders-container');
  if (!filtered.length) {
    container.innerHTML = `<div class="empty-state" style="grid-column:1/-1"><span class="empty-icon">🛒</span><p>لا توجد طلبات مطابقة</p></div>`;
    return;
  }
  container.innerHTML = filtered.map(o => `
    <div class="order-card" onclick="openOrderDetail('${o.id}')">
      <div class="oc-header">
        <span class="oc-id">#${esc(o.id)}</span>
        ${statusBadge(o.status)}
      </div>
      <div class="oc-pharmacy">
        <span class="oc-ph-icon">🏪</span> ${esc(o.pharmacyName || '—')}
      </div>
      <div class="oc-items" style="font-size:12px;color:var(--text-muted);margin:6px 0">
        ${(o.items || []).map(i => esc(i.nameAr || i.name)).join('، ') || '—'}
      </div>
      <div class="oc-footer">
        <strong style="color:var(--primary)">₪${fmt(o.total)}</strong>
        <span style="font-size:12px;color:var(--text-muted)">${timeAgo(o.date || o.createdAt)}</span>
      </div>
      <div class="order-stepper">${_buildStepper(o.status)}</div>
    </div>`).join('');
}

function _buildStepper(status) {
  const steps = ['pending','confirmed','processing','ready','delivered'];
  const idx   = steps.indexOf(status);
  return steps.map((s, i) => {
    const done   = i <= idx;
    const labels = { pending:'في الانتظار', confirmed:'مؤكد', processing:'قيد التحضير', ready:'جاهز', delivered:'مُسلَّم' };
    return `<div class="step ${done ? 'done' : ''} ${status==='cancelled'&&i===0?'cancelled':''}">
      <div class="step-dot">${done ? '✓' : (i+1)}</div>
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
  const items = (o.items || []).map(i => `
    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)">
      <span>${esc(i.nameAr || i.name)} × ${i.qty || 1}</span>
      <strong>₪${fmt((i.price||0)*(i.qty||1))}</strong>
    </div>`).join('') || '<p style="color:var(--text-muted)">لا تفاصيل متاحة</p>';

  document.getElementById('order-detail-body').innerHTML = `
    <div class="detail-grid">
      <div><span class="detail-label">رقم الطلب</span><span>#${esc(o.id)}</span></div>
      <div><span class="detail-label">الحالة</span><span>${statusBadge(o.status)}</span></div>
      <div><span class="detail-label">الصيدلية</span><span>${esc(o.pharmacyName||'—')}</span></div>
      <div><span class="detail-label">التاريخ</span><span>${fmtDate(o.date||'')}</span></div>
    </div>
    <div style="margin-top:16px"><div class="detail-label" style="margin-bottom:8px">المنتجات</div>${items}</div>
    <div style="display:flex;justify-content:space-between;margin-top:12px;font-weight:700">
      <span>الإجمالي</span><span style="color:var(--primary)">₪${fmt(o.total)}</span>
    </div>
    <div style="margin-top:16px"><div class="order-stepper detail-stepper">${_buildStepper(o.status)}</div></div>`;
  document.getElementById('order-modal').classList.add('active');
}
