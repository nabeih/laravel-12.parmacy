// ── Sidebar Nav Definition ────────────────────────────────
const NAV_ITEMS = [
  {
    href: 'dashboard.html', label: 'Dashboard',
    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>`
  },
  {
    href: 'inventory.html', label: 'Inventory',
    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>`
  },
  {
    href: 'orders.html', label: 'Orders',
    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>`
  },
  {
    href: 'analytics.html', label: 'Analytics',
    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>`
  },
  {
    href: 'qr-sales.html', label: 'QR Sales',
    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="5" y="5" width="3" height="3" rx="0.5" fill="currentColor" stroke="none"/><rect x="16" y="5" width="3" height="3" rx="0.5" fill="currentColor" stroke="none"/><rect x="16" y="16" width="3" height="3" rx="0.5" fill="currentColor" stroke="none"/></svg>`
  },
  {
    href: 'profile.html', label: 'Profile',
    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>`
  }
];

const LOGOUT_ICON = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>`;

// ── Render Sidebar ────────────────────────────────────────
function renderSidebar() {
  const currentFile = window.location.pathname.split('/').pop() || 'dashboard.html';
  const user = window.currentUser || {};
  const email = user.email || 'pharmacist@example.com';
  const initials = email.substring(0, 1).toUpperCase();

  const navHTML = NAV_ITEMS.map(item => {
    const active = item.href === currentFile ? 'active' : '';
    return `<a href="${item.href}" class="nav-link ${active}">${item.icon}<span>${item.label}</span></a>`;
  }).join('');

  const sidebarHTML = `
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-logo">
        <a href="dashboard.html" class="sidebar-logo-link">
          <span class="logo-emoji">💊</span>
          <div>
            <div class="logo-name">PharmacyLink</div>
            <div class="logo-sub">Management System</div>
          </div>
        </a>
      </div>

      <nav class="sidebar-nav">
        <div class="nav-section-label">Main Menu</div>
        ${navHTML}
      </nav>

      <div class="sidebar-footer">
        <div class="sidebar-user">
          <div class="user-avatar">${initials}</div>
          <div class="user-info">
            <div class="user-name">${DEMO_MODE ? 'Demo Pharmacist' : (user.displayName || 'Pharmacist')}</div>
            <div class="user-email">${email}</div>
          </div>
        </div>
        <button class="logout-btn" onclick="logout()">
          ${LOGOUT_ICON}
          Sign Out
        </button>
      </div>
    </aside>`;

  const container = document.getElementById('sidebar-container');
  if (container) container.innerHTML = sidebarHTML;

  // ── Mobile hamburger ──────────────────────────────────
  const hamburger = document.getElementById('hamburger');
  const overlay   = document.getElementById('sidebar-overlay');
  const sidebar   = document.getElementById('sidebar');

  function openSidebar()  { sidebar.classList.add('open');    overlay.classList.add('active');    }
  function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('active'); }

  hamburger?.addEventListener('click', openSidebar);
  overlay?.addEventListener('click',   closeSidebar);

  // Close sidebar when a nav link is clicked on mobile
  sidebar?.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', () => {
      if (window.innerWidth <= 768) closeSidebar();
    });
  });

  // Show demo banner if in demo mode
  if (DEMO_MODE) {
    const main = document.querySelector('.main-content');
    if (main) {
      const banner = document.createElement('div');
      banner.className = 'demo-banner';
      banner.innerHTML = '🟡 <strong>Demo Mode</strong> — Replace Firebase config in <code>js/firebase-config.js</code> to connect your database. All data is mock data.';
      main.insertBefore(banner, main.firstChild);
    }
  }
}
