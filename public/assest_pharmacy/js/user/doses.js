// TODO: Laravel API — GET/POST/PUT/DELETE /api/user/doses

let _doses = [], _editDoseId = null;

async function initDoses() {
  await _loadDoses();
  _setupModal();
  document.getElementById('btn-add-dose')?.addEventListener('click', openAddDose);
}

async function _loadDoses() {
  // TODO: Laravel API GET /api/user/doses
  if (!DEMO_MODE) {
    try {
      const uid  = window.currentUser?.uid;
      const snap = await db.collection('doses').where('userId','==',uid).get();
      _doses = snap.docs.map(d => ({ id: d.id, ...d.data() }));
    } catch(e) { _doses = [...mockDoses]; }
  } else {
    _doses = [...mockDoses];
  }
  _renderDoses();
}

function _renderDoses() {
  const list  = document.getElementById('doses-list');
  const active = _doses.filter(d => d.active);
  const past   = _doses.filter(d => !d.active);

  let html = '';

  if (!_doses.length) {
    list.innerHTML = `<div class="empty-state"><span class="empty-icon">💊</span><p>لم تُضف أي أدوية بعد</p></div>`;
    return;
  }

  if (active.length) {
    html += `<div class="dose-section-label">الجرعات النشطة (${active.length})</div>`;
    html += active.map(d => _doseCard(d)).join('');
  }
  if (past.length) {
    html += `<div class="dose-section-label" style="margin-top:24px">منتهية الصلاحية (${past.length})</div>`;
    html += past.map(d => _doseCard(d, true)).join('');
  }

  list.innerHTML = html;
}

function _doseCard(d, faded = false) {
  const now   = new Date();
  const hhmm  = now.getHours().toString().padStart(2,'0') + ':' + now.getMinutes().toString().padStart(2,'0');
  const times = (d.times || []).map(t => {
    const isPast = t < hhmm;
    return `<span class="dose-time-chip ${isPast ? 'past' : 'upcoming'}">${t}</span>`;
  }).join('');

  return `
    <div class="dose-card ${faded ? 'faded' : ''}">
      <div class="dose-card-header">
        <div>
          <div class="dose-card-name">${esc(d.nameAr)} <span style="color:var(--text-muted);font-size:13px;font-weight:400">${d.nameEn ? '(' + esc(d.nameEn) + ')' : ''}</span></div>
          ${d.dosage ? `<div class="dose-card-dosage">💊 ${esc(d.dosage)}</div>` : ''}
        </div>
        <div style="display:flex;gap:8px;align-items:center">
          ${d.active ? '<span class="badge badge-success">نشط</span>' : '<span class="badge badge-outline">منتهي</span>'}
          <button class="btn btn-sm btn-outline" onclick="openEditDose('${d.id}')">تعديل</button>
          <button class="btn btn-sm btn-danger" onclick="deleteDose('${d.id}')">حذف</button>
        </div>
      </div>
      <div class="dose-times-row">${times}</div>
      ${d.notes ? `<div style="font-size:12px;color:var(--text-muted);margin-top:6px">📝 ${esc(d.notes)}</div>` : ''}
      ${d.until ? `<div style="font-size:12px;color:var(--text-muted);margin-top:4px">⏳ حتى: ${fmtDate(d.until)}</div>` : ''}
    </div>`;
}

function openAddDose() {
  _editDoseId = null;
  document.getElementById('dose-modal-title').textContent = 'إضافة دواء';
  _clearDoseForm();
  _addTimeInput('08:00');
  document.getElementById('dose-modal').classList.add('active');
}

function openEditDose(id) {
  const d = _doses.find(x => x.id === id);
  if (!d) return;
  _editDoseId = id;
  document.getElementById('dose-modal-title').textContent = 'تعديل الدواء';
  document.getElementById('df-nameAr').value = d.nameAr || '';
  document.getElementById('df-nameEn').value = d.nameEn || '';
  document.getElementById('df-dosage').value = d.dosage || '';
  document.getElementById('df-until').value  = d.until  || '';
  document.getElementById('df-notes').value  = d.notes  || '';
  document.getElementById('times-container').innerHTML = '';
  (d.times || ['08:00']).forEach(t => _addTimeInput(t));
  document.getElementById('dose-modal').classList.add('active');
}

function _addTimeInput(val = '') {
  const tc  = document.getElementById('times-container');
  const row = document.createElement('div');
  row.style.cssText = 'display:flex;gap:8px;align-items:center';
  row.innerHTML = `
    <input type="time" value="${val}" class="form-control" style="max-width:130px">
    <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">✕</button>`;
  tc.appendChild(row);
}

function _clearDoseForm() {
  ['df-nameAr','df-nameEn','df-dosage','df-until','df-notes','dose-msg']
    .forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
  document.getElementById('dose-msg').textContent = '';
  document.getElementById('times-container').innerHTML = '';
}

function _setupModal() {
  const modal  = document.getElementById('dose-modal');
  const close  = () => { modal.classList.remove('active'); _editDoseId = null; };
  document.getElementById('dose-modal-close')?.addEventListener('click', close);
  document.getElementById('dose-modal-cancel')?.addEventListener('click', close);
  modal?.addEventListener('click', e => { if (e.target === modal) close(); });
  document.getElementById('btn-add-time')?.addEventListener('click', () => _addTimeInput(''));
  document.getElementById('dose-modal-save')?.addEventListener('click', saveDose);
}

async function saveDose() {
  const msg    = document.getElementById('dose-msg');
  const nameAr = document.getElementById('df-nameAr').value.trim();
  if (!nameAr) { msg.textContent = 'اسم الدواء مطلوب.'; msg.style.color = 'var(--danger)'; return; }

  const times = Array.from(document.getElementById('times-container').querySelectorAll('input[type=time]'))
    .map(i => i.value).filter(Boolean).sort();
  if (!times.length) { msg.textContent = 'أضف موعد جرعة واحد على الأقل.'; msg.style.color = 'var(--danger)'; return; }

  const dose = {
    nameAr, times,
    nameEn: document.getElementById('df-nameEn').value.trim(),
    dosage: document.getElementById('df-dosage').value.trim(),
    until:  document.getElementById('df-until').value || null,
    notes:  document.getElementById('df-notes').value.trim(),
    active: true,
    userId: window.currentUser?.uid || 'demo-user',
  };

  // TODO: Laravel API POST /api/user/doses  or  PUT /api/user/doses/{id}
  if (DEMO_MODE) {
    if (_editDoseId) {
      const idx = _doses.findIndex(x => x.id === _editDoseId);
      if (idx !== -1) _doses[idx] = { ..._doses[idx], ...dose };
    } else {
      _doses.push({ id: 'dose-' + Date.now(), ...dose });
    }
    document.getElementById('dose-modal').classList.remove('active');
    _renderDoses(); return;
  }

  try {
    if (_editDoseId) {
      await db.collection('doses').doc(_editDoseId).update(dose);
    } else {
      await db.collection('doses').add(dose);
    }
    document.getElementById('dose-modal').classList.remove('active');
    _loadDoses();
  } catch(e) { msg.textContent = 'فشل الحفظ: ' + e.message; msg.style.color = 'var(--danger)'; }
}

async function deleteDose(id) {
  if (!confirm('حذف هذا الدواء من جدول الجرعات؟')) return;
  // TODO: Laravel API DELETE /api/user/doses/{id}
  if (DEMO_MODE) { _doses = _doses.filter(d => d.id !== id); _renderDoses(); return; }
  try {
    await db.collection('doses').doc(id).delete();
    _loadDoses();
  } catch(e) { alert('فشل الحذف: ' + e.message); }
}
