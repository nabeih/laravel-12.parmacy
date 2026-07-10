// ── QR Sales Page ─────────────────────────────────────────
let currentSale  = [];   // { productId, nameEn, nameAr, qty, unitPrice }
let todaysSales  = [];   // completed sales for today's table
let saleProducts = [];   // available products for scanning

async function initQrSales() {
  saleProducts = [...mockProducts];
  await loadTodaysSales();
  setupQrEvents();
  renderSaleItems();
}

// ── Load Products (for scan pool) ────────────────────────
// already using mockProducts as saleProducts; extend below for Firestore
async function loadProductsForScan() {
  try {
    if (!DEMO_MODE) {
      const snap = await db.collection('products').get();
      if (!snap.empty) saleProducts = snap.docs.map(d => ({ id: d.id, ...d.data() }));
    }
  } catch (e) { /* use mockProducts */ }
}

// ── Load Today's Sales ────────────────────────────────────
async function loadTodaysSales() {
  try {
    if (!DEMO_MODE) {
      const today = new Date();
      today.setHours(0,0,0,0);
      const snap = await db.collection('sales')
        .where('type', '==', 'qr')
        .orderBy('createdAt', 'desc')
        .get();
      todaysSales = snap.docs
        .map(d => ({ id: d.id, ...d.data() }))
        .filter(s => {
          const d = s.createdAt?.toDate ? s.createdAt.toDate() : new Date((s.createdAt?.seconds||0)*1000);
          return d >= today;
        });
    }
  } catch (e) { todaysSales = []; }
  renderTodaysSales();
}

// ── Events ────────────────────────────────────────────────
function setupQrEvents() {
  document.getElementById('btn-simulate-scan').addEventListener('click',  simulateScan);
  document.getElementById('btn-confirm-sale').addEventListener('click',   confirmSale);
  document.getElementById('btn-clear-sale').addEventListener('click',     clearSale);
}

// ── Simulate Scan ─────────────────────────────────────────
function simulateScan() {
  // Pick a random in-stock product
  const available = saleProducts.filter(p => p.stock > 0 || DEMO_MODE);
  if (!available.length) {
    showQrMessage('No products available to scan.', 'error');
    return;
  }

  const p = available[Math.floor(Math.random() * available.length)];

  // Flash the camera box
  const box = document.getElementById('qr-camera-box');
  box.classList.add('scanning');
  setTimeout(() => box.classList.remove('scanning'), 600);

  // Add to sale or increment qty
  const existing = currentSale.find(x => x.productId === p.id);
  if (existing) {
    existing.qty = Math.min(existing.qty + 1, p.stock || 999);
  } else {
    currentSale.push({
      productId: p.id,
      nameEn:    p.nameEn,
      nameAr:    p.nameAr,
      qty:       1,
      unitPrice: p.price,
      maxQty:    p.stock || 999
    });
  }

  renderSaleItems();
  showQrMessage(`Scanned: ${p.nameEn}`, 'success');
}

// ── Render Sale Items ─────────────────────────────────────
function renderSaleItems() {
  const list       = document.getElementById('sale-items-list');
  const confirmBtn = document.getElementById('btn-confirm-sale');
  const clearBtn   = document.getElementById('btn-clear-sale');
  const badge      = document.getElementById('sale-count-badge');

  if (badge) badge.textContent = currentSale.length + (currentSale.length === 1 ? ' item' : ' items');

  if (!currentSale.length) {
    list.innerHTML = `
      <li style="list-style:none">
        <div class="no-sale">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
          <p>No items yet — simulate a scan to add products</p>
        </div>
      </li>`;
    confirmBtn.disabled = true;
    clearBtn.disabled   = true;
    updateOrderSummary(0);
    return;
  }

  confirmBtn.disabled = false;
  clearBtn.disabled   = false;

  list.innerHTML = currentSale.map((item, i) => `
    <li class="sale-item-row">
      <div class="sale-item-name" dir="auto" title="${item.nameAr}">${item.nameEn}</div>
      <input
        class="form-control sale-item-qty"
        type="number" min="1" max="${item.maxQty}"
        value="${item.qty}"
        onchange="updateQty(${i}, this.value)"
        oninput="updateQty(${i}, this.value)"
      />
      <span class="sale-item-price">₪${fmt(item.unitPrice)}</span>
      <span class="sale-item-total" id="line-total-${i}">₪${fmt(item.qty * item.unitPrice)}</span>
      <button class="sale-item-remove" onclick="removeItem(${i})" title="Remove">✕</button>
    </li>`).join('');

  const grandTotal = currentSale.reduce((s, x) => s + x.qty * x.unitPrice, 0);
  updateOrderSummary(grandTotal);
}

function updateQty(index, value) {
  const qty = Math.max(1, parseInt(value, 10) || 1);
  currentSale[index].qty = qty;

  const totalEl = document.getElementById('line-total-' + index);
  if (totalEl) totalEl.textContent = '₪' + fmt(qty * currentSale[index].unitPrice);

  const grandTotal = currentSale.reduce((s, x) => s + x.qty * x.unitPrice, 0);
  updateOrderSummary(grandTotal);
}

function removeItem(index) {
  currentSale.splice(index, 1);
  renderSaleItems();
}

function updateOrderSummary(total) {
  document.getElementById('summary-subtotal').textContent = '₪' + fmt(total);
  document.getElementById('summary-total').textContent    = '₪' + fmt(total);
}

// ── Confirm Sale ──────────────────────────────────────────
async function confirmSale() {
  if (!currentSale.length) return;
  const total = currentSale.reduce((s, x) => s + x.qty * x.unitPrice, 0);
  const btn   = document.getElementById('btn-confirm-sale');

  btn.disabled    = true;
  btn.textContent = 'Processing…';

  const saleDoc = {
    items:     currentSale.map(x => ({ productId: x.productId, nameEn: x.nameEn, nameAr: x.nameAr, qty: x.qty, price: x.unitPrice })),
    total,
    type:      'qr',
    createdAt: new Date()
  };

  try {
    if (!DEMO_MODE) {
      // Save sale
      const saleRef = await db.collection('sales').add(saleDoc);
      saleDoc.id = saleRef.id;

      // Deduct stock in a batch
      const batch = db.batch();
      currentSale.forEach(item => {
        const p = saleProducts.find(x => x.id === item.productId);
        if (p && p.id && !p.id.startsWith('mp')) {
          const ref = db.collection('products').doc(p.id);
          batch.update(ref, { stock: firebase.firestore.FieldValue.increment(-item.qty) });
        }
      });
      await batch.commit();
    } else {
      await new Promise(r => setTimeout(r, 600));
      saleDoc.id = 'sale-' + Date.now();
      // Deduct mock stock
      currentSale.forEach(item => {
        const p = saleProducts.find(x => x.id === item.productId);
        if (p) p.stock = Math.max(0, p.stock - item.qty);
      });
    }

    // Add to today's sales display
    todaysSales.unshift(saleDoc);
    renderTodaysSales();

    currentSale = [];
    renderSaleItems();
    showQrMessage(`Sale of ₪${fmt(total)} confirmed!`, 'success');
  } catch (err) {
    showQrMessage('Error saving sale: ' + err.message, 'error');
  } finally {
    btn.disabled    = false;
    btn.textContent = 'Confirm Sale';
  }
}

// ── Clear Sale ────────────────────────────────────────────
function clearSale() {
  if (!currentSale.length) return;
  if (!confirm('Clear all items from the current sale?')) return;
  currentSale = [];
  renderSaleItems();
  clearQrMessage();
}

// ── Today's Sales Table ───────────────────────────────────
function renderTodaysSales() {
  const tbody = document.getElementById('today-sales-tbody');

  if (!todaysSales.length) {
    tbody.innerHTML = '<tr><td colspan="4"><div class="empty-state"><span class="empty-icon">🛒</span><p>No sales yet today</p></div></td></tr>';
    return;
  }

  let totalRevenue = 0;
  tbody.innerHTML = todaysSales.map((s, i) => {
    totalRevenue += s.total || 0;
    const t = s.createdAt instanceof Date ? s.createdAt : new Date((s.createdAt?.seconds||0)*1000);
    return `<tr>
      <td>${todaysSales.length - i}</td>
      <td>${(s.items||[]).map(x => x.nameEn).join(', ')}</td>
      <td style="text-align:center">${(s.items||[]).reduce((a,x) => a + x.qty, 0)}</td>
      <td><strong class="text-success">₪${fmt(s.total)}</strong></td>
      <td class="text-muted">${t.toLocaleTimeString('en-GB', { hour:'2-digit', minute:'2-digit' })}</td>
    </tr>`;
  }).join('');

  document.getElementById('today-total-revenue').textContent = '₪' + fmt(totalRevenue);
  document.getElementById('today-total-count').textContent   = todaysSales.length;
}

// ── Notifications ─────────────────────────────────────────
function showQrMessage(msg, type) {
  const el = document.getElementById('qr-msg');
  if (!el) return;
  el.textContent = msg;
  el.className   = type === 'success' ? 'success-msg' : 'error-msg';
  clearTimeout(el._timer);
  el._timer = setTimeout(() => { el.textContent = ''; el.className = ''; }, 4000);
}
function clearQrMessage() {
  const el = document.getElementById('qr-msg');
  if (el) { el.textContent = ''; el.className = ''; }
}
