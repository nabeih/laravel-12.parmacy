<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') PharmacyLink — لوحة تحكم المدير</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assest_pharmacy/css/admin.css') }}">
    <link href="{{ asset('assest_pharmacy/css/bootstrap.min.css') }}" rel="stylesheet">

    <script src="{{ asset('assest_pharmacy/js/bootstrap.bundle.min.js') }}"></script>
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
                {{-- <nav class="admin-nav">
                    <div class="admin-nav-label">القائمة الرئيسية</div>

                    <a href="dashboard.html" class="admin-nav-link">
                        <span>📊</span>
                        <span>لوحة التحكم</span>
                    </a>
                    <a href="pharmacies.html" class="admin-nav-link active">
                        <span>🏪</span>
                        <span>الصيدليات</span>
                    </a>
                    <a href="users.html" class="admin-nav-link">
                        <span>👥</span>
                        <span>المستخدمون</span>
                    </a>
                    <a href="#" class="admin-nav-link">
                        <span>💊</span>
                        <span>كتالوج الأدوية</span>
                    </a>
                    <a href="{{ route('category.index') }}" class="admin-nav-link">
                        <span>💊</span>
                        <span>اقسام الأدوية</span>
                    </a>
                    <a href="orders.html" class="admin-nav-link">
                        <span>📦</span>
                        <span>الطلبات</span>
                    </a>
                    <a href="reports.html" class="admin-nav-link">
                        <span>📈</span>
                        <span>التقارير</span>
                    </a>
                </nav> --}}
                @yield('content')
            </main>
        </div>
    </div>


    {{-- @yield('script') --}}
    <script src="{{ asset('assest_pharmacy/js/firebase-config.js') }}"></script>
    <script src="{{ asset('assest_pharmacy/js/mock-data.js') }}"></script>
    <script src="{{ asset('assest_pharmacy/js/auth.js') }}"></script>
    <script src="{{ asset('assest_pharmacy/js/admin/sidebar.js') }}"></script>
    <script src="{{ asset('assest_pharmacy/js/admin/dashboard.js') }}"></script>
    <script>initAuth('admin', () => { renderAdminSidebar(); initAdminDashboard(); });</script>

</body>

</html>
