// ── Search Results Page ────────────────────────────────────

let _results = [];
let _query   = '';
let _sortBy  = 'default';

function getDistanceKm(lat1, lng1, lat2, lng2) {
  const R    = 6371;
  const dLat = (lat2 - lat1) * Math.PI / 180;
  const dLng = (lng2 - lng1) * Math.PI / 180;
  const a    = Math.sin(dLat / 2) ** 2
             + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180)
             * Math.sin(dLng / 2) ** 2;
  return R * 2 * Math.asin(Math.sqrt(a));
}

function initSearchResults() {
  const params = new URLSearchParams(window.location.search);
  _query = params.get('q') || '';

  document.getElementById('search-title').textContent = `نتائج البحث عن: "${_query}"`;
  document.getElementById('search-input-top').value   = _query;

  // Bind sort buttons immediately (before results load)
  document.querySelectorAll('.sort-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.sort-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      _sortBy = btn.dataset.sort;
      if (_results.length) _render();
    });
  });

  document.getElementById('search-input-top')?.addEventListener('keydown', e => {
    if (e.key === 'Enter') searchAgain();
  });

  // Show skeleton then load
  _showSkeleton();
  setTimeout(() => {
    _results = _getResults(_query);
    _addDistances(_results);
    _render();
  }, 1000);
}

function _getResults(q) {
  if (!q) return [];
  const qLower = q.toLowerCase();
  const found  = [];
  const seen   = new Set();

  // Search all pharmacy products
  Object.entries(mockPharmacyProducts || {}).forEach(([pharmacyId, products]) => {
    const pharmacy = (mockPharmaciesList || []).find(p => p.id === pharmacyId);
    if (!pharmacy) return;
    (products || []).forEach(prod => {
      if (prod.nameAr.includes(q) || prod.nameEn.toLowerCase().includes(qLower)) {
        const key = pharmacyId + '|' + prod.id;
        if (!seen.has(key)) {
          seen.add(key);
          found.push({
            ...prod,
            pharmacyId,
            pharmacyName:  pharmacy.name,
            pharmacyCity:  pharmacy.city,
            pharmacyLat:   pharmacy.lat,
            pharmacyLng:   pharmacy.lng,
            pharmacyPhone: pharmacy.phone
          });
        }
      }
    });
  });

  // Also check mockSearchResults for exact matches
  const srKey = Object.keys(mockSearchResults || {}).find(k => k.includes(q) || q.includes(k));
  if (srKey) {
    (mockSearchResults[srKey] || []).forEach(r => {
      const key = r.pharmacyId + '|' + (r.id || r.nameEn);
      if (!seen.has(key)) {
        seen.add(key);
        found.push(r);
      }
    });
  }

  return found;
}

function _addDistances(results) {
  const lat = parseFloat(localStorage.getItem('pharma_user_lat'));
  const lng = parseFloat(localStorage.getItem('pharma_user_lng'));
  if (!lat || !lng) return;
  results.forEach(r => {
    if (r.pharmacyLat && r.pharmacyLng) {
      r.distance = getDistanceKm(lat, lng, r.pharmacyLat, r.pharmacyLng);
    }
  });
}

function sortResults(results, sortBy) {
  const lat    = parseFloat(localStorage.getItem('pharma_user_lat'));
  const lng    = parseFloat(localStorage.getItem('pharma_user_lng'));
  const hasGPS = !!(lat && lng);
  const sorted = [...results];

  if (sortBy === 'nearest' && hasGPS) {
    sorted.sort((a, b) => (a.distance || 999) - (b.distance || 999));
  } else if (sortBy === 'cheapest') {
    sorted.sort((a, b) => a.price - b.price);
  } else {
    // default: nearest if GPS, else cheapest
    if (hasGPS) sorted.sort((a, b) => (a.distance || 999) - (b.distance || 999));
    else        sorted.sort((a, b) => a.price - b.price);
  }
  return sorted;
}

function getTopPicks(results) {
  if (!results.length) return { cheapest: null, nearest: null };
  const cheapest = [...results].sort((a, b) => a.price - b.price)[0];
  const lat      = parseFloat(localStorage.getItem('pharma_user_lat'));
  const lng      = parseFloat(localStorage.getItem('pharma_user_lng'));
  let   nearest  = null;
  if (lat && lng) {
    nearest = [...results].sort((a, b) => (a.distance || 999) - (b.distance || 999))[0];
  }
  return { cheapest, nearest };
}

function _showSkeleton() {
  document.getElementById('results-count').textContent = 'جاري البحث...';
  document.getElementById('top-picks').style.display   = 'none';
  document.getElementById('results-list').innerHTML    = Array(3).fill(0).map(() =>
    `<div class="skeleton" style="height:80px;border-radius:12px;margin-bottom:10px"></div>`
  ).join('');
}

function _render() {
  if (!_results.length) { _renderEmpty(); return; }

  const sorted = sortResults(_results, _sortBy);
  const { cheapest, nearest } = getTopPicks(_results);
  const hasGPS   = !!(parseFloat(localStorage.getItem('pharma_user_lat')));
  const pharmSet = new Set(_results.map(r => r.pharmacyId));

  document.getElementById('results-count').textContent =
    `تم العثور على ${_results.length} نتيجة في ${pharmSet.size} صيدلية`;

  // Top picks
  const tpEl = document.getElementById('top-picks');
  if (cheapest || nearest) {
    tpEl.style.display = 'grid';
    tpEl.innerHTML = `
      ${cheapest ? `
      <div style="background:white;border-radius:var(--radius);padding:16px;box-shadow:var(--shadow);border-right:4px solid var(--success)">
        <div style="color:var(--success);font-weight:700;margin-bottom:6px">💚 الأرخص</div>
        <div style="font-size:16px;font-weight:700">${esc(cheapest.pharmacyName)}</div>
        <div style="color:var(--text-muted);font-size:13px">${esc(cheapest.pharmacyCity)}</div>
        <div style="font-size:26px;font-weight:800;color:var(--primary);margin-top:6px">₪${cheapest.price.toFixed(2)}</div>
        ${cheapest.distance ? `<div style="font-size:12px;color:var(--text-muted)">📍 ${cheapest.distance.toFixed(1)} كم</div>` : ''}
      </div>` : ''}
      ${nearest && nearest.pharmacyId !== cheapest?.pharmacyId ? `
      <div style="background:white;border-radius:var(--radius);padding:16px;box-shadow:var(--shadow);border-right:4px solid var(--info)">
        <div style="color:var(--info);font-weight:700;margin-bottom:6px">📍 الأقرب</div>
        <div style="font-size:16px;font-weight:700">${esc(nearest.pharmacyName)}</div>
        <div style="color:var(--text-muted);font-size:13px">${esc(nearest.pharmacyCity)}</div>
        <div style="font-size:26px;font-weight:800;color:var(--primary);margin-top:6px">₪${nearest.price.toFixed(2)}</div>
        ${nearest.distance ? `<div style="font-size:12px;color:var(--text-muted)">📍 ${nearest.distance.toFixed(1)} كم</div>` : ''}
      </div>` : ''}`;
  } else {
    tpEl.style.display = 'none';
  }

  // Results list
  document.getElementById('results-list').innerHTML = sorted.map(r => {
    const isCheapest = cheapest && r.pharmacyId === cheapest.pharmacyId && r.id === cheapest.id;
    const isNearest  = nearest  && r.pharmacyId === nearest.pharmacyId  && r.id === nearest.id;

    let stockBadge = '';
    if (r.stock === 0)            stockBadge = `<span class="badge badge-red">نفذ</span>`;
    else if (r.stock <= r.minStock) stockBadge = `<span class="badge badge-yellow">كمية محدودة</span>`;
    else                            stockBadge = `<span class="badge badge-green">متوفر</span>`;

    const tags = [
      isCheapest ? `<span class="tag-cheapest">الأرخص 💚</span>` : '',
      (isNearest && hasGPS) ? `<span class="tag-nearest">الأقرب 📍</span>` : ''
    ].filter(Boolean).join('');

    const distText = r.distance ? ` • 📍 ${r.distance.toFixed(1)} كم` : '';
    const outOfStock = r.stock === 0;

    return `
      <div class="result-card ${isCheapest ? 'cheapest' : isNearest ? 'nearest' : ''}">
        <div style="flex:1;min-width:0">
          <div style="font-weight:700;font-size:15px">${esc(r.nameAr)}
            <span style="font-size:12px;color:var(--text-muted);font-weight:400">(${esc(r.nameEn)})</span>
          </div>
          <div class="pharmacy-tag">🏪 ${esc(r.pharmacyName)} — ${esc(r.pharmacyCity)}${distText}</div>
          <div style="margin-top:6px;display:flex;gap:4px;flex-wrap:wrap;align-items:center">
            ${tags} ${stockBadge}
          </div>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px;flex-shrink:0;margin-right:12px">
          <div class="price-tag">₪${r.price.toFixed(2)}</div>
          <div style="display:flex;gap:6px">
            <button class="btn btn-primary btn-sm"
                    onclick="_addResultToCart('${r.id}','${r.pharmacyId}','${esc(r.pharmacyName)}')"
                    ${outOfStock ? 'disabled style="opacity:.5"' : ''}>
              إضافة للسلة
            </button>
            <a href="pharmacy-details.html?id=${r.pharmacyId}" class="btn btn-outline btn-sm">عرض الصيدلية</a>
          </div>
        </div>
      </div>`;
  }).join('');
}

function _renderEmpty() {
  document.getElementById('results-count').textContent = '';
  document.getElementById('top-picks').style.display   = 'none';
  document.getElementById('results-list').innerHTML    = `
    <div class="empty-state">
      <span class="empty-icon">🔍</span>
      <h3>لم يتم العثور على "${esc(_query)}"</h3>
      <p>لا يتوفر هذا الدواء في أي صيدلية حالياً</p>
      <button class="btn btn-primary" style="margin-top:16px"
              onclick="document.getElementById('search-input-top').focus()">
        ابحث مجدداً
      </button>
    </div>`;
}

function searchAgain() {
  const val = (document.getElementById('search-input-top')?.value || '').trim();
  if (!val) return;
  window.location.href = 'search-results.html?q=' + encodeURIComponent(val);
}

function _addResultToCart(productId, pharmacyId, pharmacyName) {
  // Try to find the product in pharmacy products
  let product = null;
  const phProducts = (mockPharmacyProducts || {})[pharmacyId];
  if (phProducts) product = phProducts.find(p => p.id === productId);

  // Fallback: look in the results list we already have
  if (!product) {
    product = _results.find(r => r.id === productId && r.pharmacyId === pharmacyId);
  }

  if (!product) { showToast('لم يتم العثور على الدواء', 'error'); return; }

  const result = addToCart(pharmacyId, pharmacyName, product);
  if (result.conflict) {
    if (confirm(`سلتك تحتوي على أدوية من ${result.existingPharmacy}.\nهل تريد مسح السلة والبدء من ${pharmacyName}؟`)) {
      clearCart();
      addToCart(pharmacyId, pharmacyName, product);
      showToast('تمت الإضافة للسلة ✓');
    }
    return;
  }
  showToast('تمت الإضافة للسلة ✓');
}
