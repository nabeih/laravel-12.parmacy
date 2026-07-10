// TODO: Laravel API — GET/POST/PUT/DELETE /api/user/addresses

let _addresses = [], _editAddrId = null;

async function initAddresses() {
  // TODO: Laravel API GET /api/user/addresses
  _addresses = [...mockAddresses];
  _renderAddresses();
  _setupModal();
  document.getElementById('btn-add-addr')?.addEventListener('click', openAddAddr);
}

function _renderAddresses() {
  const list = document.getElementById('addresses-list');
  if (!_addresses.length) {
    list.innerHTML = `<div class="empty-state"><span class="empty-icon">📍</span><p>لم تُضف أي عناوين بعد</p></div>`;
    return;
  }
  list.innerHTML = _addresses.map(a => `
    <div class="addr-card ${a.isDefault ? 'default-addr' : ''}">
      <div class="addr-card-header">
        <div style="display:flex;align-items:center;gap:8px">
          <span style="font-size:20px">${a.label === 'المنزل' ? '🏠' : a.label === 'العمل' ? '🏢' : '📍'}</span>
          <strong>${esc(a.label)}</strong>
          ${a.isDefault ? '<span class="badge badge-success">افتراضي</span>' : ''}
        </div>
        <div style="display:flex;gap:6px">
          ${!a.isDefault ? `<button class="btn btn-sm btn-ghost" onclick="setDefault('${a.id}')">تعيين افتراضي</button>` : ''}
          <button class="btn btn-sm btn-outline" onclick="openEditAddr('${a.id}')">تعديل</button>
          <button class="btn btn-sm btn-danger" onclick="deleteAddr('${a.id}')">حذف</button>
        </div>
      </div>
      <div class="addr-detail">📌 ${esc(a.governorate)} — ${esc(a.area)}</div>
      ${a.street ? `<div class="addr-detail">🛣️ ${esc(a.street)}</div>` : ''}
      ${a.notes  ? `<div class="addr-detail">📝 ${esc(a.notes)}</div>` : ''}
    </div>`).join('');
}

function _setupModal() {
  const modal = document.getElementById('addr-modal');
  const close = () => { modal.classList.remove('active'); _editAddrId = null; };
  document.getElementById('addr-modal-close')?.addEventListener('click', close);
  document.getElementById('addr-modal-cancel')?.addEventListener('click', close);
  modal?.addEventListener('click', e => { if (e.target === modal) close(); });
  document.getElementById('addr-modal-save')?.addEventListener('click', saveAddr);
}

function openAddAddr() {
  _editAddrId = null;
  document.getElementById('addr-modal-title').textContent = 'إضافة عنوان';
  _clearForm();
  document.getElementById('addr-modal').classList.add('active');
}

function openEditAddr(id) {
  const a = _addresses.find(x => x.id === id);
  if (!a) return;
  _editAddrId = id;
  document.getElementById('addr-modal-title').textContent = 'تعديل العنوان';
  document.getElementById('af-label').value  = a.label    || '';
  document.getElementById('af-gov').value    = a.governorate || '';
  document.getElementById('af-area').value   = a.area     || '';
  document.getElementById('af-street').value = a.street   || '';
  document.getElementById('af-notes').value  = a.notes    || '';
  document.getElementById('af-default').checked = !!a.isDefault;
  document.getElementById('addr-modal').classList.add('active');
}

function _clearForm() {
  ['af-label','af-gov','af-area','af-street','af-notes','addr-msg']
    .forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
  document.getElementById('af-default').checked  = false;
  document.getElementById('addr-msg').textContent = '';
}

async function saveAddr() {
  const msg   = document.getElementById('addr-msg');
  const label = document.getElementById('af-label').value.trim();
  const gov   = document.getElementById('af-gov').value;
  const area  = document.getElementById('af-area').value.trim();
  if (!label || !gov || !area) { msg.textContent = 'يرجى ملء الحقول الإلزامية.'; msg.style.color = 'var(--danger)'; return; }

  const isDefault = document.getElementById('af-default').checked;
  const addr = {
    label, governorate: gov, area,
    street: document.getElementById('af-street').value.trim(),
    notes:  document.getElementById('af-notes').value.trim(),
    isDefault,
    userId: window.currentUser?.uid || 'demo-user',
  };

  // TODO: Laravel API POST/PUT /api/user/addresses
  if (DEMO_MODE) {
    if (isDefault) _addresses.forEach(a => { a.isDefault = false; });
    if (_editAddrId) {
      const idx = _addresses.findIndex(x => x.id === _editAddrId);
      if (idx !== -1) _addresses[idx] = { ..._addresses[idx], ...addr };
    } else {
      _addresses.push({ id: 'addr-' + Date.now(), ...addr });
    }
    document.getElementById('addr-modal').classList.remove('active');
    _renderAddresses(); return;
  }

  try {
    if (isDefault) {
      const uid  = window.currentUser?.uid;
      const snap = await db.collection('addresses').where('userId','==',uid).get();
      const batch = db.batch();
      snap.docs.forEach(d => batch.update(d.ref, { isDefault: false }));
      await batch.commit();
    }
    if (_editAddrId) {
      await db.collection('addresses').doc(_editAddrId).update(addr);
    } else {
      await db.collection('addresses').add(addr);
    }
    document.getElementById('addr-modal').classList.remove('active');
    initAddresses();
  } catch(e) { msg.textContent = 'فشل الحفظ: ' + e.message; msg.style.color = 'var(--danger)'; }
}

async function setDefault(id) {
  // TODO: Laravel API PUT /api/user/addresses/{id}/default
  if (DEMO_MODE) {
    _addresses.forEach(a => { a.isDefault = a.id === id; });
    _renderAddresses(); return;
  }
  try {
    const uid  = window.currentUser?.uid;
    const snap = await db.collection('addresses').where('userId','==',uid).get();
    const batch = db.batch();
    snap.docs.forEach(d => batch.update(d.ref, { isDefault: d.id === id }));
    await batch.commit();
    initAddresses();
  } catch(e) { alert('فشل: ' + e.message); }
}

async function deleteAddr(id) {
  if (!confirm('حذف هذا العنوان؟')) return;
  // TODO: Laravel API DELETE /api/user/addresses/{id}
  if (DEMO_MODE) { _addresses = _addresses.filter(a => a.id !== id); _renderAddresses(); return; }
  try { await db.collection('addresses').doc(id).delete(); initAddresses(); }
  catch(e) { alert('فشل الحذف: ' + e.message); }
}
