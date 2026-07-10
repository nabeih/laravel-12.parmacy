// ── Pharmacy Details Page ──────────────────────────────────

let _pharmacyId    = null;
let _pharmacy      = null;
let _products      = [];
let _catFilter     = 'الكل';
let _pendingProduct = null;

function initPharmacyDetails() {
  const params = new URLSearchParams(window.location.search);
  _pharmacyId  = params.get('id');

  _pharmacy = (mockPharmaciesList || []).find(p => p.id === _pharmacyId);
  if (!_pharmacy) {
    document.querySelector('.main-content').innerHTML = `
      <div class="empty-state">
        <span class="empty-icon">🏪</span>
        <h3>الصيدلية غير موجودة</h3>
        <p>تأكد من الرابط أو <a href="pharmacies.html" style="color:var(--primary)">عد للقائمة</a></p>
      </div>`;
    return;
  }

  _products = (mockPharmacyProducts || {})[_pharmacyId] || [];
  document.title = `PharmacyLink — ${_pharmacy.name}`;

  _renderHeader();
  _renderCategoryChips();

  setTimeout(() => {
    _filterAndRender();
    _bindSearch();
  }, 600);
}

function _renderHeader() {
  const p         = _pharmacy;
  const openBadge = p.isOpen
    ? `<span class="badge-open">مفتوح الآن</span>`
    : `<span class="badge-closed">مغلق</span>`;

  document.getElementById('pharmacy-header').innerHTML = `
    <div class="card" style="margin-bottom:20px">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:14px">
        <div style="flex:1;min-width:0">
          <h2 style="font-size:22px;font-weight:800;margin-bottom:6px">${esc(p.name)}</h2>
          <div style="color:var(--text-muted);font-size:14px;margin-bottom:4px">📍 ${esc(p.city)} — ${esc(p.address)}</div>
          <a href="tel:${esc(p.phone)}" style="color:var(--primary);font-size:14px;font-weight:600;display:block;margin-bottom:8px">
            📞 ${esc(p.phone)}
          </a>
          <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
            ${openBadge}
            <span style="font-size:13px;color:var(--text-muted)">🕐 ${esc(p.workingHours)}</span>
            <span style="font-size:13px;color:var(--text-muted)">⭐ ${p.rating}</span>
          </div>
        </div>
        <a href="chats.html?pharmacy=${esc(p.id)}" class="btn btn-outline">💬 تواصل مع الصيدلية</a>
      </div>
    </div>`;
}

function _getCategories() {
  return ['الكل', ...new Set(_products.map(p => p.category))];
}

function _renderCategoryChips() {
  const cats = _getCategories();
  document.getElementById('category-chips').innerHTML = cats.map(c => `
    <button class="filter-tab ${c === _catFilter ? 'active' : ''}"
            onclick="_setCat('${c}')">${c}</button>
  `).join('');
}

function _setCat(cat) {
  _catFilter = cat;
  _renderCategoryChips();
  _filterAndRender();
}

function _filterAndRender() {
  const search = (document.getElementById('prod-search')?.value || '').toLowerCase();
  let list = [..._products];
  if (_catFilter !== 'الكل') list = list.filter(p => p.category === _catFilter);
  if (search) list = list.filter(p =>
    p.nameAr.toLowerCase().includes(search) || p.nameEn.toLowerCase().includes(search)
  );
  _renderProducts(list);
}

function _renderProducts(list) {
  const grid = document.getElementById('products-grid');
  if (!list.length) {
    grid.innerHTML = `
      <div style="grid-column:1/-1" class="empty-state">
        <span class="empty-icon">💊</span>
        <p>لا توجد أدوية مطابقة</p>
      </div>`;
    return;
  }

  grid.innerHTML = list.map(p => {
    let stockBadge;
    if (p.stock === 0)            stockBadge = `<span class="badge badge-red">نفذ</span>`;
    else if (p.stock <= p.minStock) stockBadge = `<span class="badge badge-yellow">كمية محدودة</span>`;
    else                            stockBadge = `<span class="badge badge-green">متوفر</span>`;

    const outOfStock = p.stock === 0;
    return `
      <div class="medicine-card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:6px">
          <span class="badge badge-blue" style="font-size:11px">${esc(p.category)}</span>
          ${stockBadge}
        </div>
        <div class="medicine-name-ar">${esc(p.nameAr)}</div>
        <div class="medicine-name-en">${esc(p.nameEn)}</div>
        <div class="medicine-price">₪${p.price.toFixed(2)}</div>
        <button class="btn btn-primary btn-sm"
                style="width:100%;margin-top:12px;${outOfStock ? 'opacity:.5;cursor:not-allowed' : ''}"
                onclick="_addToCart('${p.id}')" ${outOfStock ? 'disabled' : ''}>
          ${outOfStock ? 'نفذ المخزون' : 'إضافة للسلة 🛒'}
        </button>
      </div>`;
  }).join('');
}

function _addToCart(productId) {
  const product = _products.find(p => p.id === productId);
  if (!product || product.stock === 0) return;

  const result = addToCart(_pharmacyId, _pharmacy.name, product);
  if (result.conflict) {
    _pendingProduct = product;
    document.getElementById('conflict-pharmacy-name').textContent = result.existingPharmacy;
    document.getElementById('conflict-modal').classList.add('active');
    return;
  }
  showToast('تمت الإضافة للسلة ✓');
}

function _confirmClearCart() {
  clearCart();
  if (_pendingProduct) {
    addToCart(_pharmacyId, _pharmacy.name, _pendingProduct);
    showToast('تمت الإضافة للسلة ✓');
    _pendingProduct = null;
  }
  document.getElementById('conflict-modal').classList.remove('active');
}

function _cancelClearCart() {
  _pendingProduct = null;
  document.getElementById('conflict-modal').classList.remove('active');
}

function _bindSearch() {
  document.getElementById('prod-search')?.addEventListener('input',
    debounce(_filterAndRender, 300));
}
