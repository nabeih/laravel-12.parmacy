// TODO: Laravel API — GET/PUT /api/pharmacist/profile  PUT /api/pharmacist/pharmacy

const DAYS_AR = ['الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس','الجمعة','السبت'];
let _hours = DAYS_AR.map((d, i) => ({ day: d, open: i < 5, from: '09:00', to: '22:00' }));

async function initPharmacistProfile() {
  _fillProfile();
  _renderHoursGrid();
  _setupForms();
}

function _fillProfile() {
  const u = window.currentUser || mockPharmacistUser;
  document.getElementById('prof-avatar').textContent     = (u.name || 'ص')[0];
  document.getElementById('prof-name-display').textContent  = u.name || '';
  document.getElementById('prof-email-display').textContent = u.email || '';
  document.getElementById('f-name').value   = u.name  || '';
  document.getElementById('f-email').value  = u.email || '';
  document.getElementById('f-phone').value  = u.phone || '';
  document.getElementById('f-pharm-name').value = u.pharmacyName || '';
  document.getElementById('f-gov').value    = u.governorate || '';
  document.getElementById('f-address').value = u.address || '';
}

function _renderHoursGrid() {
  const grid = document.getElementById('hours-grid');
  grid.innerHTML = _hours.map((h, i) => `
    <div class="hours-row">
      <label class="hours-toggle">
        <input type="checkbox" id="day-open-${i}" ${h.open ? 'checked' : ''} onchange="toggleDay(${i})">
        <span class="hours-day">${h.day}</span>
      </label>
      <div class="hours-times" id="hours-times-${i}" style="${h.open ? '' : 'opacity:.4;pointer-events:none'}">
        <input type="time" value="${h.from}" class="form-control form-control-sm" onchange="_hours[${i}].from=this.value" style="max-width:100px">
        <span>—</span>
        <input type="time" value="${h.to}" class="form-control form-control-sm" onchange="_hours[${i}].to=this.value" style="max-width:100px">
      </div>
    </div>`).join('');
}

function toggleDay(i) {
  _hours[i].open = document.getElementById(`day-open-${i}`).checked;
  const times = document.getElementById(`hours-times-${i}`);
  if (times) { times.style.opacity = _hours[i].open ? '1' : '.4'; times.style.pointerEvents = _hours[i].open ? '' : 'none'; }
}

function _setupForms() {
  // Profile form
  document.getElementById('profile-form')?.addEventListener('submit', async e => {
    e.preventDefault();
    const msg  = document.getElementById('profile-msg');
    const name  = document.getElementById('f-name').value.trim();
    const phone = document.getElementById('f-phone').value.trim();
    if (!name) { _showMsg(msg, 'الاسم مطلوب.', 'danger'); return; }

    // TODO: Laravel API PUT /api/pharmacist/profile
    if (DEMO_MODE) {
      Object.assign(mockPharmacistUser, { name, phone });
      if (window.currentUser) Object.assign(window.currentUser, { name, phone });
      document.getElementById('prof-name-display').textContent = name;
      _showMsg(msg, 'تم حفظ التغييرات.', 'success'); return;
    }
    try {
      await db.collection('users').doc(auth.currentUser.uid).update({ name, phone });
      _showMsg(msg, 'تم حفظ التغييرات.', 'success');
    } catch(err) { _showMsg(msg, 'فشل الحفظ: ' + err.message, 'danger'); }
  });

  // Pharmacy form
  document.getElementById('pharmacy-form')?.addEventListener('submit', async e => {
    e.preventDefault();
    const msg = document.getElementById('pharmacy-msg');
    const pharmName = document.getElementById('f-pharm-name').value.trim();
    const gov       = document.getElementById('f-gov').value;
    const address   = document.getElementById('f-address').value.trim();
    if (!pharmName) { _showMsg(msg, 'اسم الصيدلية مطلوب.', 'danger'); return; }

    // TODO: Laravel API PUT /api/pharmacist/pharmacy
    if (DEMO_MODE) {
      Object.assign(mockPharmacistUser, { pharmacyName: pharmName, governorate: gov, address });
      _showMsg(msg, 'تم حفظ معلومات الصيدلية.', 'success'); return;
    }
    try {
      await db.collection('pharmacies').doc(window.currentUser?.pharmacyId || auth.currentUser.uid).update({ name: pharmName, governorate: gov, address, workingHours: _hours });
      _showMsg(msg, 'تم الحفظ.', 'success');
    } catch(err) { _showMsg(msg, 'فشل الحفظ: ' + err.message, 'danger'); }
  });

  // Password form
  document.getElementById('pw-form')?.addEventListener('submit', async e => {
    e.preventDefault();
    const msg    = document.getElementById('pw-msg');
    const curPw  = document.getElementById('f-cur-pw').value;
    const newPw  = document.getElementById('f-new-pw').value;
    const confPw = document.getElementById('f-conf-pw').value;
    if (!curPw || !newPw || !confPw) { _showMsg(msg, 'يرجى ملء جميع الحقول.', 'danger'); return; }
    if (newPw.length < 8) { _showMsg(msg, 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.', 'danger'); return; }
    if (newPw !== confPw) { _showMsg(msg, 'كلمتا المرور غير متطابقتين.', 'danger'); return; }
    if (DEMO_MODE) { _showMsg(msg, '(وضع العرض) تم تغيير كلمة المرور.', 'success'); return; }
    await changePassword(curPw, newPw, 'pw-msg', document.querySelector('#pw-form button[type=submit]'));
  });
}

function _showMsg(el, text, type) {
  el.textContent  = text;
  el.style.color  = type === 'success' ? 'var(--success)' : 'var(--danger)';
  setTimeout(() => { el.textContent = ''; }, 4000);
}
