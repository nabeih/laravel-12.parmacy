// ── Pharmacist Sidebar ─────────────────────────────────────
const PHARM_NAV = [
  { href:'dashboard.html',  icon:'🏠', label:'الرئيسية' },
  { href:'inventory.html',  icon:'📦', label:'المخزون' },
  { href:'orders.html',     icon:'🛒', label:'الطلبات' },
  { href:'analytics.html',  icon:'📊', label:'التحليلات' },
  { href:'qr-sales.html',   icon:'🔲', label:'مبيعات QR' },
  { href:'chats.html',      icon:'💬', label:'المحادثات', badge:'chats' },
  { href:'profile.html',    icon:'👤', label:'الملف الشخصي' },
];

function renderPharmacistSidebar() {
  const current = window.location.pathname.split('/').pop() || 'dashboard.html';
  const user    = window.currentUser || {};

  const navHTML = PHARM_NAV.map(item => {
    const active  = item.href === current ? 'active' : '';
    const badgeEl = item.badge ? `<span class="nav-badge" id="sidebar-unread-${item.badge}">0</span>` : '';
    return `<a href="${item.href}" class="nav-link ${active}">
      <span class="nav-icon">${item.icon}</span>
      <span>${item.label}</span>
      ${badgeEl}
    </a>`;
  }).join('');

  const html = `
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-logo">
        <a href="dashboard.html" class="sidebar-logo-link">
          <span class="logo-emoji-s">💊</span>
          <div>
            <div class="logo-name-s">PharmacyLink</div>
            <div class="logo-sub-s">بوابة الصيدلاني</div>
          </div>
        </a>
      </div>
      <nav class="sidebar-nav">
        <div class="nav-section-label">القائمة الرئيسية</div>
        ${navHTML}
      </nav>
      <div class="sidebar-footer">
        <div class="sidebar-user">
          <div class="user-avatar">${(user.name || 'ص')[0]}</div>
          <div class="user-info">
            <div class="user-name-s">${user.name || 'الصيدلاني'}</div>
            <div class="user-email-s">${user.email || ''}</div>
          </div>
        </div>
        <button class="sidebar-logout" onclick="logoutUser()">
          🚪 تسجيل الخروج
        </button>
      </div>
    </aside>`;

  const container = document.getElementById('sidebar-container');
  if (container) container.innerHTML = html;

  _setupMobileToggle();
  _listenUnreadBadge();
}

function _setupMobileToggle() {
  const btn     = document.getElementById('hamburger-btn');
  const overlay = document.getElementById('sidebar-overlay');
  const sidebar = document.getElementById('sidebar');
  const open  = () => { sidebar?.classList.add('open');    overlay?.classList.add('active'); };
  const close = () => { sidebar?.classList.remove('open'); overlay?.classList.remove('active'); };
  btn?.addEventListener('click', open);
  overlay?.addEventListener('click', close);
  sidebar?.querySelectorAll('.nav-link').forEach(l => l.addEventListener('click', close));
}

function _listenUnreadBadge() {
  const badge = document.getElementById('sidebar-unread-chats');
  if (!badge) return;

  if (DEMO_MODE) {
    const pharmacyId = window.currentUser?.pharmacyId || 'pharm-001';
    const total = mockChats
      .filter(c => c.pharmacyId === pharmacyId)
      .reduce((s, c) => s + (c.unreadByPharmacist || 0), 0);
    badge.textContent = total;
    badge.style.display = total > 0 ? 'flex' : 'none';
    return;
  }

  const pharmacyId = window.currentUser?.uid;
  if (!pharmacyId) return;
  database.ref('chats').orderByChild('pharmacyId').equalTo(pharmacyId)
    .on('value', snap => {
      let total = 0;
      snap.forEach(child => { total += (child.val().unreadByPharmacist || 0); });
      badge.textContent = total;
      badge.style.display = total > 0 ? 'flex' : 'none';
    });
  window.addEventListener('beforeunload', () => {
    database.ref('chats').off();
  });
}
