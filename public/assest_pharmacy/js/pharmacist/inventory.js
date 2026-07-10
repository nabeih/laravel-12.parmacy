// TODO: Laravel API — GET/POST/PUT/DELETE /api/pharmacist/products

const PAGE_SIZE = 10;
let _products = [], _filtered = [], _page = 1, _editId = null, _selectedCatalogItem = null;

async function initInventory() {
  await _loadProducts();
  _setupSearch();
  _setupModal();
}

async function _loadProducts() {
  // TODO: Laravel API GET /api/pharmacist/products
  if (!DEMO_MODE) {
    try {
      const uid  = window.currentUser?.uid;
      const snap = await db.collection('products').where('pharmacyId','==',uid).orderBy('nameAr').get();
      _products  = snap.docs.map(d => ({ id: d.id, ...d.data() }));
    } catch(e) {
      _products = [...mockProducts];
    }
  } else {
    _products = [...mockProducts];
  }
  _applyFilter();
}

function _applyFilter() {
  const q   = document.getElementById('inv-search')?.value.toLowerCase() || '';
  const cat = document.getElementById('inv-cat')?.value || '';
  _filtered = _products.filter(p => {
    const matchQ   = !q || p.nameAr?.toLowerCase().includes(q) || p.nameEn?.toLowerCase().includes(q) || p.category?.includes(q);
    const matchCat = !cat || p.category === cat;
    return matchQ && matchCat;
  });
  _page = 1;
  _render();
}

function _render() {
  const start  = (_page - 1) * PAGE_SIZE;
  const page   = _filtered.slice(start, start + PAGE_SIZE);
  const tbody  = document.getElementById('inv-tbody');
  const total  = _filtered.length;
  const pages  = Math.max(1, Math.ceil(total / PAGE_SIZE));

  document.getElementById('page-info').textContent =
    total === 0 ? 'لا توجد نتائج' : `عرض ${start+1}–${Math.min(start+PAGE_SIZE,total)} من ${total} منتج`;

  document.getElementById('btn-prev').disabled = _page <= 1;
  document.getElementById('btn-next').disabled = _page >= pages;

  if (!page.length) {
    tbody.innerHTML = `<tr><td colspan="9"><div class="empty-state"><span class="empty-icon">📦</span><p>لا توجد منتجات مطابقة</p></div></td></tr>`;
    return;
  }

  tbody.innerHTML = page.map((p, i) => `
    <tr>
      <td class="text-muted">${start + i + 1}</td>
      <td><strong>${esc(p.nameAr)}</strong></td>
      <td style="color:var(--text-muted);font-size:13px">${esc(p.nameEn)}</td>
      <td><span class="badge badge-outline">${esc(p.category)}</span></td>
      <td><strong>₪${fmt(p.price)}</strong></td>
      <td>${p.stock}</td>
      <td>${p.expiryDate ? fmtDate(p.expiryDate) : '—'}</td>
      <td>${stockBadge(p.stock, p.minStock)}</td>
      <td>
        <button class="btn btn-sm btn-outline" onclick="openEditModal('${p.id}')">تعديل المخزون</button>
      </td>
    </tr>`).join('');
}

function _setupSearch() {
  const search = document.getElementById('inv-search');
  const cat    = document.getElementById('inv-cat');
  search?.addEventListener('input', debounce(_applyFilter, 300));
  cat?.addEventListener('change', _applyFilter);
  document.getElementById('btn-prev')?.addEventListener('click', () => { _page--; _render(); });
  document.getElementById('btn-next')?.addEventListener('click', () => { _page++; _render(); });
}

function _setupModal() {
  const modal  = document.getElementById('product-modal');
  const close  = () => { modal.classList.remove('active'); _editId = null; _selectedCatalogItem = null; };
  document.getElementById('modal-close')?.addEventListener('click', close);
  document.getElementById('modal-cancel')?.addEventListener('click', close);
  modal?.addEventListener('click', e => { if (e.target === modal) close(); });
  document.getElementById('modal-save')?.addEventListener('click', saveProduct);

  const catModal  = document.getElementById('catalog-modal');
  const catClose  = () => catModal.classList.remove('active');
  document.getElementById('catalog-modal-close')?.addEventListener('click', catClose);
  catModal?.addEventListener('click', e => { if (e.target === catModal) catClose(); });
}

function openCatalogModal() {
  document.getElementById('catalog-search').value = '';
  _renderCatalogList('');
  document.getElementById('catalog-modal').classList.add('active');
}

function _renderCatalogList(q) {
  const search    = (q || '').toLowerCase();
  const addedKeys = new Set(_products.map(p => (p.catalogId || p.nameEn).toLowerCase()));
  const items     = mockCatalog.filter(c =>
    !search || c.nameAr.includes(search) || c.nameEn.toLowerCase().includes(search) || c.category.includes(search)
  );
  const list = document.getElementById('catalog-list');
  if (!items.length) {
    list.innerHTML = `<div style="text-align:center;padding:24px;color:var(--text-muted)">لا توجد نتائج</div>`;
    return;
  }
  list.innerHTML = items.map(c => {
    const added = addedKeys.has(c.id) || addedKeys.has(c.nameEn.toLowerCase());
    return `
      <div style="padding:12px;border:1px solid var(--border);border-radius:8px;margin-bottom:8px;background:${added ? 'var(--primary-50)' : 'white'}">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px">
          <div style="flex:1;min-width:0">
            <div style="font-weight:600">${esc(c.nameAr)} <span style="color:var(--text-muted);font-size:12px">${esc(c.nameEn)}</span></div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:2px">
              ${esc(c.category)} · ${esc(c.manufacturer)} · ${esc(c.route || '')}
              ${c.rx ? ' · <span style="background:#fef3c7;color:#92400e;padding:1px 6px;border-radius:4px;font-size:11px">وصفة طبية</span>' : ''}
            </div>
            ${c.activeIngredient ? `<div style="font-size:12px;color:#374151;margin-top:3px">💊 ${esc(c.activeIngredient)}</div>` : ''}
            ${c.indicationsAndUsage ? `<div style="font-size:12px;color:#374151;margin-top:2px">📋 ${esc(c.indicationsAndUsage)}</div>` : ''}
          </div>
          ${added
            ? `<span style="font-size:12px;color:var(--success);font-weight:600;white-space:nowrap;padding-top:2px">✓ مضاف</span>`
            : `<button class="btn btn-sm btn-primary" style="white-space:nowrap" onclick="_pickFromCatalog('${c.id}')">اختيار</button>`}
        </div>
      </div>`;
  }).join('');
}

function _pickFromCatalog(cid) {
  const c = mockCatalog.find(x => x.id === cid);
  if (!c) return;
  _selectedCatalogItem = c;
  _editId              = null;
  document.getElementById('catalog-modal').classList.remove('active');
  document.getElementById('modal-title').textContent = 'إضافة من الكتالوج';
  document.getElementById('modal-save').textContent  = 'إضافة للمخزون';
  document.getElementById('f-nameAr').value       = c.nameAr;
  document.getElementById('f-nameEn').value       = c.nameEn;
  document.getElementById('f-cat').value          = c.category;
  document.getElementById('f-price').value        = c.price;
  document.getElementById('f-expiry').value       = '';
  document.getElementById('f-stock').value        = '';
  document.getElementById('f-minstock').value     = '';
  document.getElementById('f-desc').value         = '';
  document.getElementById('modal-msg').textContent = '';
  _fillCatalogFields(c);
  document.getElementById('product-modal').classList.add('active');
}

function _fillCatalogFields(c) {
  document.getElementById('f-route').value            = c ? (c.route || '') : '';
  document.getElementById('f-active-ingredient').value = c ? (c.activeIngredient || '') : '';
  document.getElementById('f-indications').value      = c ? (c.indicationsAndUsage || '') : '';
  document.getElementById('f-dosage').value           = c ? (c.dosageAndAdministration || '') : '';
  document.getElementById('f-warnings').value         = c ? (c.warnings || '') : '';
  document.getElementById('f-storage').value          = c ? (c.storageAndHandling || '') : '';
}

function openEditModal(id) {
  const p = _products.find(x => x.id === id);
  if (!p) return;
  _editId              = id;
  _selectedCatalogItem = null;
  document.getElementById('modal-title').textContent  = 'تعديل المنتج';
  document.getElementById('modal-save').textContent   = 'حفظ التعديلات';
  document.getElementById('f-nameAr').value           = p.nameAr      || '';
  document.getElementById('f-nameEn').value           = p.nameEn      || '';
  document.getElementById('f-cat').value              = p.category    || '';
  document.getElementById('f-price').value            = p.price       || '';
  document.getElementById('f-expiry').value           = p.expiryDate  || '';
  document.getElementById('f-stock').value            = p.stock       ?? '';
  document.getElementById('f-minstock').value         = p.minStock    ?? '';
  document.getElementById('f-desc').value             = p.description || '';
  document.getElementById('modal-msg').textContent    = '';
  const cat = mockCatalog.find(c => c.id === p.catalogId || c.nameEn.toLowerCase() === (p.nameEn || '').toLowerCase());
  _fillCatalogFields(cat || null);
  document.getElementById('product-modal').classList.add('active');
}

function _clearForm() {
  ['f-nameAr','f-nameEn','f-cat','f-price','f-expiry','f-stock','f-minstock','f-desc','modal-msg']
    .forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
  document.getElementById('modal-msg').textContent = '';
}

async function saveProduct() {
  const isNew = !_editId && _selectedCatalogItem !== null;
  if (!isNew && !_editId) return;

  const msg      = document.getElementById('modal-msg');
  const btn      = document.getElementById('modal-save');
  const price    = parseFloat(document.getElementById('f-price').value);
  const stock    = parseInt(document.getElementById('f-stock').value);
  const minStock = parseInt(document.getElementById('f-minstock').value);
  const expiry   = document.getElementById('f-expiry').value;
  const desc     = document.getElementById('f-desc').value.trim();

  if (isNaN(price) || price < 0) {
    msg.textContent = 'يرجى إدخال سعر صحيح.'; msg.style.color = 'var(--danger)'; return;
  }
  if (isNaN(stock) || isNaN(minStock)) {
    msg.textContent = 'يرجى ملء حقول المخزون.'; msg.style.color = 'var(--danger)'; return;
  }

  btn.disabled = true; btn.textContent = 'جاري الحفظ...';

  if (isNew) {
    const newProduct = {
      id:          'p' + Date.now(),
      nameAr:      _selectedCatalogItem.nameAr,
      nameEn:      _selectedCatalogItem.nameEn,
      category:    _selectedCatalogItem.category,
      catalogId:   _selectedCatalogItem.id,
      price, stock, minStock,
      expiryDate:  expiry || null,
      description: desc,
    };
    // TODO: Laravel API POST /api/pharmacist/products
    if (DEMO_MODE) {
      _products.push(newProduct);
      msg.textContent = 'تمت الإضافة بنجاح.'; msg.style.color = 'var(--success)';
      setTimeout(() => {
        document.getElementById('product-modal').classList.remove('active');
        _selectedCatalogItem = null;
        _applyFilter();
      }, 800);
    } else {
      try {
        const uid = window.currentUser?.uid;
        await db.collection('products').add({ ...newProduct, pharmacyId: uid });
        msg.textContent = 'تمت الإضافة بنجاح.'; msg.style.color = 'var(--success)';
        setTimeout(() => {
          document.getElementById('product-modal').classList.remove('active');
          _selectedCatalogItem = null;
          _loadProducts();
        }, 800);
      } catch(e) {
        msg.textContent = 'فشلت الإضافة: ' + e.message; msg.style.color = 'var(--danger)';
      }
    }
  } else {
    const updates = { price, stock, minStock, expiryDate: expiry || null, description: desc };
    // TODO: Laravel API PUT /api/pharmacist/products/{id}
    if (DEMO_MODE) {
      const idx = _products.findIndex(x => x.id === _editId);
      if (idx !== -1) _products[idx] = { ..._products[idx], ...updates };
      msg.textContent = 'تم تحديث المنتج.'; msg.style.color = 'var(--success)';
      setTimeout(() => { document.getElementById('product-modal').classList.remove('active'); _editId = null; _applyFilter(); }, 800);
    } else {
      try {
        await db.collection('products').doc(_editId).update(updates);
        msg.textContent = 'تم الحفظ بنجاح.'; msg.style.color = 'var(--success)';
        setTimeout(() => { document.getElementById('product-modal').classList.remove('active'); _editId = null; _loadProducts(); }, 800);
      } catch(e) {
        msg.textContent = 'فشل الحفظ: ' + e.message; msg.style.color = 'var(--danger)';
      }
    }
  }

  btn.disabled = false; btn.textContent = _editId ? 'حفظ التعديلات' : 'إضافة للمخزون';
}
