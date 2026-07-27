<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title' )Pharmacy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assest_pharmacy/css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('assest_pharmacy/css/chat.css') }}">
    <link rel="stylesheet" href="{{ asset('assest_pharmacy/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assest_pharmacy/css/notifications.css') }}">
    <link rel="stylesheet" href="{{ asset('assest_pharmacy/css/sidebar.css') }}">
</head>

<body class="admin-page">
    <button type="button" id="sidebar-toggle" class="admin-sidebar-toggle" aria-label="إظهار/إخفاء القائمة الجانبية"
        aria-expanded="true" aria-controls="admin-sidebar" data-storage-key="adminSidebarHidden">☰</button>
    <div id="sidebar-backdrop" class="admin-sidebar-backdrop"></div>
    @include('partials.notification-bell')

    <main class="admin-main">
        <aside class="admin-sidebar" id="admin-sidebar">
            <div class="admin-logo">
                <div class="admin-logo-text">💊 PharmacyLink</div>
                <div class="admin-logo-sub">لوحة إدارة النظام</div>
            </div>
            <nav class="admin-nav">
                <div class="admin-nav-label">القائمة الرئيسية</div>

                <a href="{{ route('admin.dash') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.dash') ? 'active' : '' }}">
                    <span>📊</span>
                    <span>لوحة التحكم</span>
                </a>
                <a href="{{ route('user.index') }}"
                    class="admin-nav-link {{ request()->routeIs('user.index') || request()->routeIs('user.create') || request()->routeIs('user.store') ? 'active' : '' }}">
                    <span>👥</span>
                    <span>المستخدمون</span>
                </a>

                <div class="admin-nav-label">كتالوج الأدوية</div>
                <a href="{{ route('medicine.index') }}"
                    class="admin-nav-link {{ request()->routeIs('medicine.*') ? 'active' : '' }}">
                    <span>💊</span>
                    <span>كتالوج الأدوية</span>
                </a>
                <a href="{{ route('category.index') }}"
                    class="admin-nav-link {{ request()->routeIs('category.*') ? 'active' : '' }}">
                    <span>🗂️</span>
                    <span>اقسام الادوية</span>
                </a>
                <a href="{{ route('admin.manufacturer') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.manufacturer*') ? 'active' : '' }}">
                    <span>🏭</span>
                    <span> الشركات المصنعة</span>
                </a>
                <a href="{{ route('activeingredient.index') }}"
                    class="admin-nav-link {{ request()->routeIs('activeingredient.*') ? 'active' : '' }}">
                    <span>🧪</span>
                    <span> المواد الفعالة </span>
                </a>
                <a href="{{ route('dosage_form.index') }}"
                    class="admin-nav-link {{ request()->routeIs('dosage_form.*') ? 'active' : '' }}">
                    <span>📐</span>
                    <span> شكل الدواء </span>
                </a>
                <a href="{{ route('admin.medicine_request.index') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.medicine_request.*') ? 'active' : '' }}">
                    <span>📋</span>
                    <span> طلبات الأدوية </span>
                </a>

                <div class="admin-nav-label">الصيدليات والصيادلة</div>
                <a href="{{ route('pharmacy.index') }}"
                    class="admin-nav-link {{ request()->routeIs('pharmacy.*') ? 'active' : '' }}">
                    <span>🏪</span>
                    <span>الصيدليات</span>
                </a>
                <a href="{{ route('admin.pharmacy_request.index') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.pharmacy_request.*') ? 'active' : '' }}">
                    <span>📋</span>
                    <span>طلبات الصيدليات</span>
                </a>
                <a href="{{ route('pharmacist.index') }}"
                    class="admin-nav-link {{ request()->routeIs('pharmacist.index') || request()->routeIs('pharmacist.create') || request()->routeIs('pharmacist.store') ? 'active' : '' }}">
                    <span>🧑‍⚕️</span>
                    <span>الصيادلة</span>
                </a>
            </nav>
            <div class="admin-user-section">
                <div class="admin-user-name">{{ auth()->user()->name ?? 'مدير النظام' }}</div>
                <div class="admin-user-email">{{ auth()->user()->email ?? '' }}</div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="admin-logout-btn">🔓 تسجيل الخروج</button>
                </form>
            </div>
        </aside>
        @yield('content')
    </main>

    <script src="{{ asset('assest_pharmacy/js/admin/sidebar.js') }}"></script>
    <script src="{{ asset('assest_pharmacy/js/sidebar-toggle.js') }}"></script>
    <script src="{{ asset('assest_pharmacy/js/notification-bell.js') }}"></script>
</body>

</html>
