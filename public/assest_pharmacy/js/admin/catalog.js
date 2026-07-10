// ── Admin Catalog ─────────────────────────────────────────────

const CAT_PAGE_SIZE = 12;
let _catPage     = 1;
let _catSearch   = '';
let _catCategory = '';
let _catRx       = '';
let _editingCat  = null;
let _selectedIds = new Set();

function initAdminCatalog() {
  _renderCatalog();
}

function _onCatSearch() {
  _catSearch = document.getElementById('cat-search').value.trim();
  _catPage   = 1;
  _renderCatalog();
}

function _onCatFilter() {
  _catCategory = document.getElementById('cat-category').value;
  _catRx       = document.getElementById('cat-rx').value;
  _catPage     = 1;
  _renderCatalog();
}

function _getFilteredCatalog() {
  return mockCatalog.filter(c => {
    const s            = _catSearch.toLowerCase();
    const matchSearch  = !s || c.nameAr.includes(s) || c.nameEn.toLowerCase().includes(s) || c.manufacturer.includes(s);
    const matchCat     = !_catCategory || c.category === _catCategory;
    const matchRx      = !_catRx || (_catRx === 'rx' ? c.rx : !c.rx);
    return matchSearch && matchCat && matchRx;
  });
}

function _renderCatalog() {
  const filtered = _getFilteredCatalog();
  const start    = (_catPage - 1) * CAT_PAGE_SIZE;
  const page     = filtered.slice(start, start + CAT_PAGE_SIZE);
  const tbody    = document.getElementById('cat-tbody');

  document.getElementById('select-all').checked = false;

  if (!page.length) {
    tbody.innerHTML = `<tr><td colspan="9" class="admin-empty" style="padding:30px">لا توجد نتائج</td></tr>`;
    document.getElementById('cat-pagination').innerHTML = '';
    return;
  }

  tbody.innerHTML = page.map(c => `
    <tr>
      <td><input type="checkbox" class="cat-check" value="${c.id}" ${_selectedIds.has(c.id)?'checked':''} onchange="_onCheck(this)"></td>
      <td>
        <div style="font-weight:600">${esc(c.nameAr)}</div>
        <div style="font-size:12px;color:#64748b" dir="ltr">${esc(c.nameEn)}</div>
      </td>
      <td>${esc(c.category)}</td>
      <td>${esc(c.manufacturer)}</td>
      <td dir="ltr">${fmtMoney(c.price)}</td>
      <td>${c.rx ? '<span class="requires-rx">وصفة طبية</span>' : '<span class="otc-badge">OTC</span>'}</td>
      <td>${fmtNum(c.stock)}</td>
      <td>${fmtNum(c.pharmacies)}</td>
      <td>
        <div style="display:flex;gap:4px">
          <button class="admin-btn admin-btn-outline admin-btn-sm" onclick="_editCat('${c.id}')">تعديل</button>
          <button class="admin-btn admin-btn-danger admin-btn-sm" onclick="_confirmDeleteCat('${c.id}')">حذف</button>
        </div>
      </td>
    </tr>`).join('');

  _updateBulkBar();

  // Pagination
  const total      = filtered.length;
  const totalPages = Math.ceil(total / CAT_PAGE_SIZE);
  const pg         = document.getElementById('cat-pagination');
  if (totalPages <= 1) { pg.innerHTML = ''; return; }

  let html = `<span style="font-size:13px;color:#64748b;margin-left:8px">إجمالي: ${total}</span>`;
  html += `<button class="admin-page-btn" ${_catPage<=1?'disabled':''} onclick="_catGoPage(${_catPage-1})">›</button>`;
  for (let i = 1; i <= totalPages; i++) {
    html += `<button class="admin-page-btn${i===_catPage?' active':''}" onclick="_catGoPage(${i})">${i}</button>`;
  }
  html += `<button class="admin-page-btn" ${_catPage>=totalPages?'disabled':''} onclick="_catGoPage(${_catPage+1})">‹</button>`;
  pg.innerHTML = html;
}

function _catGoPage(p) { _catPage = p; _renderCatalog(); }

function _onCheck(cb) {
  if (cb.checked) _selectedIds.add(cb.value);
  else            _selectedIds.delete(cb.value);
  _updateBulkBar();
}

function _toggleAll(masterCb) {
  document.querySelectorAll('.cat-check').forEach(cb => {
    cb.checked = masterCb.checked;
    if (masterCb.checked) _selectedIds.add(cb.value);
    else                  _selectedIds.delete(cb.value);
  });
  _updateBulkBar();
}

function _clearSelection() {
  _selectedIds.clear();
  document.querySelectorAll('.cat-check').forEach(cb => cb.checked = false);
  document.getElementById('select-all').checked = false;
  _updateBulkBar();
}

function _updateBulkBar() {
  const bar = document.getElementById('bulk-bar');
  if (_selectedIds.size > 0) {
    bar.style.display = 'flex';
    document.getElementById('bulk-count').textContent = `محدد: ${_selectedIds.size} عنصر`;
  } else {
    bar.style.display = 'none';
  }
}

function _openAddModal() {
  _editingCat = null;
  document.getElementById('cat-form-title').textContent = 'إضافة دواء جديد';
  ['cf-name-ar','cf-name-en','cf-manufacturer','cf-active-ingredient','cf-indications','cf-dosage','cf-warnings','cf-storage'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('cf-price').value    = '';
  document.getElementById('cf-route').value    = 'فموي';
  document.getElementById('cf-rx').checked     = false;
  _openModal('cat-form-modal');
}

function _editCat(id) {
  const c = mockCatalog.find(x => x.id === id);
  if (!c) return;
  _editingCat = id;
  document.getElementById('cat-form-title').textContent     = 'تعديل بيانات الدواء';
  document.getElementById('cf-name-ar').value               = c.nameAr;
  document.getElementById('cf-name-en').value               = c.nameEn;
  document.getElementById('cf-category').value              = c.category;
  document.getElementById('cf-manufacturer').value          = c.manufacturer;
  document.getElementById('cf-route').value                 = c.route || 'فموي';
  document.getElementById('cf-active-ingredient').value     = c.activeIngredient || '';
  document.getElementById('cf-price').value                 = c.price;
  document.getElementById('cf-rx').checked                  = c.rx;
  document.getElementById('cf-indications').value           = c.indicationsAndUsage || '';
  document.getElementById('cf-dosage').value                = c.dosageAndAdministration || '';
  document.getElementById('cf-warnings').value              = c.warnings || '';
  document.getElementById('cf-storage').value               = c.storageAndHandling || '';
  _openModal('cat-form-modal');
}

function _saveCatalog() {
  const nameAr                = document.getElementById('cf-name-ar').value.trim();
  const nameEn                = document.getElementById('cf-name-en').value.trim();
  const category              = document.getElementById('cf-category').value;
  const manufacturer          = document.getElementById('cf-manufacturer').value.trim();
  const route                 = document.getElementById('cf-route').value;
  const activeIngredient      = document.getElementById('cf-active-ingredient').value.trim();
  const price                 = parseFloat(document.getElementById('cf-price').value) || 0;
  const rx                    = document.getElementById('cf-rx').checked;
  const indicationsAndUsage   = document.getElementById('cf-indications').value.trim();
  const dosageAndAdministration = document.getElementById('cf-dosage').value.trim();
  const warnings              = document.getElementById('cf-warnings').value.trim();
  const storageAndHandling    = document.getElementById('cf-storage').value.trim();

  if (!nameAr || !nameEn || !price) { alert('يرجى ملء الحقول المطلوبة'); return; }

  const fields = { nameAr, nameEn, category, manufacturer, route, activeIngredient, price, rx,
                   indicationsAndUsage, dosageAndAdministration, warnings, storageAndHandling };

  if (_editingCat) {
    const c = mockCatalog.find(x => x.id === _editingCat);
    if (c) Object.assign(c, fields);
  } else {
    mockCatalog.unshift({ id:'c'+Date.now(), ...fields, stock:0, pharmacies:0 });
  }

  _editingCat = null;
  _closeModal('cat-form-modal');
  _renderCatalog();
}

let _deleteTargetCatId = null;
function _confirmDeleteCat(id) {
  const c = mockCatalog.find(x => x.id === id);
  if (!c) return;
  _deleteTargetCatId = id;
  document.getElementById('cat-delete-title').textContent = '⚠️ حذف الدواء';
  document.getElementById('cat-delete-msg').textContent   = `هل أنت متأكد من حذف "${c.nameAr}"؟`;
  document.getElementById('cat-delete-confirm-btn').onclick = _doDeleteCat;
  _openModal('cat-delete-modal');
}

function _confirmBulkDelete() {
  if (!_selectedIds.size) return;
  document.getElementById('cat-delete-title').textContent = '⚠️ حذف متعدد';
  document.getElementById('cat-delete-msg').textContent   = `هل أنت متأكد من حذف ${_selectedIds.size} دواء؟`;
  document.getElementById('cat-delete-confirm-btn').onclick = _doBulkDelete;
  _openModal('cat-delete-modal');
}

function _doDeleteCat() {
  const idx = mockCatalog.findIndex(x => x.id === _deleteTargetCatId);
  if (idx !== -1) mockCatalog.splice(idx, 1);
  _closeModal('cat-delete-modal');
  _deleteTargetCatId = null;
  _renderCatalog();
}

function _doBulkDelete() {
  _selectedIds.forEach(id => {
    const idx = mockCatalog.findIndex(x => x.id === id);
    if (idx !== -1) mockCatalog.splice(idx, 1);
  });
  _selectedIds.clear();
  _closeModal('cat-delete-modal');
  _renderCatalog();
}

const _csvHeaders = ['الاسم عربي','الاسم إنجليزي','التصنيف','الشركة','طريقة الإعطاء','المادة الفعّالة','السعر','وصفة طبية','دواعي الاستعمال','الجرعة','التحذيرات','التخزين','المخزون','عدد الصيدليات'];
const _csvRow = c => [c.nameAr, c.nameEn, c.category, c.manufacturer, c.route||'', c.activeIngredient||'', c.price, c.rx?'نعم':'لا', c.indicationsAndUsage||'', c.dosageAndAdministration||'', c.warnings||'', c.storageAndHandling||'', c.stock, c.pharmacies];

function _exportCatalog() {
  exportCsv('catalog.csv', _csvHeaders, _getFilteredCatalog().map(_csvRow));
}

function _exportSelected() {
  exportCsv('catalog_selected.csv', _csvHeaders, mockCatalog.filter(c => _selectedIds.has(c.id)).map(_csvRow));
}
