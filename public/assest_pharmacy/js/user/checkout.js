// Checkout — cart review + delivery details, cash on delivery. POST /api/orders

function initCheckout() {
  const cart = getCart();
  if (!cart || !cart.items.length) {
    document.getElementById('checkout-main').innerHTML = `
      <div class="empty-state">
        <span class="empty-icon">🛒</span>
        <h3>السلة فارغة</h3>
        <p>أضف أدوية للسلة أولاً من صفحة الصيدليات.</p>
        <a href="/user/search" class="btn btn-primary" style="margin-top:16px">تصفح الصيدليات</a>
      </div>`;
    return;
  }

  document.getElementById('cart-pharmacy-name').textContent = '🏪 ' + cart.pharmacyName;
  _renderCheckoutItems();
  document.getElementById('co-confirm').addEventListener('click', _confirmOrder);
}

function _renderCheckoutItems() {
  const cart = getCart();
  document.getElementById('checkout-items').innerHTML = cart.items.map(item => `
    <tr>
      <td>
        <div style="font-weight:600">${esc(item.nameAr)}</div>
        <div style="font-size:12px;color:var(--text-muted)">${esc(item.nameEn || '')}</div>
      </td>
      <td>
        <div style="display:flex;align-items:center;gap:5px">
          <button type="button" onclick="_changeQty(${item.medicineId},${item.qty - 1})"
                  style="width:24px;height:24px;border:1px solid var(--border);border-radius:50%;background:white;cursor:pointer;font-size:14px;display:inline-flex;align-items:center;justify-content:center">−</button>
          <span style="min-width:22px;text-align:center;font-weight:600">${item.qty}</span>
          <button type="button" onclick="_changeQty(${item.medicineId},${item.qty + 1})"
                  style="width:24px;height:24px;border:1px solid var(--border);border-radius:50%;background:white;cursor:pointer;font-size:14px;display:inline-flex;align-items:center;justify-content:center">+</button>
        </div>
      </td>
      <td>₪${item.price.toFixed(2)}</td>
      <td style="color:var(--primary);font-weight:700">₪${(item.price * item.qty).toFixed(2)}</td>
      <td>
        <button type="button" onclick="_removeCheckoutItem(${item.medicineId})"
                style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:18px;padding:0">✕</button>
      </td>
    </tr>`).join('');

  document.getElementById('co-total').textContent = '₪' + getCartTotal().toFixed(2);
}

function _changeQty(medicineId, qty) {
  updateQty(medicineId, qty);
  const cart = getCart();
  if (!cart || !cart.items.length) { window.location.href = '/user/search'; return; }
  _renderCheckoutItems();
}

function _removeCheckoutItem(medicineId) {
  removeFromCart(medicineId);
  const cart = getCart();
  if (!cart || !cart.items.length) { window.location.href = '/user/search'; return; }
  _renderCheckoutItems();
}

async function _confirmOrder() {
  const msg     = document.getElementById('co-msg');
  const address = document.getElementById('co-address').value.trim();
  const phone   = document.getElementById('co-phone').value.trim();
  msg.textContent = '';

  if (!address) { msg.textContent = 'عنوان التوصيل مطلوب.'; return; }
  if (!phone)   { msg.textContent = 'رقم الهاتف مطلوب.'; return; }

  const cart = getCart();
  if (!cart || !cart.items.length) { window.location.href = '/user/search'; return; }

  const btn = document.getElementById('co-confirm');
  btn.disabled = true;
  btn.textContent = 'جاري إرسال الطلب...';

  const payload = {
    pharmacy_id: cart.pharmacyId,
    delivery_address: address,
    phone,
    notes: document.getElementById('co-notes').value.trim() || null,
    items: cart.items.map(i => ({ medicine_id: i.medicineId, qty: i.qty })),
  };

  const { ok, data } = await phFetch('/api/orders', { method: 'POST', body: JSON.stringify(payload) });

  if (!ok) {
    msg.textContent = data?.message || 'تعذر إرسال الطلب. حاول مرة أخرى.';
    btn.disabled = false;
    btn.textContent = '✓ تأكيد الطلب (الدفع عند الاستلام)';
    return;
  }

  clearCart();
  window.location.href = '/user/orders';
}
