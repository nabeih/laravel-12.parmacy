// ── Checkout Page ──────────────────────────────────────────

let _step              = 1;
let _selectedAddressId = null;
let _paymentMethod     = null;
let _receiptBase64     = null;

function initCheckout() {
  const cart = getCart();
  if (!cart || !cart.items.length) {
    document.getElementById('checkout-main').innerHTML = `
      <div class="empty-state">
        <span class="empty-icon">🛒</span>
        <h3>السلة فارغة</h3>
        <p>أضف أدوية للسلة أولاً</p>
        <a href="pharmacies.html" class="btn btn-primary" style="margin-top:16px">تصفح الصيدليات</a>
      </div>`;
    document.querySelector('.checkout-steps')?.remove();
    return;
  }

  _renderOrderItems();
  _renderAddresses();
  _updateSummary();
  _updateStepper();
  _bindReceiptUpload();
}

// ── Step 1 ─────────────────────────────────────────────────
function _renderOrderItems() {
  const cart     = getCart();
  const pharmacy = (mockPharmaciesList || []).find(p => p.id === cart.pharmacyId);

  document.getElementById('pharmacy-info-name').textContent = cart.pharmacyName;
  const phoneEl = document.getElementById('pharmacy-info-phone');
  if (pharmacy?.phone) {
    phoneEl.href        = `tel:${pharmacy.phone}`;
    phoneEl.textContent = `📞 ${pharmacy.phone}`;
  }

  document.getElementById('order-items').innerHTML = cart.items.map(item => `
    <tr>
      <td>
        <div style="font-weight:600">${esc(item.nameAr)}</div>
        <div style="font-size:12px;color:var(--text-muted)">${esc(item.nameEn)}</div>
      </td>
      <td>
        <div style="display:flex;align-items:center;gap:5px">
          <button onclick="_changeQty('${item.medicineId}',${item.qty - 1})"
                  style="width:24px;height:24px;border:1px solid var(--border);border-radius:50%;background:white;cursor:pointer;font-size:14px;display:inline-flex;align-items:center;justify-content:center">−</button>
          <span style="min-width:22px;text-align:center;font-weight:600">${item.qty}</span>
          <button onclick="_changeQty('${item.medicineId}',${item.qty + 1})"
                  style="width:24px;height:24px;border:1px solid var(--border);border-radius:50%;background:white;cursor:pointer;font-size:14px;display:inline-flex;align-items:center;justify-content:center">+</button>
        </div>
      </td>
      <td>₪${item.price.toFixed(2)}</td>
      <td style="color:var(--primary);font-weight:700">₪${(item.price * item.qty).toFixed(2)}</td>
      <td>
        <button onclick="_removeCheckoutItem('${item.medicineId}')"
                style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:18px;padding:0">✕</button>
      </td>
    </tr>`).join('');
}

function _changeQty(medicineId, qty) {
  updateQty(medicineId, qty);
  const cart = getCart();
  if (!cart || !cart.items.length) {
    window.location.href = 'pharmacies.html';
    return;
  }
  _renderOrderItems();
  _updateSummary();
}

function _removeCheckoutItem(medicineId) {
  removeFromCart(medicineId);
  const cart = getCart();
  if (!cart || !cart.items.length) {
    window.location.href = 'pharmacies.html';
    return;
  }
  _renderOrderItems();
  _updateSummary();
}

// ── Step 2 ─────────────────────────────────────────────────
function _renderAddresses() {
  const list = document.getElementById('addresses-list');
  if (!list) return;

  list.innerHTML = (mockAddresses || []).map(addr => `
    <label style="display:block;margin-bottom:10px;cursor:pointer">
      <div class="addr-card ${addr.isDefault ? 'default-addr' : ''}"
           style="display:flex;align-items:flex-start;gap:12px">
        <input type="radio" name="co-address" value="${addr.id}"
               ${addr.isDefault ? 'checked' : ''}
               onchange="_selectAddress('${addr.id}')"
               style="margin-top:3px;flex-shrink:0;accent-color:var(--primary)">
        <div>
          <div style="font-weight:600;margin-bottom:4px">
            ${esc(addr.label)}
            ${addr.isDefault ? '<span class="badge badge-green" style="font-size:10px">افتراضي</span>' : ''}
          </div>
          <div class="addr-detail">${esc(addr.governorate)} — ${esc(addr.area)}</div>
          <div class="addr-detail">${esc(addr.street)}</div>
          ${addr.notes ? `<div class="addr-detail" style="font-style:italic;color:var(--text-muted)">${esc(addr.notes)}</div>` : ''}
        </div>
      </div>
    </label>`).join('');

  const def = (mockAddresses || []).find(a => a.isDefault);
  if (def) _selectedAddressId = def.id;
}

function _selectAddress(id) {
  _selectedAddressId = id;
}

// ── Step 3 ─────────────────────────────────────────────────
function _setPayment(method) {
  _paymentMethod = method;
  const primary = 'var(--primary)';
  const border  = 'var(--border)';
  document.getElementById('cash-card').style.borderColor = method === 'cash' ? primary : border;
  document.getElementById('ussd-card').style.borderColor = method === 'ussd' ? primary : border;
  document.getElementById('bank-card').style.borderColor = method === 'bank' ? primary : border;

  document.getElementById('ussd-details').style.display = method === 'ussd' ? 'block' : 'none';
  document.getElementById('bank-details').style.display = method === 'bank' ? 'block' : 'none';

  if (method === 'bank') {
    const cart  = getCart();
    const total = getCartTotal() + 5;
    document.getElementById('bank-pharmacy-name').textContent = cart ? cart.pharmacyName : '—';
    document.getElementById('bank-amount').textContent        = '₪' + total.toFixed(2);
  }

  if (method === 'ussd') {
    const total = getCartTotal() + 5;
    document.getElementById('ussd-code').textContent = `*724*${total.toFixed(2)}#`;
  }
}

function _copyUssd() {
  const code = document.getElementById('ussd-code')?.textContent || '';
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(code)
      .then(() => showToast('تم نسخ الرمز ✓'))
      .catch(() => _copyFallback(code));
  } else {
    _copyFallback(code);
  }
}

function _copyFallback(code) {
  try {
    const ta = document.createElement('textarea');
    ta.value = code; ta.style.cssText = 'position:fixed;opacity:0';
    document.body.appendChild(ta); ta.select();
    document.execCommand('copy');
    document.body.removeChild(ta);
    showToast('تم نسخ الرمز ✓');
  } catch {
    showToast('انسخ الرمز يدوياً: ' + code, 'error');
  }
}

function _bindReceiptUpload() {
  const input = document.getElementById('receipt-input');
  if (!input) return;
  input.addEventListener('change', e => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = ev => {
      _receiptBase64 = ev.target.result;
      document.getElementById('receipt-preview').src         = ev.target.result;
      document.getElementById('receipt-preview-wrap').style.display = 'block';
      document.getElementById('receipt-zone').style.display         = 'none';
    };
    reader.readAsDataURL(file);
  });
}

function _removeReceipt() {
  _receiptBase64 = null;
  document.getElementById('receipt-preview-wrap').style.display = 'none';
  document.getElementById('receipt-zone').style.display         = 'block';
  document.getElementById('receipt-input').value                = '';
}

// ── Summary ────────────────────────────────────────────────
function _updateSummary() {
  const cart     = getCart();
  const subtotal = cart ? getCartTotal() : 0;
  const total    = subtotal + (cart && cart.items.length ? 5 : 0);

  const summaryItems = document.getElementById('summary-items');
  if (summaryItems && cart) {
    summaryItems.innerHTML = cart.items.map(i => `
      <div style="display:flex;justify-content:space-between;font-size:13px;padding:3px 0">
        <span style="color:var(--text-muted)">${esc(i.nameAr)} × ${i.qty}</span>
        <span>₪${(i.price * i.qty).toFixed(2)}</span>
      </div>`).join('');
  }

  const subtotalEl = document.getElementById('summary-subtotal');
  const totalEl    = document.getElementById('summary-total');
  if (subtotalEl) subtotalEl.textContent = '₪' + subtotal.toFixed(2);
  if (totalEl)    totalEl.textContent    = '₪' + total.toFixed(2);
}

// ── Stepper nav ────────────────────────────────────────────
function _updateStepper() {
  document.querySelectorAll('.checkout-step').forEach(el => {
    const n = parseInt(el.dataset.step);
    el.classList.toggle('active', n === _step);
    el.classList.toggle('done',   n < _step);
  });
  document.querySelectorAll('.checkout-panel').forEach(el => {
    el.style.display = parseInt(el.dataset.step) === _step ? 'block' : 'none';
  });
}

function _goStep(n) {
  _step = n;
  _updateStepper();
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function _nextStep() {
  if (_step === 1) {
    const cart = getCart();
    if (!cart || !cart.items.length) { showToast('السلة فارغة', 'error'); return; }
    _goStep(2);
  } else if (_step === 2) {
    if (!_selectedAddressId) { showToast('اختر عنوان التوصيل', 'error'); return; }
    _goStep(3);
  }
}

// ── Place order ────────────────────────────────────────────
async function _confirmOrder() {
  if (!_paymentMethod) {
    showToast('اختر طريقة الدفع', 'error'); return;
  }
  if (_paymentMethod === 'bank' && !_receiptBase64) {
    showToast('يرجى رفع صورة إيصال التحويل', 'error'); return;
  }
  if (_paymentMethod === 'ussd') {
    const ref = (document.getElementById('ussd-ref')?.value || '').trim();
    if (!ref) { showToast('أدخل رقم مرجع العملية', 'error'); return; }
  }

  const btn = document.getElementById('confirm-btn');
  btn.disabled    = true;
  btn.textContent = 'جاري إرسال طلبك...';

  // TODO: Laravel API POST /api/orders
  await new Promise(r => setTimeout(r, 1500));

  const orderId = 'ORD-' + (Math.floor(Math.random() * 900) + 100);
  clearCart();

  document.getElementById('order-number').textContent = orderId;
  const notes = {
    cash: 'سيتصل بك الصيدلاني لتأكيد موعد التوصيل',
    ussd: 'تم استلام رقم المرجع وسيتم التحقق من دفعتك خلال دقائق',
    bank: 'سيتم مراجعة التحويل وتأكيد طلبك خلال ساعة'
  };
  document.getElementById('success-note').textContent = notes[_paymentMethod] || notes.cash;

  document.getElementById('success-modal').classList.add('active');
}
