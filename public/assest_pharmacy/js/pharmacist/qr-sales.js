// TODO: Laravel API — POST /api/pharmacist/sales  GET /api/pharmacist/sales

let _cart = [], _allProducts = [], _qrHistory = [];

/* ── Camera scanner state ─────────────────────────────── */
let _scanner       = null;   // Html5Qrcode instance
let _scannerActive = false;
let _facingMode    = 'environment'; // 'environment' = back cam, 'user' = front cam
let _lastScanCode  = '';
let _lastScanTime  = 0;      // debounce duplicate detections

/* ── QRCode display instance ─────────────────────────── */
let _qrCodeInstance = null;

/* ═══════════════════════════════════════════════════════ */
async function initQrSales() {
  await _loadProducts();
  _renderProductGrid('');
  _setupSearch();
  _setupCamera();
  _setupCart();
  _setupProductQrModal();
  _setupHardwareScanner();
  _loadHistory();
}

/* ── Products ────────────────────────────────────────── */
async function _loadProducts() {
  if (!DEMO_MODE) {
    try {
      const uid  = window.currentUser?.uid;
      const snap = await db.collection('products').where('pharmacyId','==',uid).get();
      _allProducts = snap.docs.map(d => ({ id: d.id, ...d.data() }));
    } catch(e) { _allProducts = [...mockProducts]; }
  } else {
    _allProducts = [...mockProducts];
  }
}

function _renderProductGrid(query) {
  const list = document.getElementById('qr-product-list');
  const q    = query.toLowerCase();
  const filtered = _allProducts.filter(p =>
    !q || p.nameAr?.toLowerCase().includes(q) || p.nameEn?.toLowerCase().includes(q) || p.id?.toLowerCase().includes(q)
  );
  if (!filtered.length) {
    list.innerHTML = `<div class="empty-state"><span class="empty-icon">🔍</span><p>لا توجد نتائج</p></div>`;
    return;
  }
  list.innerHTML = filtered.map(p => `
    <div class="qr-product-card ${p.stock <= 0 ? 'out-of-stock' : ''}" onclick="addToCart('${p.id}')">
      <button class="qp-qr-btn" title="عرض QR" onclick="event.stopPropagation();showProductQr('${p.id}')">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
          <rect x="3" y="14" width="7" height="7" rx="1"/>
          <path d="M14 14h2v2h-2zM18 14h3M14 18h3M20 18v3M17 20h3"/>
        </svg>
      </button>
      <div class="qp-name">${esc(p.nameAr)}</div>
      <div class="qp-sub">${esc(p.nameEn)}</div>
      <div class="qp-footer">
        <span class="qp-price">₪${fmt(p.price)}</span>
        ${stockBadge(p.stock, p.minStock)}
      </div>
    </div>`).join('');
}

/* ── Text search ─────────────────────────────────────── */
function _setupSearch() {
  document.getElementById('qr-search')?.addEventListener('input', debounce(e => {
    _renderProductGrid(e.target.value);
  }, 250));

  // When user presses Enter on the search box — treat as hardware-scanner-style lookup
  document.getElementById('qr-search')?.addEventListener('keydown', e => {
    if (e.key === 'Enter') {
      e.preventDefault();
      _handleBarcodeInput(e.target.value.trim());
    }
  });
}

/* ── Camera scanner ──────────────────────────────────── */
function _setupCamera() {
  document.getElementById('btn-open-camera')?.addEventListener('click', _openCameraModal);
  document.getElementById('btn-close-camera')?.addEventListener('click', _closeCameraModal);
  document.getElementById('btn-stop-camera')?.addEventListener('click',  _closeCameraModal);
  document.getElementById('btn-switch-camera')?.addEventListener('click', _switchCamera);

  const manualIn  = document.getElementById('barcode-manual-input');
  const manualBtn = document.getElementById('btn-manual-submit');

  // Hardware scanner inside the modal (types fast then Enter)
  manualIn?.addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); _submitManual(); }
  });
  manualBtn?.addEventListener('click', _submitManual);
}

function _openCameraModal() {
  document.getElementById('camera-modal').classList.add('active');
  document.body.style.overflow = 'hidden';
  const manualIn = document.getElementById('barcode-manual-input');
  manualIn.value = '';
  _startCamera();
}

async function _closeCameraModal() {
  await _stopCamera();
  document.getElementById('camera-modal').classList.remove('active');
  document.body.style.overflow = '';
}

async function _startCamera() {
  if (!window.Html5Qrcode) {
    _setScanHint('مكتبة المسح غير محملة — يرجى تحديث الصفحة.');
    return;
  }

  try {
    _scanner = new Html5Qrcode('qr-reader');
    _setScanHint('جاري تشغيل الكاميرا...');

    await _scanner.start(
      { facingMode: _facingMode },
      {
        fps: 15,
        qrbox: { width: 260, height: 180 },
        aspectRatio: 1.5,
        // Support all common retail barcode formats + QR
        formatsToSupport: (() => {
          try {
            const F = Html5QrcodeSupportedFormats;
            return [F.QR_CODE, F.EAN_13, F.EAN_8, F.UPC_A, F.UPC_E,
                    F.CODE_128, F.CODE_39, F.CODE_93, F.DATA_MATRIX,
                    F.ITF, F.PDF_417, F.AZTEC];
          } catch(e) { return undefined; } // use defaults if enum not available
        })(),
      },
      (decodedText) => { _onCameraScan(decodedText); },
      (_err) => { /* per-frame errors are normal — ignore */ }
    );

    _scannerActive = true;
    _setScanHint('وجّه الكاميرا نحو باركود المنتج أو رمز QR');
  } catch (err) {
    _scannerActive = false;
    if (err && (err.toString().includes('Permission') || err.toString().includes('denied'))) {
      _setScanHint('⛔ تم رفض إذن الكاميرا — يمكنك إدخال الباركود يدوياً أدناه.');
    } else if (err && err.toString().includes('not found')) {
      _setScanHint('📵 لا توجد كاميرا متاحة — يمكنك إدخال الباركود يدوياً أدناه.');
    } else {
      _setScanHint('تعذّر فتح الكاميرا — يمكنك إدخال الباركود يدوياً أدناه.');
    }
    document.getElementById('barcode-manual-input')?.focus();
  }
}

async function _stopCamera() {
  if (_scanner && _scannerActive) {
    try {
      await _scanner.stop();
      _scanner.clear();
    } catch(e) { /* already stopped */ }
    _scanner       = null;
    _scannerActive = false;
  }
}

async function _switchCamera() {
  if (!_scannerActive) return;
  await _stopCamera();
  _facingMode = _facingMode === 'environment' ? 'user' : 'environment';
  await _startCamera();
}

function _setScanHint(text) {
  const el = document.getElementById('scan-hint-text');
  if (el) el.textContent = text;
}

/* Called when the camera successfully detects a code */
function _onCameraScan(code) {
  // Debounce: ignore same code within 1.5 s to prevent duplicate adds
  const now = Date.now();
  if (code === _lastScanCode && now - _lastScanTime < 1500) return;
  _lastScanCode = code;
  _lastScanTime = now;

  _beep();
  _flashSuccess();
  _closeCameraModal();
  _handleBarcodeInput(code);
}

function _submitManual() {
  const val = document.getElementById('barcode-manual-input')?.value.trim();
  if (!val) return;
  document.getElementById('barcode-manual-input').value = '';
  _onCameraScan(val);
}

/* ── Hardware / USB scanner (global keydown capture) ─── */
function _setupHardwareScanner() {
  let _hwBuf   = '';
  let _hwTimer = null;

  // Hardware scanners fire keydown events faster than 30 ms apart
  // They always end with Enter
  document.addEventListener('keydown', e => {
    // Let the camera modal's manual input handle keys when it's open
    if (document.getElementById('camera-modal').classList.contains('active')) return;
    // Ignore modifier-key combos
    if (e.ctrlKey || e.altKey || e.metaKey) return;

    clearTimeout(_hwTimer);

    if (e.key === 'Enter') {
      if (_hwBuf.length >= 3) {
        _handleBarcodeInput(_hwBuf);
      }
      _hwBuf = '';
      return;
    }

    if (e.key.length === 1) {
      _hwBuf += e.key;
    }

    // If no more keys arrive within 80 ms, clear the buffer (user typed, not scanner)
    _hwTimer = setTimeout(() => { _hwBuf = ''; }, 80);
  });
}

/* ── Core barcode-to-product logic ──────────────────── */
function _handleBarcodeInput(code) {
  if (!code) return;
  const q = code.toLowerCase();
  const product = _allProducts.find(p =>
    p.id        === code ||
    p.barcode   === code ||
    p.nameEn?.toLowerCase() === q ||
    p.nameAr    === code
  );

  if (product) {
    addToCart(product.id);
    // Show which product was found in the search box temporarily
    const search = document.getElementById('qr-search');
    if (search) {
      search.value = product.nameAr;
      _renderProductGrid(product.nameAr);
      setTimeout(() => { search.value = ''; _renderProductGrid(''); }, 2000);
    }
  } else {
    // No exact match — filter the product grid so the pharmacist can pick
    document.getElementById('qr-search').value = code;
    _renderProductGrid(code);
  }
}

/* ── Audio + visual scan feedback ───────────────────── */
function _beep() {
  try {
    const ctx  = new (window.AudioContext || window.webkitAudioContext)();
    const osc  = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.connect(gain);
    gain.connect(ctx.destination);
    osc.type            = 'square';
    osc.frequency.value = 1800;
    gain.gain.setValueAtTime(0.25, ctx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.08);
    osc.start(ctx.currentTime);
    osc.stop(ctx.currentTime + 0.08);
    ctx.close().catch(()=>{});
  } catch(e) {}
}

function _flashSuccess() {
  const el = document.getElementById('scan-flash');
  if (!el) return;
  el.classList.add('show');
  setTimeout(() => el.classList.remove('show'), 300);
}

/* ── Product QR Code Modal ───────────────────────────── */
function _setupProductQrModal() {
  const closeIds = ['product-qr-close', 'product-qr-close-btn'];
  closeIds.forEach(id => {
    document.getElementById(id)?.addEventListener('click', () => {
      document.getElementById('product-qr-modal').classList.remove('active');
      _qrCodeInstance = null;
    });
  });

  document.getElementById('product-qr-modal')?.addEventListener('click', e => {
    if (e.target === document.getElementById('product-qr-modal')) {
      document.getElementById('product-qr-modal').classList.remove('active');
      _qrCodeInstance = null;
    }
  });
}

function showProductQr(productId) {
  const p = _allProducts.find(x => x.id === productId);
  if (!p) return;

  const modal      = document.getElementById('product-qr-modal');
  const display    = document.getElementById('qr-code-display');
  const nameEl     = document.getElementById('product-qr-name');
  const idEl       = document.getElementById('product-qr-id');

  // Clear previous QR
  display.innerHTML = '';
  _qrCodeInstance   = null;
  nameEl.textContent = p.nameAr + (p.nameEn ? ' — ' + p.nameEn : '');
  idEl.textContent   = p.id;

  if (window.QRCode) {
    // QR code encodes the product ID so the camera scanner can look it up
    _qrCodeInstance = new QRCode(display, {
      text: p.id,
      width: 200,
      height: 200,
      colorDark: '#1A7B4B',
      colorLight: '#ffffff',
      correctLevel: QRCode.CorrectLevel.M,
    });
  } else {
    display.innerHTML = `<div style="padding:40px;color:var(--text-muted);font-size:13px">مكتبة QR غير محملة</div>`;
  }

  modal.classList.add('active');
}

/* ── Cart ────────────────────────────────────────────── */
function _setupCart() {
  document.getElementById('btn-checkout')?.addEventListener('click', checkout);
  document.getElementById('btn-clear-cart')?.addEventListener('click', clearCart);
  document.getElementById('receipt-close')?.addEventListener('click', () => {
    document.getElementById('receipt-modal').classList.remove('active');
  });
}

function addToCart(productId) {
  const p = _allProducts.find(x => x.id === productId);
  if (!p || p.stock <= 0) { alert('هذا المنتج غير متوفر في المخزون.'); return; }
  const existing = _cart.find(x => x.id === productId);
  if (existing) {
    if (existing.qty >= p.stock) { alert('لا يوجد مخزون كافٍ.'); return; }
    existing.qty++;
  } else {
    _cart.push({ ...p, qty: 1 });
  }
  _renderCart();
}

function removeFromCart(productId) {
  _cart = _cart.filter(x => x.id !== productId);
  _renderCart();
}

function updateQty(productId, delta) {
  const item = _cart.find(x => x.id === productId);
  if (!item) return;
  item.qty += delta;
  if (item.qty <= 0) { removeFromCart(productId); return; }
  _renderCart();
}

function clearCart() {
  _cart = [];
  _renderCart();
}

function _renderCart() {
  const ul    = document.getElementById('cart-items');
  const count = document.getElementById('cart-count');
  const sub   = document.getElementById('cart-sub');
  const total = document.getElementById('cart-total');
  const btn   = document.getElementById('btn-checkout');

  const totalQty = _cart.reduce((s, i) => s + i.qty, 0);
  const totalAmt = _cart.reduce((s, i) => s + i.qty * i.price, 0);

  count.textContent = totalQty;
  sub.textContent   = '₪' + fmt(totalAmt);
  total.textContent = '₪' + fmt(totalAmt);
  btn.disabled      = _cart.length === 0;

  if (!_cart.length) {
    ul.innerHTML = `<li style="padding:32px;text-align:center;color:var(--text-muted)">
      <div style="font-size:36px;margin-bottom:8px">🛒</div><p>السلة فارغة — امسح منتجاً للإضافة</p>
    </li>`;
    return;
  }

  ul.innerHTML = _cart.map(item => `
    <li style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--border)">
      <div style="flex:1;min-width:0">
        <div style="font-weight:600;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${esc(item.nameAr)}</div>
        <div style="font-size:12px;color:var(--text-muted)">₪${fmt(item.price)} × ${item.qty}</div>
      </div>
      <div style="display:flex;align-items:center;gap:4px">
        <button class="btn btn-sm btn-ghost" style="padding:2px 8px" onclick="updateQty('${item.id}',-1)">−</button>
        <span style="min-width:24px;text-align:center;font-weight:600">${item.qty}</span>
        <button class="btn btn-sm btn-ghost" style="padding:2px 8px" onclick="updateQty('${item.id}',+1)">+</button>
      </div>
      <strong style="min-width:60px;text-align:left;color:var(--primary)">₪${fmt(item.qty*item.price)}</strong>
      <button class="btn btn-sm btn-danger" style="padding:2px 8px" onclick="removeFromCart('${item.id}')">✕</button>
    </li>`).join('');
}

/* ── Checkout ────────────────────────────────────────── */
async function checkout() {
  if (!_cart.length) return;
  const total  = _cart.reduce((s, i) => s + i.qty * i.price, 0);
  const saleId = 'QR-' + Date.now();

  const sale = {
    id: saleId,
    items: _cart.map(i => ({ id: i.id, nameAr: i.nameAr, nameEn: i.nameEn, qty: i.qty, price: i.price })),
    total,
    type: 'qr',
    pharmacyId: window.currentUser?.uid || 'pharm-001',
    createdAt: new Date().toISOString(),
  };

  // TODO: Laravel API POST /api/pharmacist/sales
  if (!DEMO_MODE) {
    try { await db.collection('qr_sales').add(sale); } catch(e) { console.warn(e); }
  }

  _qrHistory.unshift(sale);
  _renderHistory();

  _cart.forEach(item => {
    const p = _allProducts.find(x => x.id === item.id);
    if (p) p.stock = Math.max(0, p.stock - item.qty);
  });
  _renderProductGrid(document.getElementById('qr-search')?.value || '');

  _showReceipt(sale);
  _cart = [];
  _renderCart();
}

function _showReceipt(sale) {
  const rows = sale.items.map(i =>
    `<tr><td>${esc(i.nameAr)}</td><td style="text-align:center">${i.qty}</td><td style="text-align:left">₪${fmt(i.qty*i.price)}</td></tr>`
  ).join('');
  document.getElementById('receipt-body').innerHTML = `
    <div style="text-align:center;margin-bottom:16px">
      <div style="font-size:24px">💊</div>
      <strong>PharmacyLink</strong><br>
      <span style="font-size:12px;color:var(--text-muted)">فاتورة رقم: ${esc(sale.id)}</span><br>
      <span style="font-size:12px;color:var(--text-muted)">${new Date().toLocaleString('ar-EG')}</span>
    </div>
    <table style="width:100%;border-collapse:collapse">
      <thead><tr style="border-bottom:2px solid var(--border)">
        <th>المنتج</th><th style="text-align:center">الكمية</th><th style="text-align:left">الإجمالي</th>
      </tr></thead>
      <tbody>${rows}</tbody>
      <tfoot>
        <tr style="border-top:2px solid var(--border)">
          <td colspan="2"><strong>الإجمالي</strong></td>
          <td style="text-align:left"><strong style="color:var(--primary)">₪${fmt(sale.total)}</strong></td>
        </tr>
      </tfoot>
    </table>`;
  document.getElementById('receipt-modal').classList.add('active');
}

/* ── Sales History ───────────────────────────────────── */
function _loadHistory() {
  // TODO: Laravel API GET /api/pharmacist/sales?type=qr
  _renderHistory();
}

function _renderHistory() {
  const body = document.getElementById('qr-history-body');
  if (!_qrHistory.length) {
    body.innerHTML = `<tr><td colspan="4"><div class="empty-state"><span class="empty-icon">📋</span><p>لا توجد مبيعات QR بعد</p></div></td></tr>`;
    return;
  }
  body.innerHTML = _qrHistory.slice(0, 20).map(s => `
    <tr>
      <td><strong>${esc(s.id)}</strong></td>
      <td style="font-size:12px">${(s.items||[]).map(i=>esc(i.nameAr)).join('، ')}</td>
      <td><strong style="color:var(--primary)">₪${fmt(s.total)}</strong></td>
      <td class="text-muted">${timeAgo(s.createdAt)}</td>
    </tr>`).join('');
}
