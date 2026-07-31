<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') — بوابة المستخدم</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assest_pharmacy/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assest_pharmacy/css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('assest_pharmacy/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assest_pharmacy/css/chat.css') }}">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; }
        .portal-sidebar {
            width: 250px; background: #7e22ce; min-height: 100vh; position: fixed; right: 0; top: 0;
            display: flex; flex-direction: column; z-index: 100; transition: transform .25s ease;
        }
        .portal-main { margin-right: 250px; padding: 64px 28px 28px; transition: margin-right .25s ease; }
        .portal-nav-link { color: #f3e8ff; padding: 10px 16px; border-radius: 8px; display: block; text-decoration: none; margin-bottom: 4px; }
        .portal-nav-link:hover, .portal-nav-link.active { background: rgba(255,255,255,.15); color: #fff; }

        .portal-sidebar-toggle {
            display: flex; position: fixed; top: 14px; left: 14px; z-index: 110;
            width: 42px; height: 42px; align-items: center; justify-content: center;
            border-radius: 10px; border: none; background: #7e22ce; color: #fff;
            font-size: 20px; line-height: 1; cursor: pointer; box-shadow: 0 2px 10px rgba(0,0,0,.25);
        }
        .portal-sidebar-backdrop { display: none; position: fixed; inset: 0; background: rgba(15,23,42,.5); z-index: 99; }

        /* Explicitly hidden by the user — content reflows to fill the space at any screen size. */
        body.sidebar-hidden .portal-sidebar { transform: translateX(100%); }
        body.sidebar-hidden .portal-main { margin-right: 0; }

        @media (max-width: 768px) {
            /* Small screens: sidebar defaults to hidden and, when shown, overlays the content. */
            .portal-sidebar { transform: translateX(100%); }
            body:not(.sidebar-hidden) .portal-sidebar { transform: translateX(0); box-shadow: -8px 0 32px rgba(0,0,0,.25); }
            body:not(.sidebar-hidden) .portal-sidebar-backdrop { display: block; }
            .portal-main { margin-right: 0; padding: 64px 16px 16px; }
        }
    </style>
</head>

<body>
    <button type="button" id="sidebar-toggle" class="portal-sidebar-toggle" aria-label="إظهار/إخفاء القائمة الجانبية"
        aria-expanded="true" aria-controls="portal-sidebar" data-storage-key="userSidebarHidden">☰</button>
    <div id="sidebar-backdrop" class="portal-sidebar-backdrop"></div>
    @include('partials.notification-bell')

    <div class="d-flex flex-wrap">
        <aside class="portal-sidebar text-white p-3" id="portal-sidebar">
            <div class="mb-4">
                <div class="fw-bold fs-5">💊 PharmacyLink</div>
                <div class="small opacity-75">بوابة المستخدم</div>
            </div>
            <nav class="flex-grow-1">
                <a href="{{ route('user.dashboard') }}"
                    class="portal-nav-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}">🏠 الرئيسية</a>
                <a href="{{ route('user.search') }}"
                    class="portal-nav-link {{ request()->routeIs('user.search') ? 'active' : '' }}">🔍 الصيدليات
                    والأدوية</a>
                <a href="{{ route('user.scanner') }}"
                    class="portal-nav-link {{ request()->routeIs('user.scanner') ? 'active' : '' }}">🔍 مسح الروشتة</a>
                <a href="{{ route('user.assistant') }}"
                    class="portal-nav-link {{ request()->routeIs('user.assistant') ? 'active' : '' }}">🤖 المساعد الذكي</a>
                <a href="{{ route('user.doses') }}"
                    class="portal-nav-link {{ request()->routeIs('user.doses') ? 'active' : '' }}">💊 جرعاتي</a>
                <a href="{{ route('user.orders') }}"
                    class="portal-nav-link {{ request()->routeIs('user.orders') ? 'active' : '' }}">🛒 طلباتي</a>
            </nav>
            <div class="mt-auto pt-4 border-top border-light border-opacity-25">
                <div class="fw-semibold">{{ auth()->user()->name ?? 'مستخدم' }}</div>
                <div class="small opacity-75 mb-3">{{ auth()->user()->email ?? '' }}</div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-light btn-sm w-100">🚪 تسجيل الخروج</button>
                </form>
            </div>
        </aside>
        <main class="portal-main flex-grow-1">
            @yield('content')
        </main>
    </div>
    <script src="{{ asset('assest_pharmacy/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assest_pharmacy/js/sidebar-toggle.js') }}"></script>
    <script src="{{ asset('assest_pharmacy/js/notification-bell.js') }}"></script>
    <script src="{{ asset('assest_pharmacy/js/mock-data.js') }}"></script>
    <script>
        // Shared same-origin fetch helper: attaches the session CSRF token so
        // POST/PUT/DELETE calls from the portal pages pass Laravel's VerifyCsrfToken.
        async function phFetch(url, options = {}) {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const headers = {
                'Accept': 'application/json',
                ...(options.body && !(options.body instanceof FormData) ? { 'Content-Type': 'application/json' } : {}),
                ...(token ? { 'X-CSRF-TOKEN': token } : {}),
                ...(options.headers || {}),
            };
            const res = await fetch(url, { ...options, headers, credentials: 'same-origin' });
            let data = null;
            try { data = await res.json(); } catch (_) {}
            return { ok: res.ok, status: res.status, data };
        }
    </script>
    @stack('scripts')
</body>

</html>
