// TODO: Laravel API — GET /api/user/dashboard

async function initUserDashboard() {
  // notifications.js may still be loading dynamically from sidebar.js — guard with typeof
  if (typeof initNotifications === 'function') initNotifications();
  _injectSearchBar();
  _detectGPS();
  const u = window.currentUser || mockUser;
  document.getElementById('user-greeting').textContent =
    `مرحباً، ${u.name || 'المستخدم'} 👋`;
  document.getElementById('user-date').textContent =
    new Date().toLocaleDateString('ar-EG', { weekday:'long', year:'numeric', month:'long', day:'numeric' });

  await Promise.all([_loadRecentOrders(), _loadDoses()]);
  _showNextDose();
}

async function _loadRecentOrders() {
  // TODO: Laravel API GET /api/user/orders?limit=3
  const orders = DEMO_MODE ? mockUserOrders.slice(0,3) : await _fetchUserOrders(3);
  const list   = document.getElementById('recent-orders-list');
  if (!orders.length) {
    list.innerHTML = `<li class="empty-state"><span class="empty-icon">🛒</span><p>لا توجد طلبات بعد</p></li>`;
    return;
  }
  list.innerHTML = orders.map(o => `
    <li class="order-list-item">
      <div class="oli-header">
        <span class="oli-id">#${esc(o.id)}</span>
        ${statusBadge(o.status)}
      </div>
      <div class="oli-pharmacy">${esc(o.pharmacyName || '—')}</div>
      <div class="oli-footer">
        <span class="oli-total">₪${fmt(o.total)}</span>
        <span class="oli-date">${timeAgo(o.date || o.createdAt)}</span>
      </div>
    </li>`).join('');
}

async function _loadDoses() {
  // TODO: Laravel API GET /api/user/doses?active=true
  const doses = DEMO_MODE ? mockDoses.filter(d => d.active) : await _fetchDoses();
  const list  = document.getElementById('dose-list');
  if (!doses.length) {
    list.innerHTML = `<li class="empty-state"><span class="empty-icon">💊</span><p>لا توجد جرعات نشطة</p></li>`;
    return;
  }
  list.innerHTML = doses.slice(0,4).map(d => `
    <li class="dose-list-item">
      <div class="dose-dot"></div>
      <div class="dose-info">
        <div class="dose-name">${esc(d.nameAr)}</div>
        <div class="dose-times">${(d.times || []).join(' • ')}</div>
      </div>
    </li>`).join('');
}

function _showNextDose() {
  const now   = new Date();
  const hhmm  = now.getHours().toString().padStart(2,'0') + ':' + now.getMinutes().toString().padStart(2,'0');
  let next = null, nextDose = null;
  for (const d of mockDoses.filter(x => x.active)) {
    for (const t of (d.times || [])) {
      if (t > hhmm && (!next || t < next)) { next = t; nextDose = d; }
    }
  }
  if (nextDose) {
    document.getElementById('nd-name').textContent = nextDose.nameAr;
    document.getElementById('nd-time').textContent = `الجرعة القادمة: ${next}`;
    document.getElementById('next-dose-banner').style.display = 'flex';
  }
}

async function _fetchUserOrders(limit = 10) {
  try {
    const uid  = window.currentUser?.uid;
    const snap = await db.collection('orders').where('userId','==',uid).orderBy('createdAt','desc').limit(limit).get();
    return snap.docs.map(d => ({ id: d.id, ...d.data() }));
  } catch(e) { return mockUserOrders; }
}

async function _fetchDoses() {
  try {
    const uid  = window.currentUser?.uid;
    const snap = await db.collection('doses').where('userId','==',uid).where('active','==',true).get();
    return snap.docs.map(d => ({ id: d.id, ...d.data() }));
  } catch(e) { return mockDoses; }
}

// ── Search bar ─────────────────────────────────────────────
function _injectSearchBar() {
  const quickActions = document.getElementById('quick-actions');
  if (!quickActions || document.getElementById('home-search-bar')) return;
  const div = document.createElement('div');
  div.id        = 'home-search-bar';
  div.className = 'home-search-bar';
  div.innerHTML = `
    <input type="text" id="homeSearch"
           placeholder="ابحث عن دواء... مثال: باراسيتامول"
           onkeydown="if(event.key==='Enter')searchMedicine()" />
    <button onclick="searchMedicine()">🔍 بحث</button>`;
  quickActions.parentNode.insertBefore(div, quickActions);
}

function searchMedicine() {
  const val = (document.getElementById('homeSearch')?.value || '').trim();
  if (!val) {
    if (typeof showToast === 'function') showToast('أدخل اسم الدواء', 'error');
    else alert('أدخل اسم الدواء');
    return;
  }
  window.location.href = 'search-results.html?q=' + encodeURIComponent(val);
}

// ── GPS detection ──────────────────────────────────────────
function _detectGPS() {
  if (!navigator.geolocation) { _showCityBanner(); return; }
  navigator.geolocation.getCurrentPosition(
    pos => {
      localStorage.setItem('pharma_user_lat', pos.coords.latitude);
      localStorage.setItem('pharma_user_lng', pos.coords.longitude);
    },
    () => _showCityBanner(),
    { timeout: 5000 }
  );
}

function _showCityBanner() {
  if (document.getElementById('city-banner')) return;
  const main   = document.querySelector('.main-content');
  if (!main) return;
  const banner = document.createElement('div');
  banner.id    = 'city-banner';
  banner.style.cssText = 'background:#FFFBEB;border:1px solid #FDE68A;border-radius:8px;padding:10px 16px;font-size:13px;color:#92400E;display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:16px';
  const cities = ['نابلس','رام الله','جنين','الخليل','طولكرم'];
  const saved  = localStorage.getItem('pharma_user_city') || '';
  banner.innerHTML = `
    <span>📍 لتحسين نتائج البحث، حدد مدينتك:</span>
    <select onchange="_saveCity(this.value)"
            style="padding:5px 10px;border:1px solid #FDE68A;border-radius:6px;background:white;color:#92400E;cursor:pointer">
      <option value="">اختر مدينتك</option>
      ${cities.map(c => `<option value="${c}" ${saved === c ? 'selected' : ''}>${c}</option>`).join('')}
    </select>
    <button onclick="document.getElementById('city-banner').remove()"
            style="margin-right:auto;background:none;border:none;cursor:pointer;font-size:16px;color:#92400E;opacity:.6">✕</button>`;

  const header = main.querySelector('.page-header');
  if (header) header.insertAdjacentElement('afterend', banner);
  else main.prepend(banner);
}

function _saveCity(city) {
  if (city) {
    localStorage.setItem('pharma_user_city', city);
    showToast?.('تم حفظ مدينتك: ' + city);
  }
}
