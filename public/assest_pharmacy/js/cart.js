const CART_KEY = 'pharma_cart';

function getCart() {
  try { return JSON.parse(localStorage.getItem(CART_KEY)) || null; }
  catch { return null; }
}

function getCartCount() {
  const cart = getCart();
  if (!cart) return 0;
  return cart.items.reduce((sum, i) => sum + i.qty, 0);
}

function getCartTotal() {
  const cart = getCart();
  if (!cart) return 0;
  return cart.items.reduce((sum, i) => sum + i.price * i.qty, 0);
}

function addToCart(pharmacyId, pharmacyName, medicine) {
  const cart = getCart();
  if (cart && cart.pharmacyId !== pharmacyId) {
    return { conflict: true, existingPharmacy: cart.pharmacyName };
  }
  const newCart = cart || { pharmacyId, pharmacyName, items: [] };
  const existing = newCart.items.find(i => i.medicineId === medicine.id);
  if (existing) {
    existing.qty += 1;
  } else {
    newCart.items.push({
      medicineId: medicine.id,
      nameAr:     medicine.nameAr,
      nameEn:     medicine.nameEn,
      price:      medicine.price,
      qty:        1
    });
  }
  localStorage.setItem(CART_KEY, JSON.stringify(newCart));
  updateCartBadge();
  renderCartDrawer();        // refresh drawer content immediately
  _updatePharmacyBanner(newCart.pharmacyName);
  return { success: true };
}

function removeFromCart(medicineId) {
  const cart = getCart();
  if (!cart) return;
  cart.items = cart.items.filter(i => i.medicineId !== medicineId);
  if (cart.items.length === 0) {
    localStorage.removeItem(CART_KEY);
  } else {
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
  }
  updateCartBadge();
  renderCartDrawer();
}

function updateQty(medicineId, qty) {
  const cart = getCart();
  if (!cart) return;
  const item = cart.items.find(i => i.medicineId === medicineId);
  if (!item) return;
  if (qty <= 0) { removeFromCart(medicineId); return; }
  item.qty = qty;
  localStorage.setItem(CART_KEY, JSON.stringify(cart));
  updateCartBadge();
  renderCartDrawer();
}

function clearCart() {
  localStorage.removeItem(CART_KEY);
  updateCartBadge();
  renderCartDrawer();
  _updatePharmacyBanner('');
}

function _updatePharmacyBanner(pharmacyName) {
  const banner = document.getElementById('cart-pharmacy-banner');
  if (!banner) return;
  if (pharmacyName) {
    banner.textContent    = '📍 ' + pharmacyName;
    banner.style.display  = 'block';
  } else {
    banner.style.display  = 'none';
    banner.textContent    = '';
  }
}

function updateCartBadge() {
  const badge = document.getElementById('cart-badge');
  const count = getCartCount();
  if (badge) {
    badge.textContent = count;
    badge.style.display = count > 0 ? 'flex' : 'none';
  }
}

function renderCartDrawer() {
  const drawer  = document.getElementById('cart-drawer-items');
  const totalEl = document.getElementById('cart-drawer-total');
  if (!drawer) return;

  const cart = getCart();
  if (!cart || !cart.items.length) {
    drawer.innerHTML = '<div style="text-align:center;padding:40px 20px;color:var(--text-muted)">🛒<br><br>السلة فارغة</div>';
    if (totalEl) totalEl.textContent = '₪0.00';
    _updatePharmacyBanner('');
    return;
  }
  _updatePharmacyBanner(cart.pharmacyName);

  drawer.innerHTML = cart.items.map(item => `
    <div class="cart-item">
      <div style="flex:1;min-width:0">
        <div style="font-weight:600;margin-bottom:2px">${item.nameAr}</div>
        <div style="font-size:12px;color:var(--text-muted)">${item.nameEn}</div>
        <div style="color:var(--primary);font-weight:700;margin-top:2px">₪${item.price.toFixed(2)}</div>
      </div>
      <div style="display:flex;align-items:center;gap:6px;flex-shrink:0">
        <button onclick="updateQty('${item.medicineId}',${item.qty - 1})"
          style="width:28px;height:28px;border:1px solid var(--border);border-radius:50%;background:white;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center">−</button>
        <span style="font-weight:600;min-width:20px;text-align:center">${item.qty}</span>
        <button onclick="updateQty('${item.medicineId}',${item.qty + 1})"
          style="width:28px;height:28px;border:1px solid var(--border);border-radius:50%;background:white;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center">+</button>
        <button onclick="removeFromCart('${item.medicineId}')"
          style="color:#ef4444;background:none;border:none;cursor:pointer;font-size:18px;padding:0 4px">✕</button>
      </div>
    </div>`).join('');

  if (totalEl) totalEl.textContent = '₪' + getCartTotal().toFixed(2);
}

function injectCartDrawer() {
  if (document.getElementById('cart-drawer')) return;
  const cart         = getCart();
  const pharmacyName = cart ? cart.pharmacyName : '';
  document.body.insertAdjacentHTML('beforeend', `
    <div class="cart-overlay" id="cart-overlay" onclick="closeCart()"></div>
    <div class="cart-drawer" id="cart-drawer">
      <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;flex-shrink:0">
        <h3 style="margin:0;font-size:16px">🛒 سلة التسوق</h3>
        <button onclick="closeCart()" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--text-muted)">✕</button>
      </div>
      <div id="cart-pharmacy-banner" style="padding:8px 20px;background:var(--primary-50);font-size:13px;color:var(--primary);display:${pharmacyName ? 'block' : 'none'}">${pharmacyName ? '📍 ' + pharmacyName : ''}</div>
      <div id="cart-drawer-items" style="flex:1;overflow-y:auto;padding:0 20px"></div>
      <div style="padding:16px 20px;border-top:1px solid var(--border);flex-shrink:0">
        <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:13px">
          <span style="color:var(--text-muted)">المجموع الفرعي</span>
          <span id="cart-drawer-total" style="font-weight:700;color:var(--primary)">₪0.00</span>
        </div>
        <div style="display:flex;justify-content:space-between;margin-bottom:14px;font-size:13px">
          <span style="color:var(--text-muted)">رسوم التوصيل</span>
          <span>₪5.00</span>
        </div>
        <button onclick="window.location.href='checkout.html'"
          style="width:100%;background:var(--primary);color:white;border:none;border-radius:30px;padding:14px;font-size:15px;cursor:pointer;font-family:inherit;font-weight:600">
          ✓ إتمام الطلب
        </button>
        <button onclick="if(confirm('هل تريد مسح السلة؟')){clearCart()}"
          style="width:100%;background:none;border:none;color:#ef4444;margin-top:8px;cursor:pointer;font-family:inherit;font-size:13px">
          مسح السلة
        </button>
      </div>
    </div>`);
  renderCartDrawer();
}

function openCart() {
  document.getElementById('cart-drawer')?.classList.add('open');
  document.getElementById('cart-overlay')?.classList.add('open');
}

function closeCart() {
  document.getElementById('cart-drawer')?.classList.remove('open');
  document.getElementById('cart-overlay')?.classList.remove('open');
}

function showToast(msg, type = 'success') {
  const old = document.getElementById('ph-toast');
  if (old) old.remove();
  const el = document.createElement('div');
  el.id = 'ph-toast';
  el.style.cssText = [
    'position:fixed', 'bottom:24px', 'left:50%', 'transform:translateX(-50%)',
    `background:${type === 'error' ? '#ef4444' : '#1A7B4B'}`,
    'color:white', 'padding:12px 26px', 'border-radius:24px', 'font-size:14px',
    'z-index:9999', 'box-shadow:0 4px 12px rgba(0,0,0,.2)', 'white-space:nowrap',
    'font-family:inherit'
  ].join(';');
  el.textContent = msg;
  document.body.appendChild(el);
  setTimeout(() => el.remove(), 3000);
}

// Detect GPS on load and store in localStorage
(function _initGPS() {
  if (!navigator.geolocation) return;
  if (localStorage.getItem('pharma_user_lat')) return;
  navigator.geolocation.getCurrentPosition(
    pos => {
      localStorage.setItem('pharma_user_lat', pos.coords.latitude);
      localStorage.setItem('pharma_user_lng', pos.coords.longitude);
    },
    () => {},
    { timeout: 5000 }
  );
})();

// Sync badge across tabs
window.addEventListener('storage', e => {
  if (e.key === CART_KEY) {
    updateCartBadge();
    renderCartDrawer();
  }
});
