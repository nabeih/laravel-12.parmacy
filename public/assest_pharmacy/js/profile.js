// ── Profile Page ──────────────────────────────────────────
const DAYS = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
const DAY_LABELS = { Sun:'Sunday', Mon:'Monday', Tue:'Tuesday', Wed:'Wednesday', Thu:'Thursday', Fri:'Friday', Sat:'Saturday' };
let pharmacyDocId = null;

async function initProfile() {
  await loadPharmacyInfo();
  renderWorkingHours();
  setupProfileEvents();
}

// ── Load Pharmacy Info ────────────────────────────────────
async function loadPharmacyInfo() {
  let info = { ...mockPharmacyInfo };

  try {
    if (!DEMO_MODE && window.currentUser) {
      const snap = await db.collection('pharmacies')
        .where('ownerId', '==', window.currentUser.uid)
        .limit(1).get();
      if (!snap.empty) {
        pharmacyDocId = snap.docs[0].id;
        info = { ...info, ...snap.docs[0].data() };
      }
    }
  } catch (e) { /* use mock */ }

  document.getElementById('p-name').value    = info.name    || '';
  document.getElementById('p-owner').value   = info.owner   || '';
  document.getElementById('p-phone').value   = info.phone   || '';
  document.getElementById('p-email').value   = info.email   || (window.currentUser?.email || '');
  document.getElementById('p-city').value    = info.city    || '';
  document.getElementById('p-address').value = info.address || '';
  document.getElementById('p-license').value = info.license || '';

  // Store working hours in a data attribute for the hours renderer
  document.getElementById('profile-form').dataset.hours = JSON.stringify(info.workingHours || mockPharmacyInfo.workingHours);
}

// ── Working Hours Renderer ────────────────────────────────
function renderWorkingHours() {
  let hoursData = mockPharmacyInfo.workingHours;
  try {
    const stored = document.getElementById('profile-form').dataset.hours;
    if (stored) hoursData = JSON.parse(stored);
  } catch(e) {}

  const tbody = document.getElementById('hours-tbody');
  tbody.innerHTML = DAYS.map(day => {
    const h = hoursData[day] || { open: true, from: '08:00', to: '20:00' };
    return `
      <tr>
        <td><strong>${DAY_LABELS[day]}</strong></td>
        <td>
          <input type="time" class="form-control" id="h-from-${day}" value="${h.from}"
            ${!h.open ? 'disabled' : ''}>
        </td>
        <td>
          <input type="time" class="form-control" id="h-to-${day}" value="${h.to}"
            ${!h.open ? 'disabled' : ''}>
        </td>
        <td style="text-align:right">
          <label class="toggle" title="${h.open ? 'Open' : 'Closed'}">
            <input type="checkbox" id="h-open-${day}" ${h.open ? 'checked' : ''}
              onchange="toggleDayOpen('${day}', this.checked)">
            <span class="toggle-slider"></span>
          </label>
        </td>
        <td style="padding-left:10px">
          <span id="h-label-${day}" style="font-size:12px;font-weight:500;color:${h.open ? 'var(--success)' : 'var(--text-muted)'}">${h.open ? 'Open' : 'Closed'}</span>
        </td>
      </tr>`;
  }).join('');
}

function toggleDayOpen(day, isOpen) {
  const fromEl  = document.getElementById('h-from-' + day);
  const toEl    = document.getElementById('h-to-'   + day);
  const labelEl = document.getElementById('h-label-' + day);
  if (fromEl)  fromEl.disabled  = !isOpen;
  if (toEl)    toEl.disabled    = !isOpen;
  if (labelEl) {
    labelEl.textContent = isOpen ? 'Open' : 'Closed';
    labelEl.style.color = isOpen ? 'var(--success)' : 'var(--text-muted)';
  }
}

// ── Events ────────────────────────────────────────────────
function setupProfileEvents() {
  document.getElementById('btn-save-info').addEventListener('click',  savePharmacyInfo);
  document.getElementById('btn-save-hours').addEventListener('click', saveWorkingHours);
  document.getElementById('btn-change-pw').addEventListener('click',  changePassword);
}

// ── Save Pharmacy Info ────────────────────────────────────
async function savePharmacyInfo() {
  const data = {
    name:    document.getElementById('p-name').value.trim(),
    owner:   document.getElementById('p-owner').value.trim(),
    phone:   document.getElementById('p-phone').value.trim(),
    email:   document.getElementById('p-email').value.trim(),
    city:    document.getElementById('p-city').value.trim(),
    address: document.getElementById('p-address').value.trim(),
    license: document.getElementById('p-license').value.trim(),
    ownerId: window.currentUser?.uid || 'demo'
  };

  if (!data.name || !data.owner) {
    showSectionMsg('info-msg', 'Pharmacy name and owner name are required.', 'error');
    return;
  }

  const btn = document.getElementById('btn-save-info');
  btn.disabled = true; btn.textContent = 'Saving…';

  try {
    if (!DEMO_MODE) {
      if (pharmacyDocId) {
        await db.collection('pharmacies').doc(pharmacyDocId).update(data);
      } else {
        const ref = await db.collection('pharmacies').add(data);
        pharmacyDocId = ref.id;
      }
    } else {
      await new Promise(r => setTimeout(r, 600));
    }
    showSectionMsg('info-msg', 'Pharmacy information saved successfully!', 'success');
  } catch (e) {
    showSectionMsg('info-msg', 'Error: ' + e.message, 'error');
  } finally {
    btn.disabled = false; btn.textContent = 'Save Information';
  }
}

// ── Save Working Hours ────────────────────────────────────
async function saveWorkingHours() {
  const hoursData = {};
  DAYS.forEach(day => {
    hoursData[day] = {
      open: document.getElementById('h-open-' + day)?.checked || false,
      from: document.getElementById('h-from-' + day)?.value || '08:00',
      to:   document.getElementById('h-to-'   + day)?.value || '20:00',
    };
  });

  const btn = document.getElementById('btn-save-hours');
  btn.disabled = true; btn.textContent = 'Saving…';

  try {
    if (!DEMO_MODE && pharmacyDocId) {
      await db.collection('pharmacies').doc(pharmacyDocId).update({ workingHours: hoursData });
    } else {
      await new Promise(r => setTimeout(r, 500));
    }
    showSectionMsg('hours-msg', 'Working hours saved successfully!', 'success');
  } catch (e) {
    showSectionMsg('hours-msg', 'Error: ' + e.message, 'error');
  } finally {
    btn.disabled = false; btn.textContent = 'Save Hours';
  }
}

// ── Change Password ───────────────────────────────────────
async function changePassword() {
  const currentPw = document.getElementById('pw-current').value;
  const newPw     = document.getElementById('pw-new').value;
  const confirmPw = document.getElementById('pw-confirm').value;

  clearPasswordFields();

  if (!currentPw || !newPw || !confirmPw) {
    showSectionMsg('pw-msg', 'Please fill in all password fields.', 'error');
    return;
  }
  if (newPw.length < 6) {
    showSectionMsg('pw-msg', 'New password must be at least 6 characters.', 'error');
    return;
  }
  if (newPw !== confirmPw) {
    showSectionMsg('pw-msg', 'New password and confirmation do not match.', 'error');
    document.getElementById('pw-confirm').classList.add('error');
    return;
  }

  const btn = document.getElementById('btn-change-pw');
  btn.disabled = true; btn.textContent = 'Updating…';

  try {
    if (DEMO_MODE) {
      await new Promise(r => setTimeout(r, 700));
      showSectionMsg('pw-msg', 'Password updated successfully! (Demo)', 'success');
      document.getElementById('pw-form').reset();
      return;
    }

    const user       = auth.currentUser;
    const credential = firebase.auth.EmailAuthProvider.credential(user.email, currentPw);
    await user.reauthenticateWithCredential(credential);
    await user.updatePassword(newPw);

    showSectionMsg('pw-msg', 'Password updated successfully!', 'success');
    document.getElementById('pw-form').reset();
  } catch (e) {
    const msg = e.code === 'auth/wrong-password'
      ? 'Current password is incorrect.'
      : 'Error: ' + e.message;
    showSectionMsg('pw-msg', msg, 'error');
  } finally {
    btn.disabled = false; btn.textContent = 'Update Password';
  }
}

function clearPasswordFields() {
  document.querySelectorAll('#pw-form .error').forEach(el => el.classList.remove('error'));
}

// ── Shared Helpers ────────────────────────────────────────
function showSectionMsg(id, msg, type) {
  const el = document.getElementById(id);
  if (!el) return;
  el.textContent = msg;
  el.className   = type === 'success' ? 'success-msg' : 'error-msg';
  clearTimeout(el._t);
  el._t = setTimeout(() => { if (type === 'success') { el.textContent = ''; el.className = ''; } }, 5000);
}
