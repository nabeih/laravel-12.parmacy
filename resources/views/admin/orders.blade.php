<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PharmacyLink — جميع الطلبات</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/admin.css">
  <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-auth-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-firestore-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-database-compat.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
            <h1 class="admin-page-title">جميع الطلبات</h1>
            <p class="admin-page-sub">عرض وإشراف على طلبات جميع الصيدليات</p>
          </div>
          <button class="admin-btn admin-btn-outline" onclick="_exportOrders()">⬇ تصدير CSV</button>
        </div>

        <!-- Status distribution mini-chart -->
        <div style="display:flex;align-items:center;gap:20px;margin-bottom:16px">
          <div style="width:110px;height:110px;flex-shrink:0">
            <canvas id="ord-status-chart"></canvas>
          </div>
          <div id="ord-status-legend" style="font-size:12px;line-height:1.9;color:#374151"></div>
        </div>

        <div class="admin-card">
          <div class="admin-toolbar">
            <input class="admin-search" id="ord-search" placeholder="ابحث برقم الطلب أو المستخدم..." oninput="_onOrdSearch()">
            <select class="admin-select" id="ord-status" onchange="_onOrdFilter()">
              <option value="">جميع الحالات</option>
              <option value="pending">قيد الانتظار</option>
              <option value="confirmed">مؤكد</option>
              <option value="shipping">جارٍ التوصيل</option>
              <option value="delivered">تم التسليم</option>
              <option value="cancelled">ملغى</option>
            </select>
            <select class="admin-select" id="ord-pharmacy" onchange="_onOrdFilter()">
              <option value="">جميع الصيدليات</option>
            </select>
            <label class="admin-form-check" style="font-size:13px;white-space:nowrap">
              <input type="checkbox" id="ord-flagged-only" onchange="_onOrdFilter()"> الطلبات المُبلَّغة فقط
            </label>
          </div>

          <div class="admin-table-wrap">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>رقم الطلب</th>
                  <th>المستخدم</th>
                  <th>الصيدلية</th>
                  <th>الإجمالي</th>
                  <th>طريقة الدفع</th>
                  <th>الحالة</th>
                  <th>التاريخ</th>
                  <th>إجراءات</th>
                </tr>
              </thead>
              <tbody id="ord-tbody"></tbody>
            </table>
          </div>
          <div id="ord-pagination" class="admin-pagination"></div>
        </div>

      </main>
    </div>
  </div>

  <!-- Order details modal -->
  <div class="admin-modal-overlay" id="ord-view-modal">
    <div class="admin-modal">
      <div class="admin-modal-title" id="ord-view-title">تفاصيل الطلب</div>
      <button class="admin-modal-close" onclick="_closeModal('ord-view-modal')">✕</button>
      <div id="ord-view-body"></div>
      <div class="admin-modal-footer">
        <button class="admin-btn admin-btn-outline" onclick="_closeModal('ord-view-modal')">إغلاق</button>
      </div>
    </div>
  </div>

  <script src="../js/firebase-config.js"></script>
  <script src="../js/mock-data.js"></script>
  <script src="../js/auth.js"></script>
  <script src="../js/admin/sidebar.js"></script>
  <script src="../js/admin/orders.js"></script>
  <script>initAuth('admin', () => { renderAdminSidebar(); initAdminOrders(); });</script>
</body>
</html>
