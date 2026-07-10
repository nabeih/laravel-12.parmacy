// ── Orders Page ───────────────────────────────────────────
let allOrders      = [];
let activeStatus   = 'All';
let viewingOrderId = null;

async function initOrders() {
  await loadOrders();
  setupOrderEvents();
}

// ── Load ──────────────────────────────────────────────────
async function loadOrders() {
  showOrdersLoading();
  try {
    if (!DEMO_MODE) {
      const snap = await db.collection('orders').orderBy('createdAt','desc').get();
      allOrders = snap.docs.map(d => ({ id: d.id, ...d.data() }));
      if (!allOrders.length) allOrders = mockOrders;
    } else {
      allOrders = [...mockOrders];
    }
  } catch (e) {
    console.warn('Orders: using mock data', e);
    allOrders = [...mockOrders];
  }
  renderOrdersTable();
  updateTabCounts();
}

// ── Render Table ──────────────────────────────────────────
function renderOrdersTable() {
  const tbody = document.getElementById('orders-tbody');
  const filtered = activeStatus === 'All'
    ? allOrders
    : allOrders.filter(o => o.status === activeStatus);

  if (!filtered.length) {
    tbody.innerHTML = `<tr><td colspan="9"><div class="empty-state"><span class="empty-icon">📋</span><p>No ${activeStatus === 'All' ? '' : activeStatus.toLowerCase() + ' '}orders found</p></div></td></tr>`;
    return;
  }

  tbody.innerHTML = filtered.map(o => `
    <tr>
      <td><strong>${o.orderNum || o.id}</strong></td>
      <td dir="auto" style="font-weight:500">${o.customerName}</td>
      <td>${o.customerPhone || '—'}</td>
      <td style="text-align:center">${(o.items || []).length}</td>
      <td><strong class="text-success">₪${fmt(o.total)}</strong></td>
      <td>${o.paymentMethod || '—'}</td>
      <td class="text-muted">${relativeTime(o.createdAt)}</td>
      <td>${statusBadge(o.status)}</td>
      <td>
        <button class="btn btn-sm btn-outline" onclick="openOrderDetail('${o.id}')">View</button>
      </td>
    </tr>`).join('');
}

function updateTabCounts() {
  const statuses = ['Pending','Processing','Delivered','Cancelled'];
  statuses.forEach(s => {
    const el = document.getElementById('tab-count-' + s.toLowerCase());
    if (el) el.textContent = allOrders.filter(o => o.status === s).length;
  });
  const allEl = document.getElementById('tab-count-all');
  if (allEl) allEl.textContent = allOrders.length;
}

// ── Filter Tabs ───────────────────────────────────────────
function setupOrderEvents() {
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      activeStatus = btn.dataset.status;
      renderOrdersTable();
    });
  });

  document.getElementById('order-modal-overlay').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeOrderModal();
  });
  document.getElementById('order-modal-close').addEventListener('click', closeOrderModal);
  document.getElementById('btn-update-status').addEventListener('click', updateOrderStatus);
}

// ── Order Detail Modal ────────────────────────────────────
function openOrderDetail(id) {
  const o = allOrders.find(x => x.id === id);
  if (!o) return;
  viewingOrderId = id;

  // Customer info
  document.getElementById('od-order-num').textContent  = o.orderNum || o.id;
  document.getElementById('od-customer').textContent   = o.customerName;
  document.getElementById('od-phone').textContent      = o.customerPhone || '—';
  document.getElementById('od-city').textContent       = o.city || '—';
  document.getElementById('od-address').textContent    = o.address || '—';
  document.getElementById('od-payment').textContent    = o.paymentMethod || '—';
  document.getElementById('od-date').textContent       = relativeTime(o.createdAt);

  // Items table
  const itemsTbody = document.getElementById('od-items-tbody');
  itemsTbody.innerHTML = (o.items || []).map(item => `
    <tr>
      <td dir="auto">${item.nameEn} <span class="text-muted" style="font-size:11px">(${item.nameAr})</span></td>
      <td style="text-align:center">${item.qty}</td>
      <td>₪${fmt(item.price)}</td>
      <td><strong>₪${fmt(item.qty * item.price)}</strong></td>
    </tr>`).join('');

  // Total row
  document.getElementById('od-total').textContent = '₪' + fmt(o.total);

  // Status dropdown
  document.getElementById('od-status-select').value = o.status;

  // Current status badge
  document.getElementById('od-current-status').innerHTML = statusBadge(o.status);

  clearUpdateMessage();
  document.getElementById('order-modal-overlay').classList.add('active');
}

function closeOrderModal() {
  document.getElementById('order-modal-overlay').classList.remove('active');
  viewingOrderId = null;
}

// ── Update Status ─────────────────────────────────────────
async function updateOrderStatus() {
  if (!viewingOrderId) return;
  const newStatus = document.getElementById('od-status-select').value;
  const btn = document.getElementById('btn-update-status');

  btn.disabled    = true;
  btn.textContent = 'Updating…';

  try {
    if (!DEMO_MODE) {
      await db.collection('orders').doc(viewingOrderId).update({ status: newStatus });
    } else {
      await new Promise(r => setTimeout(r, 500));
    }

    // Update in-memory
    const order = allOrders.find(x => x.id === viewingOrderId);
    if (order) order.status = newStatus;

    document.getElementById('od-current-status').innerHTML = statusBadge(newStatus);
    showUpdateMessage('Status updated successfully!', 'success');
    renderOrdersTable();
    updateTabCounts();
  } catch (err) {
    showUpdateMessage('Error: ' + err.message, 'error');
  } finally {
    btn.disabled    = false;
    btn.textContent = 'Update Status';
  }
}

function showUpdateMessage(msg, type) {
  const el = document.getElementById('update-msg');
  if (!el) return;
  el.textContent = msg;
  el.className   = type === 'success' ? 'success-msg' : 'error-msg';
}
function clearUpdateMessage() {
  const el = document.getElementById('update-msg');
  if (el) { el.textContent = ''; el.className = ''; }
}

// ── Loading State ─────────────────────────────────────────
function showOrdersLoading() {
  document.getElementById('orders-tbody').innerHTML =
    '<tr><td colspan="9"><div class="loading-state"><div class="spinner"></div><p>Loading orders…</p></div></td></tr>';
}
