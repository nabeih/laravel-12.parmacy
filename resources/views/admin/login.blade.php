<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PharmacyLink — دخول المدير</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/admin.css">
  <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-auth-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-firestore-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-database-compat.js"></script>
</head>
<body class="admin-page">
  <div class="admin-login-wrap">
    <div class="admin-login-card">
      <div class="admin-login-logo">
        <div class="admin-login-logo-icon">🔧</div>
        <div class="admin-login-logo-title">لوحة الإدارة</div>
        <div class="admin-login-logo-sub">PharmacyLink — مدير النظام</div>
      </div>

      <div class="admin-form-group">
        <label class="admin-form-label" for="admin-email">البريد الإلكتروني</label>
        <input class="admin-form-input" type="email" id="admin-email"
               placeholder="admin@pharmalink.com" autocomplete="email">
      </div>
      <div class="admin-form-group">
        <label class="admin-form-label" for="admin-pw">كلمة المرور</label>
        <input class="admin-form-input" type="password" id="admin-pw"
               placeholder="••••••••" autocomplete="current-password"
               onkeydown="if(event.key==='Enter') _doLogin()">
      </div>

      <button class="admin-login-btn" id="login-btn" onclick="_doLogin()">
        دخول إلى لوحة الإدارة
      </button>
      <div class="admin-login-error" id="login-error"></div>
      <div class="admin-login-back">
        <a href="../index.html">← العودة إلى الصفحة الرئيسية</a>
      </div>
    </div>
  </div>

  <script src="../js/firebase-config.js"></script>
  <script src="../js/mock-data.js"></script>
  <script src="../js/auth.js"></script>
  <script>
    async function _doLogin() {
      const email = document.getElementById('admin-email').value.trim();
      const pw    = document.getElementById('admin-pw').value;
      const errEl = document.getElementById('login-error');
      const btn   = document.getElementById('login-btn');

      errEl.textContent = '';
      if (!email || !pw) { errEl.textContent = 'يرجى إدخال البريد وكلمة المرور'; return; }

      if (DEMO_MODE) {
        btn.disabled = true; btn.textContent = 'جاري الدخول...';
        await new Promise(r => setTimeout(r, 700));
        window.location.href = 'dashboard.html';
        return;
      }

      loginUser(email, pw, 'admin', 'login-error', btn);
    }
  </script>
</body>
</html>
