// TODO: Laravel API — GET/DELETE /api/user/favorites

let _favMeds = [], _favPharms = [];

async function initFavorites() {
  // TODO: Laravel API GET /api/user/favorites
  _favMeds   = [...mockFavoriteMedicines];
  _favPharms = [...mockFavoritePharmacies];
  _renderMeds();
  _renderPharms();
}

function _renderMeds() {
  const el = document.getElementById('fav-medicines');
  document.getElementById('med-count').textContent = _favMeds.length;
  if (!_favMeds.length) {
    el.innerHTML = `<div class="empty-state"><span class="empty-icon">💊</span><p>لا توجد أدوية مفضلة</p></div>`;
    return;
  }
  el.innerHTML = _favMeds.map(m => `
    <div class="fav-card">
      <div class="fav-card-icon">💊</div>
      <div class="fav-card-body">
        <div class="fav-card-name">${esc(m.nameAr)}</div>
        <div class="fav-card-sub">${esc(m.nameEn || '')}</div>
        <div class="fav-card-price">₪${fmt(m.price)}</div>
      </div>
      <button class="btn-fav-remove" onclick="removeMed('${m.id}')" title="إزالة من المفضلة">❤️</button>
    </div>`).join('');
}

function _renderPharms() {
  const el = document.getElementById('fav-pharmacies');
  document.getElementById('ph-count').textContent = _favPharms.length;
  if (!_favPharms.length) {
    el.innerHTML = `<div class="empty-state"><span class="empty-icon">🏪</span><p>لا توجد صيدليات مفضلة</p></div>`;
    return;
  }
  el.innerHTML = _favPharms.map(p => `
    <div class="fav-card">
      <div class="fav-card-icon">🏪</div>
      <div class="fav-card-body">
        <div class="fav-card-name">${esc(p.name)}</div>
        <div class="fav-card-sub">📍 ${esc(p.governorate || '')}</div>
        <div class="fav-card-sub">${p.isOpen ? '<span style="color:var(--success)">● مفتوح</span>' : '<span style="color:var(--danger)">● مغلق</span>'}</div>
      </div>
      <div style="display:flex;flex-direction:column;gap:6px;align-items:flex-end">
        <button class="btn btn-sm btn-outline" onclick="chatWithPharmacy('${p.id}')">💬 محادثة</button>
        <button class="btn-fav-remove" onclick="removePharm('${p.id}')" title="إزالة">❤️</button>
      </div>
    </div>`).join('');
}

function removeMed(id) {
  // TODO: Laravel API DELETE /api/user/favorites/medicines/{id}
  _favMeds = _favMeds.filter(m => m.id !== id);
  _renderMeds();
}

function removePharm(id) {
  // TODO: Laravel API DELETE /api/user/favorites/pharmacies/{id}
  _favPharms = _favPharms.filter(p => p.id !== id);
  _renderPharms();
}

function chatWithPharmacy(pharmacyId) {
  window.location.href = `chats.html?pharmacyId=${pharmacyId}`;
}
