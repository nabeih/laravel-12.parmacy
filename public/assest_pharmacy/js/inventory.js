// ── Inventory Page ────────────────────────────────────────
let allProducts   = [];
let filteredProducts = [];
let currentPage   = 1;
const ITEMS_PER_PAGE = 10;
let editingId = null;   // null = add mode, string = edit mode

async function initInventory() {
  await loadProducts();
  setupInventoryEvents();
}

// ── Load ──────────────────────────────────────────────────
async function loadProducts() {
  showTableLoading();
  try {
    if (!DEMO_MODE) {
      const snap = await db.collection('products').orderBy('nameEn').get();
      allProducts = snap.docs.map(d => ({ id: d.id, ...d.data() }));
      if (!allProducts.length) allProducts = mockProducts;
    } else {
      allProducts = [...mockProducts];
    }
  } catch (e) {
    showTableError('Failed to load products: ' + e.message);
    allProducts = [...mockProducts];
  }
  applyFilters();
}

// ── Filtering & Pagination ────────────────────────────────
function applyFilters() {
  const search = document.getElementById('inv-search').value.toLowerCase().trim();
  const cat    = document.getElementById('inv-category').value;

  filteredProducts = allProducts.filter(p => {
    const matchSearch = !search ||
      p.nameEn.toLowerCase().includes(search) ||
      p.nameAr.includes(search) ||
      p.category.toLowerCase().includes(search);
    const matchCat = !cat || p.category === cat;
    return matchSearch && matchCat;
  });

  currentPage = 1;
  renderTable();
}

function renderTable() {
  const tbody  = document.getElementById('inv-tbody');
  const total  = filteredProducts.length;
  const start  = (currentPage - 1) * ITEMS_PER_PAGE;
  const end    = Math.min(start + ITEMS_PER_PAGE, total);
  const rows   = filteredProducts.slice(start, end);

  if (!rows.length) {
    tbody.innerHTML = `<tr><td colspan="9"><div class="empty-state"><span class="empty-icon">📦</span><p>No products found</p></div></td></tr>`;
    updatePagination(0, 0, 0);
    return;
  }

  tbody.innerHTML = rows.map((p, i) => `
    <tr>
      <td class="text-muted">${start + i + 1}</td>
      <td dir="rtl" style="font-size:14px">${p.nameAr}</td>
      <td>${p.nameEn}</td>
      <td><span class="badge badge-secondary">${p.category}</span></td>
      <td><strong>₪${fmt(p.price)}</strong></td>
      <td>${p.stock}</td>
      <td>${p.expiryDate || '—'}</td>
      <td>${stockBadge(p.stock, p.minStock)}</td>
      <td>
        <div style="display:flex;gap:6px">
          <button class="btn btn-sm btn-outline" onclick="openEditModal('${p.id}')">Edit</button>
          <button class="btn btn-sm btn-outline-danger" onclick="deleteProduct('${p.id}')">Delete</button>
        </div>
      </td>
    </tr>`).join('');

  updatePagination(start + 1, end, total);
}

function updatePagination(start, end, total) {
  document.getElementById('page-info').textContent   = total ? `${start}–${end} of ${total} products` : '0 products';
  document.getElementById('btn-prev').disabled = currentPage <= 1;
  document.getElementById('btn-next').disabled = end >= total;
}

// ── Events ────────────────────────────────────────────────
function setupInventoryEvents() {
  document.getElementById('inv-search').addEventListener('input',  applyFilters);
  document.getElementById('inv-category').addEventListener('change', applyFilters);
  document.getElementById('btn-prev').addEventListener('click', () => { currentPage--; renderTable(); });
  document.getElementById('btn-next').addEventListener('click', () => { currentPage++; renderTable(); });
  document.getElementById('btn-add-product').addEventListener('click', openAddModal);

  // Modal close buttons
  document.getElementById('product-modal-overlay').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeProductModal();
  });
  document.getElementById('modal-close-btn').addEventListener('click',   closeProductModal);
  document.getElementById('modal-cancel-btn').addEventListener('click',  closeProductModal);
  document.getElementById('product-form').addEventListener('submit',     saveProduct);
}

// ── Add Modal ─────────────────────────────────────────────
function openAddModal() {
  editingId = null;
  document.getElementById('modal-title').textContent = 'Add New Product';
  document.getElementById('modal-save-btn').textContent = 'Add Product';
  document.getElementById('product-form').reset();
  clearFormErrors();
  clearModalMessage();
  openProductModal();
}

// ── Edit Modal ────────────────────────────────────────────
function openEditModal(id) {
  const p = allProducts.find(x => x.id === id);
  if (!p) return;
  editingId = id;

  document.getElementById('modal-title').textContent      = 'Edit Product';
  document.getElementById('modal-save-btn').textContent   = 'Save Changes';
  clearFormErrors();
  clearModalMessage();

  document.getElementById('f-nameAr').value     = p.nameAr     || '';
  document.getElementById('f-nameEn').value     = p.nameEn     || '';
  document.getElementById('f-category').value   = p.category   || '';
  document.getElementById('f-price').value      = p.price      || '';
  document.getElementById('f-stock').value      = p.stock      ?? '';
  document.getElementById('f-minStock').value   = p.minStock   || '';
  document.getElementById('f-expiry').value     = p.expiryDate || '';
  document.getElementById('f-desc').value       = p.description|| '';

  openProductModal();
}

function openProductModal()  { document.getElementById('product-modal-overlay').classList.add('active'); }
function closeProductModal() { document.getElementById('product-modal-overlay').classList.remove('active'); }

// ── Save (Add / Edit) ─────────────────────────────────────
async function saveProduct(e) {
  e.preventDefault();
  if (!validateProductForm()) return;

  const data = {
    nameAr:      document.getElementById('f-nameAr').value.trim(),
    nameEn:      document.getElementById('f-nameEn').value.trim(),
    category:    document.getElementById('f-category').value,
    price:       parseFloat(document.getElementById('f-price').value),
    stock:       parseInt(document.getElementById('f-stock').value, 10),
    minStock:    parseInt(document.getElementById('f-minStock').value, 10),
    expiryDate:  document.getElementById('f-expiry').value,
    description: document.getElementById('f-desc').value.trim(),
  };

  const btn = document.getElementById('modal-save-btn');
  btn.disabled    = true;
  btn.textContent = 'Saving…';

  try {
    if (!DEMO_MODE) {
      if (editingId) {
        await db.collection('products').doc(editingId).update(data);
      } else {
        const docRef = await db.collection('products').add(data);
        data.id = docRef.id;
      }
    } else {
      await new Promise(r => setTimeout(r, 500)); // simulate save
      data.id = editingId || 'mp' + Date.now();
    }

    // Update in-memory list
    if (editingId) {
      const idx = allProducts.findIndex(x => x.id === editingId);
      if (idx > -1) allProducts[idx] = { ...allProducts[idx], ...data };
    } else {
      allProducts.unshift(data);
    }

    applyFilters();
    showModalMessage('Product saved successfully!', 'success');
    setTimeout(closeProductModal, 1000);
  } catch (err) {
    showModalMessage('Error: ' + err.message, 'error');
  } finally {
    btn.disabled    = false;
    btn.textContent = editingId ? 'Save Changes' : 'Add Product';
  }
}

// ── Delete ────────────────────────────────────────────────
async function deleteProduct(id) {
  const p = allProducts.find(x => x.id === id);
  if (!p) return;
  if (!confirm(`Delete "${p.nameEn}" from inventory?\nThis action cannot be undone.`)) return;

  try {
    if (!DEMO_MODE) {
      await db.collection('products').doc(id).delete();
    } else {
      await new Promise(r => setTimeout(r, 300));
    }
    allProducts = allProducts.filter(x => x.id !== id);
    applyFilters();
  } catch (err) {
    alert('Error deleting product: ' + err.message);
  }
}

// ── Form Validation ───────────────────────────────────────
function validateProductForm() {
  clearFormErrors();
  let valid = true;

  const required = [
    { id: 'f-nameAr',   msg: 'Arabic name is required' },
    { id: 'f-nameEn',   msg: 'English name is required' },
    { id: 'f-category', msg: 'Category is required' },
  ];

  required.forEach(({ id, msg }) => {
    const el = document.getElementById(id);
    if (!el.value.trim()) { setFieldError(el, msg); valid = false; }
  });

  const priceEl = document.getElementById('f-price');
  if (!priceEl.value || parseFloat(priceEl.value) <= 0) {
    setFieldError(priceEl, 'Price must be greater than 0');
    valid = false;
  }

  const stockEl = document.getElementById('f-stock');
  if (stockEl.value === '' || parseInt(stockEl.value,10) < 0) {
    setFieldError(stockEl, 'Stock must be 0 or more');
    valid = false;
  }

  const minEl = document.getElementById('f-minStock');
  if (!minEl.value || parseInt(minEl.value,10) <= 0) {
    setFieldError(minEl, 'Min stock must be greater than 0');
    valid = false;
  }

  return valid;
}

function setFieldError(el, msg) {
  el.classList.add('error');
  const span = document.createElement('span');
  span.className = 'inline-error';
  span.textContent = msg;
  el.parentNode.appendChild(span);
}

function clearFormErrors() {
  document.querySelectorAll('#product-form .error').forEach(el => el.classList.remove('error'));
  document.querySelectorAll('#product-form .inline-error').forEach(el => el.remove());
}

function showModalMessage(msg, type) {
  const el = document.getElementById('modal-msg');
  el.textContent  = msg;
  el.className    = type === 'success' ? 'success-msg' : 'error-msg';
}
function clearModalMessage() {
  const el = document.getElementById('modal-msg');
  if (el) { el.textContent = ''; el.className = ''; }
}

// ── Loading States ────────────────────────────────────────
function showTableLoading() {
  document.getElementById('inv-tbody').innerHTML =
    '<tr><td colspan="9"><div class="loading-state"><div class="spinner"></div><p>Loading products…</p></div></td></tr>';
}
function showTableError(msg) {
  document.getElementById('inv-tbody').innerHTML =
    `<tr><td colspan="9"><div class="empty-state"><span class="empty-icon">⚠️</span><p class="text-danger">${msg}</p></div></td></tr>`;
}
