// ── Admin Pharmacies ──────────────────────────────────────────

const PAGE_SIZE = 10;
let _pharmPage    = 1;
let _pharmSearch  = '';
let _pharmStatus  = '';
let _editingPharm = null;

function initAdminPharmacies() {
  document.getElementById('pharm-search').addEventListener('input',
    debounce(() => { _pharmSearch = document.getElementById('pharm-search').value.trim(); _pharmPage = 1; _renderTable(); }, 300));
  _renderTable();
}

function _onSearch() {
  _pharmSearch = document.getElementById('pharm-search').value.trim();
  _pharmPage   = 1;
  _renderTable();
}

function _onFilter() {
  _pharmStatus = document.getElementById('pharm-status-filter').value;
  _pharmPage   = 1;
  _renderTable();
}

function _getFiltered() {
  return mockAllPharmacies.filter(p => {
    const matchSearch = !_pharmSearch ||
      p.name.includes(_pharmSearch) || p.owner.includes(_pharmSearch) || p.city.includes(_pharmSearch);
    const matchStatus = !_pharmStatus || p.status === _pharmStatus;
    return matchSearch && matchStatus;
  });
}

function _renderTable() {
  const filtered = _getFiltered();
  const start    = (_pharmPage - 1) * PAGE_SIZE;
  const page     = filtered.slice(start, start + PAGE_SIZE);
  const tbody    = document.getElementById('pharm-tbody');

  if (!page.length) {
    tbody.innerHTML = `<tr><td colspan="8" class="admin-empty" style="padding:30px">لا توجد نتائج</td></tr>`;
    document.getElementById('pharm-pagination').innerHTML = '';
    return;
  }

  tbody.innerHTML = page.map(p => `
    <tr style="${p.status === 'pending' ? 'background:#fffbeb' : ''}">
      <td><strong>${esc(p.name)}</strong><br><small style="color:#64748b">${esc(p.license)}</small></td>
      <td>${esc(p.owner)}</td>
      <td>${esc(p.city)}</td>
      <td>${fmtNum(p.orders)}</td>
      <td>₪${fmtMoney(p.revenue)}</td>
      <td>${pharmStatusBadge(p.status)}</td>
      <td>${fmtDate(p.joinedAt)}</td>
      <td>
        <div style="display:flex;gap:4px;flex-wrap:wrap">
          ${p.status === 'pending' ? `<button class="admin-btn admin-btn-success admin-btn-sm" onclick="_approvePharm('${p.id}')">موافقة</button>` : ''}
          ${p.status === 'active'  ? `<button class="admin-btn admin-btn-outline admin-btn-sm" style="color:#f97316;border-color:#f97316" onclick="_suspendPharm('${p.id}')">إيقاف</button>` : ''}
          ${p.status === 'suspended' ? `<button class="admin-btn admin-btn-success admin-btn-sm" onclick="_approvePharm('${p.id}')">تفعيل</button>` : ''}
          <button class="admin-btn admin-btn-outline admin-btn-sm" onclick="_viewPharm('${p.id}')">عرض</button>
          <button class="admin-btn admin-btn-outline admin-btn-sm" onclick="_editPharm('${p.id}')">تعديل</button>
          <button class="admin-btn admin-btn-danger admin-btn-sm" onclick="_confirmDelete('${p.id}')">حذف</button>
        </div>
      </td>
    </tr>`).join('');

  // Pagination
  const total      = filtered.length;
  const totalPages = Math.ceil(total / PAGE_SIZE);
  const pg         = document.getElementById('pharm-pagination');

  if (totalPages <= 1) { pg.innerHTML = ''; return; }
  let html = `<span style="font-size:13px;color:#64748b;margin-left:8px">إجمالي: ${total}</span>`;
  html += `<button class="admin-page-btn" ${_pharmPage<=1?'disabled':''} onclick="_goPage(${_pharmPage-1})">›</button>`;
  for (let i = 1; i <= totalPages; i++) {
    html += `<button class="admin-page-btn${i===_pharmPage?' active':''}" onclick="_goPage(${i})">${i}</button>`;
  }
  html += `<button class="admin-page-btn" ${_pharmPage>=totalPages?'disabled':''} onclick="_goPage(${_pharmPage+1})">‹</button>`;
  pg.innerHTML = html;
}

function _goPage(p) { _pharmPage = p; _renderTable(); }

function _viewPharm(id) {
  const p = mockAllPharmacies.find(x => x.id === id);
  if (!p) return;
  document.getElementById('pharm-view-body').innerHTML = `
    <div class="admin-info-row"><span class="admin-info-label">اسم الصيدلية</span><span class="admin-info-value">${esc(p.name)}</span></div>
    <div class="admin-info-row"><span class="admin-info-label">صاحب الترخيص</span><span class="admin-info-value">${esc(p.owner)}</span></div>
    <div class="admin-info-row"><span class="admin-info-label">المدينة</span><span class="admin-info-value">${esc(p.city)}</span></div>
    <div class="admin-info-row"><span class="admin-info-label">رقم الهاتف</span><span class="admin-info-value" dir="ltr">${esc(p.phone)}</span></div>
    <div class="admin-info-row"><span class="admin-info-label">رقم الترخيص</span><span class="admin-info-value" dir="ltr">${esc(p.license)}</span></div>
    <div class="admin-info-row"><span class="admin-info-label">الحالة</span><span class="admin-info-value">${pharmStatusBadge(p.status)}</span></div>
    <div class="admin-info-row"><span class="admin-info-label">إجمالي الطلبات</span><span class="admin-info-value">${fmtNum(p.orders)}</span></div>
    <div class="admin-info-row"><span class="admin-info-label">إجمالي الإيرادات</span><span class="admin-info-value">₪${fmtMoney(p.revenue)}</span></div>
    <div class="admin-info-row"><span class="admin-info-label">تاريخ الانضمام</span><span class="admin-info-value">${fmtDate(p.joinedAt)}</span></div>`;
  _openModal('pharm-view-modal');
}

function _editPharm(id) {
  const p = mockAllPharmacies.find(x => x.id === id);
  if (!p) return;
  _editingPharm = id;
  document.getElementById('pharm-form-title').textContent = 'تعديل بيانات الصيدلية';
  document.getElementById('pf-name').value    = p.name;
  document.getElementById('pf-owner').value   = p.owner;
  document.getElementById('pf-city').value    = p.city;
  document.getElementById('pf-phone').value   = p.phone;
  document.getElementById('pf-license').value = p.license;
  document.getElementById('pf-status').value  = p.status;
  _openModal('pharm-form-modal');
}

function _savePharm() {
  const name    = document.getElementById('pf-name').value.trim();
  const owner   = document.getElementById('pf-owner').value.trim();
  const city    = document.getElementById('pf-city').value;
  const phone   = document.getElementById('pf-phone').value.trim();
  const license = document.getElementById('pf-license').value.trim();
  const status  = document.getElementById('pf-status').value;

  if (!name || !owner || !phone) {
    alert('يرجى ملء الحقول المطلوبة');
    return;
  }

  if (_editingPharm) {
    const p = mockAllPharmacies.find(x => x.id === _editingPharm);
    if (p) Object.assign(p, { name, owner, city, phone, license, status });
  } else {
    mockAllPharmacies.unshift({
      id: 'ph' + Date.now(), name, owner, city, phone, license, status,
      orders: 0, revenue: 0, joinedAt: Date.now()
    });
  }

  _editingPharm = null;
  document.getElementById('pharm-form-title').textContent = 'إضافة صيدلية جديدة';
  ['pf-name','pf-owner','pf-phone','pf-license'].forEach(id => document.getElementById(id).value = '');
  _closeModal('pharm-form-modal');
  _renderTable();
}

function _approvePharm(id) {
  const p = mockAllPharmacies.find(x => x.id === id);
  if (p) p.status = 'active';
  _renderTable();
}

function _suspendPharm(id) {
  const p = mockAllPharmacies.find(x => x.id === id);
  if (p) p.status = 'suspended';
  _renderTable();
}

let _deleteTargetId = null;
function _confirmDelete(id) {
  const p = mockAllPharmacies.find(x => x.id === id);
  if (!p) return;
  _deleteTargetId = id;
  document.getElementById('pharm-delete-name').textContent = p.name;
  document.getElementById('pharm-delete-confirm-btn').onclick = _doDelete;
  _openModal('pharm-delete-modal');
}

function _doDelete() {
  const idx = mockAllPharmacies.findIndex(x => x.id === _deleteTargetId);
  if (idx !== -1) mockAllPharmacies.splice(idx, 1);
  _closeModal('pharm-delete-modal');
  _deleteTargetId = null;
  _renderTable();
}

function _exportPharm() {
  const rows = _getFiltered().map(p => [
    p.name, p.owner, p.city, p.phone, p.license,
    p.status === 'active' ? 'نشط' : p.status === 'pending' ? 'بانتظار الموافقة' : 'موقوف',
    p.orders, p.revenue, fmtDate(p.joinedAt)
  ]);
  exportCsv('pharmacies.csv',
    ['الصيدلية','المالك','المدينة','الهاتف','الترخيص','الحالة','الطلبات','الإيرادات','تاريخ الانضمام'],
    rows);
}
