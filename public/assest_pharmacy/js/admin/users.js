// ── Admin Users ───────────────────────────────────────────────

const USER_PAGE_SIZE = 10;
let _userPage   = 1;
let _userSearch = '';
let _userStatus = '';
let _userCity   = '';
let _deleteUserId = null;

function initAdminUsers() {
  _renderUserStats();
  _renderUserTable();
}

function _renderUserStats() {
  const total   = mockAllUsers.length;
  const active  = mockAllUsers.filter(u => u.status === 'active').length;
  const blocked = mockAllUsers.filter(u => u.status === 'blocked').length;
  const today   = new Date().toDateString();
  const newToday= mockAllUsers.filter(u => new Date(u.joinedAt).toDateString() === today).length;

  document.getElementById('user-stats').innerHTML = `
    <div class="admin-stat-card blue">
      <div><div class="admin-stat-value">${total}</div><div class="admin-stat-label">إجمالي المستخدمين</div></div>
      <div class="admin-stat-icon">👥</div>
    </div>
    <div class="admin-stat-card green">
      <div><div class="admin-stat-value">${active}</div><div class="admin-stat-label">مستخدمون نشطون</div></div>
      <div class="admin-stat-icon">✅</div>
    </div>
    <div class="admin-stat-card red">
      <div><div class="admin-stat-value">${blocked}</div><div class="admin-stat-label">محظورون</div></div>
      <div class="admin-stat-icon">🚫</div>
    </div>
    <div class="admin-stat-card orange">
      <div><div class="admin-stat-value">${newToday}</div><div class="admin-stat-label">جدد اليوم</div></div>
      <div class="admin-stat-icon">🆕</div>
    </div>`;
}

function _onUserSearch() {
  _userSearch = document.getElementById('user-search').value.trim();
  _userPage   = 1;
  _renderUserTable();
}

function _onUserFilter() {
  _userStatus = document.getElementById('user-status-filter').value;
  _userCity   = document.getElementById('user-city-filter').value;
  _userPage   = 1;
  _renderUserTable();
}

function _getFilteredUsers() {
  return mockAllUsers.filter(u => {
    const s = _userSearch.toLowerCase();
    const matchSearch = !s || u.name.includes(s) || u.email.toLowerCase().includes(s) || (u.phone||'').includes(s);
    const matchStatus = !_userStatus || u.status === _userStatus;
    const matchCity   = !_userCity   || u.city === _userCity;
    return matchSearch && matchStatus && matchCity;
  });
}

function _renderUserTable() {
  const filtered = _getFilteredUsers();
  const start    = (_userPage - 1) * USER_PAGE_SIZE;
  const page     = filtered.slice(start, start + USER_PAGE_SIZE);
  const tbody    = document.getElementById('user-tbody');

  if (!page.length) {
    tbody.innerHTML = `<tr><td colspan="8" class="admin-empty" style="padding:30px">لا توجد نتائج</td></tr>`;
    document.getElementById('user-pagination').innerHTML = '';
    return;
  }

  tbody.innerHTML = page.map(u => `
    <tr>
      <td>
        <div style="font-weight:600">${esc(u.name)}</div>
        <div style="font-size:12px;color:#64748b" dir="ltr">${esc(u.email)}</div>
      </td>
      <td dir="ltr">${esc(u.phone || '—')}</td>
      <td>${esc(u.city)}</td>
      <td>${fmtNum(u.orders)}</td>
      <td>${fmtDate(u.joinedAt)}</td>
      <td>${timeAgoAdmin(u.lastLogin)}</td>
      <td>${userStatusBadge(u.status)}</td>
      <td>
        <div style="display:flex;gap:4px">
          <button class="admin-btn admin-btn-outline admin-btn-sm" onclick="_viewUser('${u.id}')">عرض</button>
          <button class="admin-btn admin-btn-outline admin-btn-sm" style="${u.status==='blocked'?'color:#22c55e;border-color:#22c55e':'color:#f97316;border-color:#f97316'}"
                  onclick="_toggleBlock('${u.id}')">${u.status === 'blocked' ? 'رفع الحظر' : 'حظر'}</button>
          <button class="admin-btn admin-btn-danger admin-btn-sm" onclick="_confirmDeleteUser('${u.id}')">حذف</button>
        </div>
      </td>
    </tr>`).join('');

  // Pagination
  const total      = filtered.length;
  const totalPages = Math.ceil(total / USER_PAGE_SIZE);
  const pg         = document.getElementById('user-pagination');
  if (totalPages <= 1) { pg.innerHTML = ''; return; }

  let html = `<span style="font-size:13px;color:#64748b;margin-left:8px">إجمالي: ${total}</span>`;
  html += `<button class="admin-page-btn" ${_userPage<=1?'disabled':''} onclick="_userGoPage(${_userPage-1})">›</button>`;
  for (let i = 1; i <= totalPages; i++) {
    html += `<button class="admin-page-btn${i===_userPage?' active':''}" onclick="_userGoPage(${i})">${i}</button>`;
  }
  html += `<button class="admin-page-btn" ${_userPage>=totalPages?'disabled':''} onclick="_userGoPage(${_userPage+1})">‹</button>`;
  pg.innerHTML = html;
}

function _userGoPage(p) { _userPage = p; _renderUserTable(); }

function _viewUser(id) {
  const u = mockAllUsers.find(x => x.id === id);
  if (!u) return;
  document.getElementById('user-view-body').innerHTML = `
    <div class="admin-info-row"><span class="admin-info-label">الاسم</span><span class="admin-info-value">${esc(u.name)}</span></div>
    <div class="admin-info-row"><span class="admin-info-label">البريد الإلكتروني</span><span class="admin-info-value" dir="ltr">${esc(u.email)}</span></div>
    <div class="admin-info-row"><span class="admin-info-label">رقم الهاتف</span><span class="admin-info-value" dir="ltr">${esc(u.phone || '—')}</span></div>
    <div class="admin-info-row"><span class="admin-info-label">المدينة</span><span class="admin-info-value">${esc(u.city)}</span></div>
    <div class="admin-info-row"><span class="admin-info-label">إجمالي الطلبات</span><span class="admin-info-value">${fmtNum(u.orders)}</span></div>
    <div class="admin-info-row"><span class="admin-info-label">تاريخ الانضمام</span><span class="admin-info-value">${fmtDate(u.joinedAt)}</span></div>
    <div class="admin-info-row"><span class="admin-info-label">آخر نشاط</span><span class="admin-info-value">${timeAgoAdmin(u.lastLogin)}</span></div>
    <div class="admin-info-row"><span class="admin-info-label">الحالة</span><span class="admin-info-value">${userStatusBadge(u.status)}</span></div>`;
  _openModal('user-view-modal');
}

function _toggleBlock(id) {
  const u = mockAllUsers.find(x => x.id === id);
  if (!u) return;
  u.status = u.status === 'blocked' ? 'active' : 'blocked';
  _renderUserTable();
  _renderUserStats();
}

function _confirmDeleteUser(id) {
  const u = mockAllUsers.find(x => x.id === id);
  if (!u) return;
  _deleteUserId = id;
  document.getElementById('user-delete-name').textContent = u.name;
  document.getElementById('user-delete-confirm-input').value = '';
  document.getElementById('user-delete-btn').disabled = true;
  _openModal('user-delete-modal');
}

function _checkDeleteInput() {
  const val = document.getElementById('user-delete-confirm-input').value;
  document.getElementById('user-delete-btn').disabled = val !== 'CONFIRM';
}

function _doDeleteUser() {
  const idx = mockAllUsers.findIndex(x => x.id === _deleteUserId);
  if (idx !== -1) mockAllUsers.splice(idx, 1);
  _closeModal('user-delete-modal');
  _deleteUserId = null;
  _renderUserTable();
  _renderUserStats();
}

function _exportUsers() {
  const rows = _getFilteredUsers().map(u => [
    u.name, u.email, u.phone || '', u.city,
    u.orders, fmtDate(u.joinedAt), fmtDate(u.lastLogin),
    u.status === 'active' ? 'نشط' : 'محظور'
  ]);
  exportCsv('users.csv',
    ['الاسم','البريد','الهاتف','المدينة','الطلبات','تاريخ الانضمام','آخر نشاط','الحالة'],
    rows);
}
