// TODO: Laravel API — GET/PUT /api/user/profile

async function initUserProfile() {
  _fillProfile();
  _loadStats();
  _setupForms();
}

function _fillProfile() {
  const u = window.currentUser || mockUser;
  document.getElementById('user-avatar').textContent       = (u.name || 'م')[0];
  document.getElementById('user-name-display').textContent = u.name  || '';
  document.getElementById('user-email-display').textContent = u.email || '';
  document.getElementById('uf-name').value  = u.name  || '';
  document.getElementById('uf-email').value = u.email || '';
  document.getElementById('uf-phone').value = u.phone || '';
}

function _loadStats() {
  // TODO: Laravel API GET /api/user/stats
  document.getElementById('stat-orders').textContent = mockUserOrders.length;
  document.getElementById('stat-doses').textContent  = mockDoses.filter(d => d.active).length;
  document.getElementById('stat-favs').textContent   = mockFavoriteMedicines.length + mockFavoritePharmacies.length;
  document.getElementById('stat-addrs').textContent  = mockAddresses.length;
}

function _setupForms() {
  document.getElementById('profile-form')?.addEventListener('submit', async e => {
    e.preventDefault();
    const msg   = document.getElementById('profile-msg');
    const name  = document.getElementById('uf-name').value.trim();
    const phone = document.getElementById('uf-phone').value.trim();
    if (!name) { _showMsg(msg, 'الاسم مطلوب.', 'danger'); return; }

    // TODO: Laravel API PUT /api/user/profile
    if (DEMO_MODE) {
      Object.assign(mockUser, { name, phone });
      if (window.currentUser) Object.assign(window.currentUser, { name, phone });
      document.getElementById('user-name-display').textContent = name;
      document.getElementById('user-avatar').textContent = name[0];
      _showMsg(msg, 'تم حفظ التغييرات.', 'success'); return;
    }
    try {
      await db.collection('users').doc(auth.currentUser.uid).update({ name, phone });
      _showMsg(msg, 'تم حفظ التغييرات.', 'success');
    } catch(err) { _showMsg(msg, 'فشل الحفظ: ' + err.message, 'danger'); }
  });

  document.getElementById('pw-form')?.addEventListener('submit', async e => {
    e.preventDefault();
    const msg    = document.getElementById('pw-msg');
    const curPw  = document.getElementById('uf-cur-pw').value;
    const newPw  = document.getElementById('uf-new-pw').value;
    const confPw = document.getElementById('uf-conf-pw').value;
    if (!curPw || !newPw || !confPw) { _showMsg(msg, 'يرجى ملء جميع الحقول.', 'danger'); return; }
    if (newPw.length < 8) { _showMsg(msg, 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.', 'danger'); return; }
    if (newPw !== confPw) { _showMsg(msg, 'كلمتا المرور غير متطابقتين.', 'danger'); return; }
    if (DEMO_MODE) { _showMsg(msg, '(وضع العرض) تم تغيير كلمة المرور.', 'success'); return; }
    await changePassword(curPw, newPw, 'pw-msg', document.querySelector('#pw-form button[type=submit]'));
  });
}

function _showMsg(el, text, type) {
  el.textContent = text;
  el.style.color = type === 'success' ? 'var(--success)' : 'var(--danger)';
  setTimeout(() => { el.textContent = ''; }, 4000);
}
