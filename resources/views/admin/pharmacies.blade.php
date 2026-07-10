<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PharmacyLink — إدارة الصيدليات</title>
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
            <h1 class="admin-page-title">إدارة الصيدليات</h1>
            <p class="admin-page-sub">إدارة جميع الصيدليات المسجلة في المنصة</p>
          </div>
          <button class="admin-btn admin-btn-primary" onclick="_openModal('pharm-form-modal')">+ إضافة صيدلية</button>
        </div>

        <div class="admin-card">
          <div class="admin-toolbar">
            <input class="admin-search" id="pharm-search" placeholder="ابحث بالاسم أو المالك..." oninput="_onSearch()">
            <select class="admin-select" id="pharm-status-filter" onchange="_onFilter()">
              <option value="">جميع الحالات</option>
              <option value="active">نشط</option>
              <option value="pending">بانتظار الموافقة</option>
              <option value="suspended">موقوف</option>
            </select>
            <button class="admin-btn admin-btn-outline" onclick="_exportPharm()">⬇ تصدير CSV</button>
          </div>

          <div class="admin-table-wrap">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>الصيدلية</th>
                  <th>صاحب الترخيص</th>
                  <th>المدينة</th>
                  <th>الطلبات</th>
                  <th>الإيرادات</th>
                  <th>الحالة</th>
                  <th>تاريخ الانضمام</th>
                  <th>إجراءات</th>
                </tr>
              </thead>
              <tbody id="pharm-tbody"></tbody>
            </table>
          </div>
          <div id="pharm-pagination" class="admin-pagination"></div>
        </div>

      </main>
    </div>
  </div>

  <!-- View Modal -->
  <div class="admin-modal-overlay" id="pharm-view-modal">
    <div class="admin-modal">
      <div class="admin-modal-title">تفاصيل الصيدلية</div>
      <button class="admin-modal-close" onclick="_closeModal('pharm-view-modal')">✕</button>
      <div id="pharm-view-body"></div>
      <div class="admin-modal-footer">
        <button class="admin-btn admin-btn-outline" onclick="_closeModal('pharm-view-modal')">إغلاق</button>
      </div>
    </div>
  </div>

  <!-- Add/Edit Modal -->
  <div class="admin-modal-overlay" id="pharm-form-modal">
    <div class="admin-modal">
      <div class="admin-modal-title" id="pharm-form-title">إضافة صيدلية جديدة</div>
      <button class="admin-modal-close" onclick="_closeModal('pharm-form-modal')">✕</button>
      <div class="admin-form-grid">
        <div class="admin-form-group">
          <label class="admin-form-label">اسم الصيدلية *</label>
          <input class="admin-form-input" id="pf-name" placeholder="صيدلية الشفاء">
        </div>
        <div class="admin-form-group">
          <label class="admin-form-label">صاحب الترخيص *</label>
          <input class="admin-form-input" id="pf-owner" placeholder="د. علي حسن">
        </div>
        <div class="admin-form-group">
          <label class="admin-form-label">المدينة *</label>
          <select class="admin-form-select" id="pf-city">
            <option>نابلس</option><option>رام الله</option><option>جنين</option>
            <option>الخليل</option><option>طولكرم</option><option>بيت لحم</option>
            <option>قلقيلية</option><option>أريحا</option>
          </select>
        </div>
        <div class="admin-form-group">
          <label class="admin-form-label">رقم الهاتف *</label>
          <input class="admin-form-input" id="pf-phone" placeholder="0599-XXXXXX" dir="ltr">
        </div>
        <div class="admin-form-group">
          <label class="admin-form-label">رقم الترخيص</label>
          <input class="admin-form-input" id="pf-license" placeholder="LIC-XXX" dir="ltr">
        </div>
        <div class="admin-form-group">
          <label class="admin-form-label">الحالة</label>
          <select class="admin-form-select" id="pf-status">
            <option value="pending">بانتظار الموافقة</option>
            <option value="active">نشط</option>
            <option value="suspended">موقوف</option>
          </select>
        </div>
      </div>
      <div class="admin-modal-footer">
        <button class="admin-btn admin-btn-outline" onclick="_closeModal('pharm-form-modal')">إلغاء</button>
        <button class="admin-btn admin-btn-primary" onclick="_savePharm()">حفظ</button>
      </div>
    </div>
  </div>

  <!-- Delete Confirm Modal -->
  <div class="admin-modal-overlay" id="pharm-delete-modal">
    <div class="admin-modal" style="max-width:420px">
      <div class="admin-modal-title">⚠️ تأكيد الحذف</div>
      <button class="admin-modal-close" onclick="_closeModal('pharm-delete-modal')">✕</button>
      <p style="color:#374151;margin:0 0 20px">هل أنت متأكد من حذف <strong id="pharm-delete-name"></strong>؟ لا يمكن التراجع عن هذه العملية.</p>
      <div class="admin-modal-footer">
        <button class="admin-btn admin-btn-outline" onclick="_closeModal('pharm-delete-modal')">إلغاء</button>
        <button class="admin-btn admin-btn-danger" id="pharm-delete-confirm-btn">حذف نهائياً</button>
      </div>
    </div>
  </div>

  <script src="../js/firebase-config.js"></script>
  <script src="../js/mock-data.js"></script>
  <script src="../js/auth.js"></script>
  <script src="../js/admin/sidebar.js"></script>
  <script src="../js/admin/pharmacies.js"></script>
  <script>initAuth('admin', () => { renderAdminSidebar(); initAdminPharmacies(); });</script>
</body>
</html>
