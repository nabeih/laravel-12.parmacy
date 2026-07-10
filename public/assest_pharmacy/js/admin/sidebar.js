// ── Admin Sidebar ─────────────────────────────────────────────

const ADMIN_NAV = [
  { href:'dashboard.html',  icon:'📊', label:'لوحة التحكم'    },
  { href:'pharmacies.html', icon:'🏪', label:'الصيدليات'      },
  { href:'users.html',      icon:'👥', label:'المستخدمون'     },
  { href:'catalog.html',    icon:'💊', label:'كتالوج الأدوية' },
  { href:'orders.html',     icon:'📦', label:'الطلبات'        },
  { href:'reports.html',    icon:'📈', label:'التقارير'       },
];

function renderAdminSidebar() {
  const container = document.getElementById('admin-sidebar-container');
  if (!container) return;

  const currentPage = location.pathname.split('/').pop();
  const user        = window.currentUser || mockAdminUser;

  container.innerHTML = `
    <aside class="admin-sidebar">
      <div class="admin-logo">
        <div class="admin-logo-text">💊 PharmacyLink</div>
        <div class="admin-logo-sub">لوحة إدارة النظام</div>
      </div>
      <nav class="admin-nav">
        <div class="admin-nav-label">القائمة الرئيسية</div>
        ${ADMIN_NAV.map(item => `
          <a href="${item.href}" class="admin-nav-link${currentPage === item.href ? ' active' : ''}">
            <span>${item.icon}</span>
            <span>${item.label}</span>
          </a>`).join('')}
      </nav>
      <div class="admin-user-section">
        <div class="admin-user-name">${esc(user.name || 'مدير النظام')}</div>
        <div class="admin-user-email">${esc(user.email || '')}</div>
        <button class="admin-logout-btn" onclick="logoutAdmin()">🔓 تسجيل الخروج</button>
      </div>
    </aside>`;
}

function logoutAdmin() {
  if (typeof DEMO_MODE !== 'undefined' && DEMO_MODE) {
    window.location.href = '../index.html';
    return;
  }
  if (typeof auth !== 'undefined') {
    auth.signOut()
      .then(() => window.location.href = '../index.html')
      .catch(() => window.location.href = '../index.html');
  } else {
    window.location.href = '../index.html';
  }
}

function esc(str) {
  return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function _openModal(id)  { document.getElementById(id)?.classList.add('open'); }
function _closeModal(id) { document.getElementById(id)?.classList.remove('open'); }

function debounce(fn, ms) {
  let timer;
  return (...args) => { clearTimeout(timer); timer = setTimeout(() => fn(...args), ms); };
}

function fmtNum(n) {
  return Number(n || 0).toLocaleString('ar-EG', { minimumFractionDigits: 0 });
}
function fmtMoney(n) {
  return Number(n || 0).toLocaleString('ar-EG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function fmtDate(ts) {
  return new Date(ts).toLocaleDateString('ar-EG', { year:'numeric', month:'2-digit', day:'2-digit' });
}
function timeAgoAdmin(ts) {
  const diff = Date.now() - ts;
  const m = Math.floor(diff / 60000);
  if (m < 1)   return 'الآن';
  if (m < 60)  return `منذ ${m} دقيقة`;
  const h = Math.floor(m / 60);
  if (h < 24)  return `منذ ${h} ساعة`;
  const d = Math.floor(h / 24);
  if (d === 1) return 'أمس';
  if (d < 30)  return `منذ ${d} يوم`;
  return fmtDate(ts);
}

function buildPagination(containerId, total, page, pageSize, onChange) {
  const totalPages = Math.ceil(total / pageSize);
  const container  = document.getElementById(containerId);
  if (!container) return;
  if (totalPages <= 1) { container.innerHTML = ''; return; }

  let html = `<button class="admin-page-btn" ${page<=1?'disabled':''} onclick="(${onChange.toString()})(${page-1})">›</button>`;
  for (let i = 1; i <= totalPages; i++) {
    html += `<button class="admin-page-btn${i===page?' active':''}" onclick="(${onChange.toString()})(${i})">${i}</button>`;
  }
  html += `<button class="admin-page-btn" ${page>=totalPages?'disabled':''} onclick="(${onChange.toString()})(${page+1})">‹</button>`;
  container.innerHTML = html;
}

function exportCsv(filename, headers, rows) {
  const bom  = '﻿';
  const lines = [headers.join(','), ...rows.map(r => r.map(c => `"${String(c||'').replace(/"/g,'""')}"`).join(','))];
  const blob  = new Blob([bom + lines.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
  const url   = URL.createObjectURL(blob);
  const a     = document.createElement('a');
  a.href      = url; a.download = filename;
  document.body.appendChild(a); a.click();
  document.body.removeChild(a); URL.revokeObjectURL(url);
}

function pharmStatusBadge(status) {
  const map = {
    active:    '<span class="admin-badge admin-badge-green">نشط</span>',
    pending:   '<span class="admin-badge admin-badge-yellow">بانتظار الموافقة</span>',
    suspended: '<span class="admin-badge admin-badge-red">موقوف</span>',
  };
  return map[status] || `<span class="admin-badge admin-badge-gray">${esc(status)}</span>`;
}

function userStatusBadge(status) {
  return status === 'blocked'
    ? '<span class="admin-badge admin-badge-red">محظور</span>'
    : '<span class="admin-badge admin-badge-green">نشط</span>';
}

function orderStatusBadge(status) {
  const map = {
    pending:   '<span class="admin-badge admin-badge-yellow">قيد الانتظار</span>',
    confirmed: '<span class="admin-badge admin-badge-blue">مؤكد</span>',
    shipping:  '<span class="admin-badge admin-badge-purple">جارٍ التوصيل</span>',
    delivered: '<span class="admin-badge admin-badge-green">تم التسليم</span>',
    cancelled: '<span class="admin-badge admin-badge-red">ملغى</span>',
  };
  return map[status] || `<span class="admin-badge admin-badge-gray">${esc(status)}</span>`;
}

function paymentLabel(method) {
  return { cash:'نقداً', ussd:'USSD', bank:'بطاقة بنكية' }[method] || method;
}

// Close modals when clicking overlay
document.addEventListener('click', e => {
  if (e.target.classList.contains('admin-modal-overlay')) {
    e.target.classList.remove('open');
  }
});
