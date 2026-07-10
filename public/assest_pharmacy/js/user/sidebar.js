// ── User Sidebar ─────────────────────────────────────
const USER_NAV = [
  { href:'dashboard.html',       icon:'🏠', label:'الرئيسية' },
  { href:'orders.html',          icon:'📦', label:'طلباتي' },
  { href:'pharmacies.html',      icon:'🏪', label:'الصيدليات' },
  { href:'new-medicines.html',   icon:'🆕', label:'أدوية جديدة' },
  { href:'doses.html',           icon:'💊', label:'جرعاتي' },
  { href:'favorites.html',       icon:'❤️', label:'المفضلة' },
  { href:'addresses.html',       icon:'📍', label:'عناويني' },
  { href:'scanner.html',         icon:'🔍', label:'مسح الروشتة', badge:null },
  { href:'chatbot.html',         icon:'🤖', label:'مساعد AI' },
  { href:'chats.html',           icon:'💬', label:'المحادثات', badge:'chats' },
  { href:'notifications.html',   icon:'🔔', label:'الإشعارات',  badge:'notif' },
  { href:'profile.html',         icon:'👤', label:'الملف الشخصي' },
];

function renderUserSidebar() {
  const current = window.location.pathname.split('/').pop() || 'dashboard.html';
  const user    = window.currentUser || {};

  const navHTML = USER_NAV.map(item => {
    const active = item.href === current ? 'active' : '';
    let badgeEl  = '';
    if (item.badge === 'notif') {
      // Notification badge — uses id="notif-badge" so updateNotifBadge() can find it
      badgeEl = `<span id="notif-badge"
        style="display:none;background:#ef4444;color:white;border-radius:50%;min-width:18px;height:18px;font-size:10px;font-weight:700;align-items:center;justify-content:center;padding:0 3px;margin-right:auto">
        0</span>`;
    } else if (item.badge) {
      badgeEl = `<span class="nav-badge" id="sidebar-unread-${item.badge}">0</span>`;
    }
    return `<a href="${item.href}" class="nav-link ${active}">
      <span class="nav-icon">${item.icon}</span>
      <span style="flex:1">${item.label}</span>
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
            <div class="logo-sub-s">بوابة المستخدم</div>
          </div>
        </a>
      </div>
      <nav class="sidebar-nav">
        <div class="nav-section-label">القائمة الرئيسية</div>
        ${navHTML}
      </nav>
      <div class="sidebar-footer">
        <div class="sidebar-user">
          <div class="user-avatar">${(user.name || 'م')[0]}</div>
          <div class="user-info">
            <div class="user-name-s">${user.name || 'المستخدم'}</div>
            <div class="user-email-s">${user.email || ''}</div>
          </div>
          <div style="position:relative;flex-shrink:0;margin-right:4px">
            <button onclick="typeof openCart==='function'&&openCart()"
                    title="سلة التسوق"
                    style="background:rgba(255,255,255,.18);border:none;border-radius:8px;width:34px;height:34px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:18px;position:relative;transition:background .15s"
                    onmouseover="this.style.background='rgba(255,255,255,.28)'"
                    onmouseout="this.style.background='rgba(255,255,255,.18)'">
              🛒
              <span id="cart-badge"
                    style="display:none;position:absolute;top:-4px;left:-4px;background:#ef4444;color:white;border-radius:50%;width:16px;height:16px;font-size:10px;align-items:center;justify-content:center;font-weight:700">
                0
              </span>
            </button>
          </div>
        </div>
        <button class="sidebar-logout" onclick="logoutUser()">🚪 تسجيل الخروج</button>
      </div>
    </aside>`;

  const container = document.getElementById('sidebar-container');
  if (container) container.innerHTML = html;

  _setupUserMobileToggle();
  _listenUserUnreadBadge();

  // Cart drawer (only on pages that load cart.js)
  if (typeof injectCartDrawer === 'function') {
    injectCartDrawer();
    updateCartBadge();
  }

  // Notification engine — load dynamically if not already on the page
  if (typeof initNotifications === 'function') {
    // notifications.js already loaded (e.g. notifications.html loads it in <head>)
    seedDemoNotifications();
    if (typeof seedMockDoses   === 'function') seedMockDoses();
    if (typeof seedFavMedicines === 'function') seedFavMedicines();
    initNotifications();
  } else {
    const _ns = document.createElement('script');
    _ns.src   = '../js/notifications.js';
    _ns.onload = () => {
      seedDemoNotifications();
      if (typeof seedMockDoses    === 'function') seedMockDoses();
      if (typeof seedFavMedicines === 'function') seedFavMedicines();
      initNotifications();
    };
    document.head.appendChild(_ns);
  }
}

function _setupUserMobileToggle() {
  const btn     = document.getElementById('hamburger-btn');
  const overlay = document.getElementById('sidebar-overlay');
  const sidebar = document.getElementById('sidebar');
  const open  = () => { sidebar?.classList.add('open');    overlay?.classList.add('active'); };
  const close = () => { sidebar?.classList.remove('open'); overlay?.classList.remove('active'); };
  btn?.addEventListener('click', open);
  overlay?.addEventListener('click', close);
  sidebar?.querySelectorAll('.nav-link').forEach(l => l.addEventListener('click', close));
}

function _listenUserUnreadBadge() {
  const badge = document.getElementById('sidebar-unread-chats');
  if (!badge) return;

  if (DEMO_MODE) {
    const uid   = window.currentUser?.uid || 'demo-user';
    const total = mockChats.reduce((s, c) => s + (c.unreadByUser || 0), 0);
    badge.textContent = total;
    badge.style.display = total > 0 ? 'flex' : 'none';
    return;
  }

  const uid = window.currentUser?.uid;
  if (!uid) return;
  database.ref('chats').orderByChild('userId').equalTo(uid).on('value', snap => {
    let total = 0;
    snap.forEach(pharm => {
      pharm.forEach(chat => { total += (chat.val().unreadByUser || 0); });
    });
    badge.textContent = total;
    badge.style.display = total > 0 ? 'flex' : 'none';
  });
  window.addEventListener('beforeunload', () => database.ref('chats').off());
}
