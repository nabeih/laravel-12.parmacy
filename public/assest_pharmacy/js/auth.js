// ── Auth Guard ─────────────────────────────────────────────
// requiredRole: 'pharmacist' | 'user' | null (for public pages)
// onReady(currentUser) called after auth resolves
function initAuth(requiredRole, onReady) {
  if (DEMO_MODE) {
    if (requiredRole === 'pharmacist') {
      window.currentUser = { ...mockPharmacistUser };
    } else if (requiredRole === 'user') {
      window.currentUser = { ...mockUser };
    } else if (requiredRole === 'admin') {
      window.currentUser = { ...mockAdminUser };
    }
    _showContent();
    if (typeof onReady === 'function') onReady(window.currentUser);
    return;
  }

  auth.onAuthStateChanged(async (firebaseUser) => {
    if (!firebaseUser) {
      if (requiredRole) { window.location.replace('login.html'); }
      return;
    }
    try {
      const snap = await db.collection('users').doc(firebaseUser.uid).get();
      const data = snap.data() || {};
      if (requiredRole && data.role !== requiredRole) {
        window.location.replace('../index.html');
        return;
      }
      window.currentUser = {
        uid:           firebaseUser.uid,
        email:         firebaseUser.email,
        emailVerified: firebaseUser.emailVerified,
        ...data
      };
      _showContent();
      if (typeof onReady === 'function') onReady(window.currentUser);
    } catch (err) {
      _showError('خطأ في التحقق من الهوية: ' + err.message);
    }
  });
}

// ── Landing Page Auth Check ────────────────────────────────
function checkAuthAndRedirect() {
  if (DEMO_MODE) return; // landing page stays visible in demo mode
  auth.onAuthStateChanged(async (user) => {
    if (!user) return;
    try {
      const snap = await db.collection('users').doc(user.uid).get();
      const role = snap.data()?.role;
      if (role === 'pharmacist') window.location.replace('pharmacist/dashboard.html');
      else if (role === 'user')  window.location.replace('user/dashboard.html');
      else if (role === 'admin') window.location.replace('admin/dashboard.html');
    } catch (_) {}
  });
}

// ── Login ──────────────────────────────────────────────────
async function loginUser(email, password, role, errorElId, btnEl) {
  if (DEMO_MODE) {
    _setBtn(btnEl, 'جاري الدخول...', true);
    await new Promise(r => setTimeout(r, 700));
    window.location.href = 'dashboard.html';
    return;
  }
  _setBtn(btnEl, 'جاري الدخول...', true);
  _clearMsg(errorElId);
  try {
    const cred = await auth.signInWithEmailAndPassword(email, password);
    if (!cred.user.emailVerified) {
      _showMsg(errorElId, 'يرجى تفعيل بريدك الإلكتروني أولاً. تحقق من بريدك الوارد.');
      await auth.signOut();
      return;
    }
    const snap = await db.collection('users').doc(cred.user.uid).get();
    if (snap.data()?.role !== role) {
      _showMsg(errorElId, 'هذا الحساب غير مصرح له بالدخول من هذه البوابة.');
      await auth.signOut();
      return;
    }
    window.location.href = 'dashboard.html';
  } catch (err) {
    _showMsg(errorElId, _firebaseErrAr(err.code));
  } finally {
    _setBtn(btnEl, role === 'pharmacist' ? 'تسجيل الدخول' : 'تسجيل الدخول', false);
  }
}

// ── Register ───────────────────────────────────────────────
async function registerUser(email, password, name, phone, errorElId, btnEl) {
  if (DEMO_MODE) {
    _setBtn(btnEl, 'جاري الإنشاء...', true);
    await new Promise(r => setTimeout(r, 800));
    window.location.href = 'verify-email.html';
    return;
  }
  _setBtn(btnEl, 'جاري الإنشاء...', true);
  _clearMsg(errorElId);
  try {
    const cred = await auth.createUserWithEmailAndPassword(email, password);
    await cred.user.sendEmailVerification();
    await db.collection('users').doc(cred.user.uid).set({
      role: 'user', name, email, phone,
      createdAt: firebase.firestore.FieldValue.serverTimestamp()
    });
    window.location.href = 'verify-email.html';
  } catch (err) {
    _showMsg(errorElId, _firebaseErrAr(err.code));
  } finally {
    _setBtn(btnEl, 'إنشاء حساب', false);
  }
}

// ── Logout ─────────────────────────────────────────────────
function logoutUser() {
  if (DEMO_MODE) { window.location.href = '../index.html'; return; }
  auth.signOut().then(() => window.location.href = '../index.html')
                .catch(() => window.location.href = '../index.html');
}

// ── Email verification ─────────────────────────────────────
async function resendVerificationEmail(statusElId, btnEl) {
  if (DEMO_MODE) { _showMsg(statusElId, 'تم إرسال الرابط (وضع عرض)', 'success'); return; }
  _setBtn(btnEl, '...', true);
  try {
    const user = auth.currentUser;
    if (user) {
      await user.sendEmailVerification();
      _showMsg(statusElId, 'تم إرسال رابط التفعيل إلى بريدك', 'success');
      _startCooldown(btnEl, 60);
    }
  } catch (err) {
    _showMsg(statusElId, _firebaseErrAr(err.code));
  }
}

async function checkIfVerified() {
  if (DEMO_MODE) { window.location.href = 'dashboard.html'; return; }
  try {
    await auth.currentUser?.reload();
    if (auth.currentUser?.emailVerified) {
      window.location.href = 'dashboard.html';
    } else {
      alert('لم يتم تفعيل البريد بعد. يرجى التحقق من بريدك الوارد والسبام.');
    }
  } catch (e) { alert('حدث خطأ، حاول مجدداً'); }
}

// ── Password reset ─────────────────────────────────────────
async function sendPasswordReset(email, msgElId) {
  if (DEMO_MODE) { _showMsg(msgElId, 'تم إرسال رابط إعادة التعيين (وضع عرض)', 'success'); return; }
  try {
    await auth.sendPasswordResetEmail(email);
    _showMsg(msgElId, 'تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك.', 'success');
  } catch (err) {
    _showMsg(msgElId, _firebaseErrAr(err.code));
  }
}

// ── Change password (reauthenticate first) ─────────────────
async function changePassword(currentPw, newPw, msgElId, btnEl) {
  _setBtn(btnEl, 'جاري التحديث...', true);
  try {
    if (DEMO_MODE) {
      await new Promise(r => setTimeout(r, 600));
      _showMsg(msgElId, 'تم تحديث كلمة المرور بنجاح! (وضع عرض)', 'success');
      return;
    }
    const user       = auth.currentUser;
    const credential = firebase.auth.EmailAuthProvider.credential(user.email, currentPw);
    await user.reauthenticateWithCredential(credential);
    await user.updatePassword(newPw);
    _showMsg(msgElId, 'تم تحديث كلمة المرور بنجاح!', 'success');
  } catch (err) {
    const msg = err.code === 'auth/wrong-password'
      ? 'كلمة المرور الحالية غير صحيحة.'
      : _firebaseErrAr(err.code);
    _showMsg(msgElId, msg);
  } finally {
    _setBtn(btnEl, 'تحديث كلمة المرور', false);
  }
}

// ── Private helpers ────────────────────────────────────────
function _showContent() {
  const ov = document.getElementById('loading-overlay');
  const ap = document.getElementById('app-content');
  if (ov) ov.style.display = 'none';
  if (ap) ap.style.display = 'block';
}
function _showError(msg) {
  const ov = document.getElementById('loading-overlay');
  if (ov) ov.innerHTML = `<div style="color:#ef4444;text-align:center;padding:24px;font-size:15px">${msg}</div>`;
}
function _setBtn(btn, txt, disabled) {
  if (!btn) return;
  btn.textContent = txt;
  btn.disabled    = disabled;
}
function _showMsg(id, msg, type) {
  const el = document.getElementById(id);
  if (!el) return;
  el.textContent = msg;
  el.className   = type === 'success' ? 'success-msg' : 'error-msg';
}
function _clearMsg(id) {
  const el = document.getElementById(id);
  if (el) { el.textContent = ''; el.className = ''; }
}
function _startCooldown(btn, secs) {
  let t = secs;
  const tick = () => {
    if (t <= 0) { btn.disabled = false; btn.textContent = 'إعادة إرسال الرابط'; return; }
    btn.textContent = `إعادة الإرسال (${t--}ث)`;
    setTimeout(tick, 1000);
  };
  tick();
}
function _firebaseErrAr(code) {
  const map = {
    'auth/user-not-found':        'لا يوجد حساب بهذا البريد الإلكتروني.',
    'auth/wrong-password':        'كلمة المرور غير صحيحة.',
    'auth/invalid-email':         'صيغة البريد الإلكتروني غير صحيحة.',
    'auth/too-many-requests':     'كثرة المحاولات الفاشلة. حاول لاحقاً.',
    'auth/email-already-in-use':  'هذا البريد الإلكتروني مسجّل مسبقاً.',
    'auth/weak-password':         'كلمة المرور قصيرة جداً. يجب أن تكون 6 أحرف على الأقل.',
    'auth/invalid-credential':    'البريد أو كلمة المرور غير صحيحة.',
    'auth/network-request-failed':'خطأ في الاتصال بالإنترنت.',
    'auth/user-disabled':         'تم تعطيل هذا الحساب.',
  };
  return map[code] || `حدث خطأ (${code}). حاول مجدداً.`;
}
