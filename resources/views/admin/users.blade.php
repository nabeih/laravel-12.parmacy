<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PharmacyLink — إدارة المستخدمين</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/admin.css">
  <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-auth-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-firestore-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-database-compat.js"></script>
</head>
<body class="admin-page">
  <div id="loading-overlay" style="position:fixed;inset:0;background:var(--admin-bg);display:flex;align-items:center;justify-content:center;z-index:9999">
    <div style="width:40px;height:40px;border:4px solid var(--admin-accent-light);border-top-color:var(--admin-accent);border-radius:50%;animation:spin .8s linear infinite"></div>
  </div>
  <div id="app-content" style="display:none">
    <div class="admin-layout">
      <div id="admin-sidebar-container"></div>
      <main class="admin-main">

        <div class="admin-page-header">
          <div>
            <h1 class="admin-page-title">إدارة المستخدمين</h1>
            <p class="admin-page-sub">عرض وإدارة جميع المستخدمين المسجلين</p>
          </div>
          <button class="admin-btn admin-btn-outline" onclick="_exportUsers()">⬇ تصدير CSV</button>
        </div>

        <!-- Stats bar -->
        <div class="admin-stats-grid" style="margin-bottom:20px" id="user-stats"></div>

        <div class="admin-card">
          <div class="admin-toolbar">
            <input class="admin-search" id="user-search" placeholder="ابحث بالاسم أو البريد..." oninput="_onUserSearch()">
            <select class="admin-select" id="user-status-filter" onchange="_onUserFilter()">
              <option value="">جميع الحالات</option>
              <option value="active">نشط</option>
              <option value="blocked">محظور</option>
            </select>
            <select class="admin-select" id="user-city-filter" onchange="_onUserFilter()">
              <option value="">جميع المدن</option>
              <option>نابلس</option><option>رام الله</option><option>جنين</option>
              <option>الخليل</option><option>طولكرم</option><option>بيت لحم</option>
            </select>
          </div>

          <div class="admin-table-wrap">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>المستخدم</th>
                  <th>رقم الهاتف</th>
                  <th>المدينة</th>
                  <th>الطلبات</th>
                  <th>تاريخ الانضمام</th>
                  <th>آخر نشاط</th>
                  <th>الحالة</th>
                  <th>إجراءات</th>
                </tr>
              </thead>
              <tbody id="user-tbody"></tbody>
            </table>
          </div>
          <div id="user-pagination" class="admin-pagination"></div>
        </div>

      </main>
    </div>
  </div>

  <!-- View modal -->
  <div class="admin-modal-overlay" id="user-view-modal">
    <div class="admin-modal">
      <div class="admin-modal-title">تفاصيل المستخدم</div>
      <button class="admin-modal-close" onclick="_closeModal('user-view-modal')">✕</button>
      <div id="user-view-body"></div>
      <div class="admin-modal-footer">
        <button class="admin-btn admin-btn-outline" onclick="_closeModal('user-view-modal')">إغلاق</button>
      </div>
    </div>
  </div>

  <!-- Delete confirm -->
  <div class="admin-modal-overlay" id="user-delete-modal">
    <div class="admin-modal" style="max-width:420px">
      <div class="admin-modal-title">⚠️ حذف المستخدم</div>
      <button class="admin-modal-close" onclick="_closeModal('user-delete-modal')">✕</button>
      <p style="color:#374151;margin:0 0 12px">لحذف المستخدم <strong id="user-delete-name"></strong>، اكتب <code>CONFIRM</code> أدناه:</p>
      <input class="admin-form-input" id="user-delete-confirm-input" placeholder="CONFIRM" dir="ltr" oninput="_checkDeleteInput()">
      <div class="admin-modal-footer">
        <button class="admin-btn admin-btn-outline" onclick="_closeModal('user-delete-modal')">إلغاء</button>
        <button class="admin-btn admin-btn-danger" id="user-delete-btn" disabled onclick="_doDeleteUser()">حذف نهائياً</button>
      </div>
    </div>
  </div>

  <script src="../js/firebase-config.js"></script>
  <script src="../js/mock-data.js"></script>
  <script src="../js/auth.js"></script>
  <script src="../js/admin/sidebar.js"></script>
  <script src="../js/admin/users.js"></script>
  <script>initAuth('admin', () => { renderAdminSidebar(); initAdminUsers(); });</script>
</body>
</html>
