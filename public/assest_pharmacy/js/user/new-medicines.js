// ── New Medicines Page ─────────────────────────────────────

let _newCatFilter  = 'الكل';
let _newCityFilter = '';
let _newSortBy     = 'newest';
let _newPage       = 1;
const PAGE_SIZE    = 12;

function initNewMedicines() {
  _renderCategoryChips();
  _bindFilters();
  _showSkeleton();
  setTimeout(_filterAndRenderNew, 1000);
}

function _renderCategoryChips() {
  const cats = ['الكل', ...new Set(mockNewMedicines.map(m => m.category))];
  document.getElementById('new-cat-chips').innerHTML = cats.map(c => `
    <button class="filter-tab ${c === _newCatFilter ? 'active' : ''}"
            onclick="_setNewCat('${c}')">${c}</button>
  `).join('');
}

function _setNewCat(cat) {
  _newCatFilter = cat;
  _newPage = 1;
  _renderCategoryChips();
  _filterAndRenderNew();
}

function _showSkeleton() {
  const grid = document.getElementById('new-meds-grid');
  grid.innerHTML = Array(6).fill(0).map(() =>
    `<div class="skeleton" style="height:220px;border-radius:12px"></div>`
  ).join('');
}

function _filterAndRenderNew() {
  let list = [...mockNewMedicines];

  if (_newCatFilter !== 'الكل') list = list.filter(m => m.category === _newCatFilter);
  if (_newCityFilter)           list = list.filter(m => m.pharmacyCity === _newCityFilter);

  if (_newSortBy === 'newest') {
    list.sort((a, b) => b.addedAt - a.addedAt);
  } else if (_newSortBy === 'cheapest') {
    list.sort((a, b) => a.price - b.price);
  }

  const total = list.length;
  const paged = list.slice((_newPage - 1) * PAGE_SIZE, _newPage * PAGE_SIZE);
  _renderNewGrid(paged);
  _renderPagination(total);
}

function _renderNewGrid(list) {
  const grid = document.getElementById('new-meds-grid');
  if (!list.length) {
    grid.innerHTML = `
      <div style="grid-column:1/-1" class="empty-state">
        <span class="empty-icon">🆕</span>
        <h3>لا توجد أدوية جديدة</h3>
        <p>جرّب تغيير الفلاتر</p>
      </div>`;
    return;
  }

  const now = Date.now();
  grid.innerHTML = list.map(m => {
    const daysAgo  = Math.floor((now - m.addedAt) / 86400000);
    const daysText = daysAgo === 0
      ? 'اليوم'
      : `منذ ${daysAgo} ${daysAgo === 1 ? 'يوم' : 'أيام'}`;

    return `
      <div class="medicine-card" style="position:relative">
        <span class="badge-new" style="position:absolute;top:10px;left:10px">جديد 🆕</span>
        <div style="margin-bottom:8px;margin-top:4px">
          <span class="badge badge-blue" style="font-size:11px">${esc(m.category)}</span>
        </div>
        <div class="medicine-name-ar">${esc(m.nameAr)}</div>
        <div class="medicine-name-en">${esc(m.nameEn)}</div>
        <div class="medicine-price">₪${m.price.toFixed(2)}</div>
        <div class="medicine-pharmacy">🏪 ${esc(m.pharmacyName)} — ${esc(m.pharmacyCity)}</div>
        <div style="font-size:11px;color:var(--text-muted);margin-top:4px">أضيف ${daysText}</div>
        <div style="display:flex;gap:6px;margin-top:12px">
          <button class="btn btn-primary btn-sm" style="flex:1"
                  onclick="_addNewMedToCart('${m.id}','${m.pharmacyId}','${esc(m.pharmacyName)}')">
            إضافة للسلة 🛒
          </button>
          <button onclick="_toggleFav(this)" style="background:none;border:1.5px solid var(--border);border-radius:var(--radius-sm);padding:5px 10px;cursor:pointer;font-size:15px;transition:all .15s" title="المفضلة">
            🤍
          </button>
        </div>
      </div>`;
  }).join('');
}

function _renderPagination(total) {
  const pages = Math.ceil(total / PAGE_SIZE);
  const el    = document.getElementById('new-pagination');
  if (pages <= 1) { el.innerHTML = ''; return; }
  el.innerHTML = `
    <div class="pagination">
      <span class="pagination-info">الصفحة ${_newPage} من ${pages} (${total} دواء)</span>
      <div class="pagination-btns">
        <button class="btn btn-ghost btn-sm" onclick="_goNewPage(${_newPage - 1})" ${_newPage === 1 ? 'disabled' : ''}>→ السابق</button>
        <button class="btn btn-ghost btn-sm" onclick="_goNewPage(${_newPage + 1})" ${_newPage === pages ? 'disabled' : ''}>التالي ←</button>
      </div>
    </div>`;
}

function _goNewPage(p) {
  if (p < 1) return;
  _newPage = p;
  _filterAndRenderNew();
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function _addNewMedToCart(medId, pharmacyId, pharmacyName) {
  const med = mockNewMedicines.find(m => m.id === medId);
  if (!med) return;
  const result = addToCart(pharmacyId, pharmacyName, med);
  if (result.conflict) {
    if (confirm(`سلتك تحتوي على أدوية من ${result.existingPharmacy}.\nهل تريد مسح السلة والبدء من ${pharmacyName}؟`)) {
      clearCart();
      addToCart(pharmacyId, pharmacyName, med);
      showToast('تمت الإضافة للسلة ✓');
    }
    return;
  }
  showToast('تمت الإضافة للسلة ✓');
}

function _toggleFav(btn) {
  const isActive = btn.textContent === '❤️';
  btn.textContent = isActive ? '🤍' : '❤️';
  showToast(isActive ? 'تمت الإزالة من المفضلة' : 'تمت الإضافة للمفضلة');
}

function _bindFilters() {
  document.getElementById('new-city-filter')?.addEventListener('change', e => {
    _newCityFilter = e.target.value;
    _newPage = 1;
    _filterAndRenderNew();
  });
  document.getElementById('new-sort')?.addEventListener('change', e => {
    _newSortBy = e.target.value;
    _newPage = 1;
    _filterAndRenderNew();
  });
}
