<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmacyLink — لوحة تحكم المدير</title>
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
    <div id="loading-overlay"
        style="position:fixed;inset:0;background:var(--admin-bg);display:flex;align-items:center;justify-content:center;z-index:9999">
        <div
            style="width:40px;height:40px;border:4px solid var(--admin-accent-light);border-top-color:var(--admin-accent);border-radius:50%;animation:spin .8s linear infinite">
        </div>
    </div>
    <div id="app-content" style="display:none">
        <div class="admin-layout">
            <div id="admin-sidebar-container"></div>
            <main class="admin-main">

                <div class="admin-page-header">
                    <div>
                        <h1 class="admin-page-title">لوحة التحكم</h1>
                        <p class="admin-page-sub" id="dash-date"></p>
                    </div>
                </div>

                <!-- Stat Cards -->
                <div class="admin-stats-grid" id="stat-cards"></div>

                <!-- Charts -->
                <div class="admin-charts-grid">
                    <div class="admin-card">
                        <div class="admin-card-title">📈 الطلبات اليومية — آخر 30 يوم</div>
                        <canvas id="ordersChart" height="200"></canvas>
                    </div>
                    <div class="admin-card">
                        <div class="admin-card-title">🏪 توزيع الصيدليات بالمدن</div>
                        <canvas id="citiesChart" height="200"></canvas>
                    </div>
                </div>

                <!-- Status distribution -->
                <div class="admin-card" style="margin-bottom:20px">
                    <div class="admin-card-title">📊 توزيع حالات الطلبات</div>
                    <div style="max-width:200px;margin:0 auto">
                        <canvas id="statusDistChart" height="110"></canvas>
                    </div>
                </div>

                <!-- Recent tables -->
                <div class="admin-tables-grid">
                    <div class="admin-card">
                        <div class="admin-card-title">📦 آخر الطلبات</div>
                        <div class="admin-table-wrap">
                            <table class="admin-table" id="recent-orders-tbl">
                                <thead>
                                    <tr>
                                        <th>رقم الطلب</th>
                                        <th>المستخدم</th>
                                        <th>الصيدلية</th>
                                        <th>الإجمالي</th>
                                        <th>الحالة</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="admin-card">
                        <div class="admin-card-title">🏪 صيدليات تنتظر الموافقة</div>
                        <div class="admin-table-wrap">
                            <table class="admin-table" id="pending-pharm-tbl">
                                <thead>
                                    <tr>
                                        <th>الاسم</th>
                                        <th>المدينة</th>
                                        <th>تاريخ الطلب</th>
                                        <th>إجراء</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <script src="../js/firebase-config.js"></script>
    <script src="../js/mock-data.js"></script>
    <script src="../js/auth.js"></script>
    <script src="../js/admin/sidebar.js"></script>
    <script src="../js/admin/dashboard.js"></script>
    <script>initAuth('admin', () => { renderAdminSidebar(); initAdminDashboard(); });</script>
</body>

</html>
