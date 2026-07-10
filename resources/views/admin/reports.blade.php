<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PharmacyLink — التقارير والإحصاءات</title>
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
            <h1 class="admin-page-title">التقارير والإحصاءات</h1>
            <p class="admin-page-sub">تحليل أداء المنصة والمبيعات</p>
          </div>
          <button class="admin-btn admin-btn-outline" onclick="_exportAllReports()">⬇ تصدير جميع التقارير</button>
        </div>

        <!-- Date range -->
        <div class="admin-card" style="margin-bottom:20px">
          <div class="admin-toolbar" style="margin:0">
            <div class="admin-date-range">
              <label style="font-size:13px;font-weight:600;color:#374151">من:</label>
              <input type="date" class="admin-date-input" id="rpt-from">
              <label style="font-size:13px;font-weight:600;color:#374151">إلى:</label>
              <input type="date" class="admin-date-input" id="rpt-to">
              <button class="admin-btn admin-btn-primary admin-btn-sm" onclick="_applyDateRange()">تطبيق</button>
            </div>
            <div style="display:flex;gap:6px;flex-wrap:wrap">
              <button class="admin-quick-btn active" id="qb-7"   onclick="_quickRange(7,this)">آخر 7 أيام</button>
              <button class="admin-quick-btn" id="qb-30"  onclick="_quickRange(30,this)">آخر 30 يوم</button>
              <button class="admin-quick-btn" id="qb-90"  onclick="_quickRange(90,this)">آخر 3 أشهر</button>
              <button class="admin-quick-btn" id="qb-365" onclick="_quickRange(365,this)">هذا العام</button>
            </div>
          </div>
        </div>

        <!-- Summary cards -->
        <div class="admin-stats-grid" id="rpt-summary" style="margin-bottom:20px"></div>

        <!-- Charts row 1 -->
        <div class="admin-charts-grid">
          <div class="admin-card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
              <div class="admin-card-title" style="margin:0;border:none;padding:0">📦 الطلبات اليومية</div>
              <button class="admin-btn admin-btn-outline admin-btn-sm" onclick="_exportDailyOrders()">⬇ CSV</button>
            </div>
            <canvas id="dailyOrdersChart" height="200"></canvas>
          </div>
          <div class="admin-card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
              <div class="admin-card-title" style="margin:0;border:none;padding:0">💰 الإيرادات الشهرية</div>
              <button class="admin-btn admin-btn-outline admin-btn-sm" onclick="_exportMonthlyRevenue()">⬇ CSV</button>
            </div>
            <canvas id="monthlyRevenueChart" height="200"></canvas>
          </div>
        </div>

        <!-- Charts row 2 -->
        <div class="admin-charts-grid">
          <div class="admin-card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
              <div class="admin-card-title" style="margin:0;border:none;padding:0">📊 توزيع حالات الطلبات</div>
              <button class="admin-btn admin-btn-outline admin-btn-sm" onclick="_exportStatusDist()">⬇ CSV</button>
            </div>
            <canvas id="statusDistChart" height="220"></canvas>
          </div>
          <div class="admin-card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
              <div class="admin-card-title" style="margin:0;border:none;padding:0">🏆 أعلى 5 صيدليات بالإيرادات</div>
              <button class="admin-btn admin-btn-outline admin-btn-sm" onclick="_exportTopPharm()">⬇ CSV</button>
            </div>
            <canvas id="topPharmChart" height="220"></canvas>
          </div>
        </div>

        <!-- Charts row 3 -->
        <div class="admin-card">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
            <div class="admin-card-title" style="margin:0;border:none;padding:0">👥 نمو المستخدمين الشهري</div>
            <button class="admin-btn admin-btn-outline admin-btn-sm" onclick="_exportUserGrowth()">⬇ CSV</button>
          </div>
          <canvas id="userGrowthChart" height="140"></canvas>
        </div>

      </main>
    </div>
  </div>

  <script src="../js/firebase-config.js"></script>
  <script src="../js/mock-data.js"></script>
  <script src="../js/auth.js"></script>
  <script src="../js/admin/sidebar.js"></script>
  <script src="../js/admin/reports.js"></script>
  <script>initAuth('admin', () => { renderAdminSidebar(); initAdminReports(); });</script>
</body>
</html>
