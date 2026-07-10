// ── Pharmacies List Page ───────────────────────────────────

// Approximate coordinates for Palestinian cities
const CITY_COORDS = {
  'نابلس':    { lat: 32.2211, lng: 35.2544 },
  'رام الله': { lat: 31.9038, lng: 35.2034 },
  'جنين':     { lat: 32.4616, lng: 35.2946 },
  'الخليل':   { lat: 31.5326, lng: 35.0998 },
  'طولكرم':   { lat: 32.3104, lng: 35.0286 },
  'بيت لحم':  { lat: 31.7054, lng: 35.2024 },
  'قلقيلية':  { lat: 32.1862, lng: 34.9707 },
  'أريحا':    { lat: 31.8413, lng: 35.4572 },
  'طوباس':    { lat: 32.3235, lng: 35.3705 },
  'البيرة':   { lat: 31.9060, lng: 35.2160 }
};

function getDistanceKm(lat1, lng1, lat2, lng2) {
  const R    = 6371;
  const dLat = (lat2 - lat1) * Math.PI / 180;
  const dLng = (lng2 - lng1) * Math.PI / 180;
  const a    = Math.sin(dLat / 2) ** 2
             + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180)
             * Math.sin(dLng / 2) ** 2;
  return R * 2 * Math.asin(Math.sqrt(a));
}

let _pharmacies = [];
let _sortBy     = 'nearest';
let _locPref    = 'gps';   // 'gps' | 'home' | 'work'

function initPharmacies() {
  _pharmacies = mockPharmaciesList.map(p => ({ ...p }));
  _addDistances();
  _renderSubtitle();

  setTimeout(() => {
    _sortAndRender();
    _bindFilters();
  }, 800);
}

// Returns {lat, lng} for the currently selected location preference
function _getRefCoords() {
  if (_locPref === 'gps') {
    const lat = parseFloat(localStorage.getItem('pharma_user_lat'));
    const lng = parseFloat(localStorage.getItem('pharma_user_lng'));
    return (lat && lng) ? { lat, lng } : null;
  }

  // home / work — look up the saved address then map its city to coordinates
  const labelMap = { home: 'المنزل', work: 'العمل' };
  const addr = (mockAddresses || []).find(a => a.label === labelMap[_locPref]);
  if (!addr) return null;

  // Try exact governorate match, then area match in CITY_COORDS
  return CITY_COORDS[addr.governorate] || CITY_COORDS[addr.area] || null;
}

function _addDistances() {
  const ref = _getRefCoords();
  _pharmacies.forEach(p => {
    if (ref) {
      p.distance      = getDistanceKm(ref.lat, ref.lng, p.lat, p.lng);
      p.distanceLabel = p.distance.toFixed(1) + ' كم';
    } else {
      p.distance      = null;
      p.distanceLabel = '';
    }
  });
}

function _updateLocLabel() {
  const el  = document.getElementById('loc-pref-label');
  if (!el) return;
  const ref = _getRefCoords();
  if (!ref) {
    const hints = {
      gps:  '(تعذّر تحديد الموقع — اضغط "تحديث GPS")',
      home: '(لا يوجد عنوان منزل محفوظ)',
      work: '(لا يوجد عنوان عمل محفوظ)'
    };
    el.textContent = hints[_locPref] || '';
  } else {
    el.textContent = '';
  }
}

function _renderSubtitle() {
  const city = localStorage.getItem('pharma_user_city');
  const el   = document.getElementById('pharmacies-subtitle');
  if (el) el.textContent = city ? `المدينة: ${city}` : 'جميع المناطق';
  _updateLocLabel();
}

function _sortAndRender() {
  let list = [..._pharmacies];

  const cityFilter = document.getElementById('city-filter')?.value || '';
  const search     = (document.getElementById('pharm-search')?.value || '').toLowerCase();

  if (cityFilter) list = list.filter(p => p.city === cityFilter);
  if (search)     list = list.filter(p =>
    p.name.toLowerCase().includes(search) || p.address.toLowerCase().includes(search)
  );

  const hasRef = !!_getRefCoords();

  if (_sortBy === 'nearest' && hasRef) {
    list.sort((a, b) => (a.distance || 999) - (b.distance || 999));
  } else if (_sortBy === 'rating') {
    list.sort((a, b) => b.rating - a.rating);
  } else if (_sortBy === 'newest') {
    // newest = highest id (mock data)
    list.reverse();
  }

  _renderPharmacies(list);
}

function _renderPharmacies(list) {
  const container = document.getElementById('pharmacies-list');
  if (!list.length) {
    container.innerHTML = `
      <div class="empty-state">
        <span class="empty-icon">🏪</span>
        <h3>لا توجد صيدليات مطابقة</h3>
        <p>جرّب تغيير فلتر المدينة أو البحث</p>
      </div>`;
    return;
  }

  container.innerHTML = list.map(p => {
    const openBadge  = p.isOpen
      ? `<span class="badge-open">مفتوح الآن</span>`
      : `<span class="badge-closed">مغلق</span>`;
    const distHTML   = p.distanceLabel
      ? `<span class="pharmacy-distance">📍 ${p.distanceLabel}</span>`
      : '';

    return `
      <div class="pharmacy-card">
        <div style="flex:1;min-width:0">
          <div class="pharmacy-name">${esc(p.name)}</div>
          <div class="pharmacy-info">${esc(p.city)} — ${esc(p.address)}</div>
          <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-top:8px">
            ${openBadge}
            <span style="font-size:13px;color:var(--text-muted)">⭐ ${p.rating}</span>
            <span style="font-size:13px;color:var(--text-muted)">${p.totalMedicines} دواء</span>
            <span style="font-size:12px;color:var(--text-muted)">🕐 ${esc(p.workingHours)}</span>
          </div>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:10px;flex-shrink:0;margin-right:16px">
          ${distHTML}
          <a href="pharmacy-details.html?id=${p.id}" class="btn btn-primary btn-sm">عرض الأدوية →</a>
        </div>
      </div>`;
  }).join('');
}

function _bindFilters() {
  document.getElementById('pharm-search')?.addEventListener('input',
    debounce(_sortAndRender, 300));

  document.getElementById('city-filter')?.addEventListener('change', _sortAndRender);

  document.querySelectorAll('.sort-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.sort-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      _sortBy = btn.dataset.sort;
      _sortAndRender();
    });
  });

  document.getElementById('gps-btn')?.addEventListener('click', () => {
    if (!navigator.geolocation) { showToast('المتصفح لا يدعم تحديد الموقع', 'error'); return; }
    showToast('جاري تحديد موقعك...');
    navigator.geolocation.getCurrentPosition(
      pos => {
        localStorage.setItem('pharma_user_lat', pos.coords.latitude);
        localStorage.setItem('pharma_user_lng', pos.coords.longitude);
        _locPref = 'gps';
        document.querySelectorAll('.loc-pref-btn').forEach(b => b.classList.remove('active'));
        document.querySelector('.loc-pref-btn[data-pref="gps"]')?.classList.add('active');
        _addDistances();
        _sortAndRender();
        _updateLocLabel();
        showToast('تم تحديث موقعك ✓');
      },
      () => showToast('لم يتم السماح بالوصول للموقع', 'error'),
      { timeout: 5000 }
    );
  });

  document.querySelectorAll('.loc-pref-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.loc-pref-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      _locPref = btn.dataset.pref;
      _addDistances();
      _sortAndRender();
      _updateLocLabel();
    });
  });
}
