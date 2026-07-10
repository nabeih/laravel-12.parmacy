<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmacyLink — نظام إدارة الصيدليات</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assest_pharmacy/css/main.css') }}">
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-firestore-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-database-compat.js"></script>
</head>

<body>
    <div class="landing-page">
        <div class="landing-logo">
            <span class="logo-emoji">💊</span>
            <h1>PharmacyLink</h1>
            <p>نظام إدارة الصيدليات المتكامل</p>
        </div>

        <div class="role-cards">
            <!-- User Card -->
            <div class="role-card" onclick="window.location='user/login.html'">
                <div class="role-icon-wrap user-icon">👤</div>
                <h2>مستخدم</h2>
                <p>ابحث عن الأدوية وتابع طلباتك وجرعاتك</p>
                <ul class="role-features">
                    <li>بحث عن الأدوية</li>
                    <li>ماسح الروشتة الذكي</li>
                    <li>تتبع الطلبات</li>
                    <li>جدول الجرعات</li>
                    <li>استشارة صيدلانية بالذكاء الاصطناعي</li>
                </ul>
                <button class="btn btn-outline btn-full btn-lg">دخول كمستخدم</button>
            </div>

            <!-- Pharmacist Card -->
            <div class="role-card" onclick="window.location='pharmacist/login.html'">
                <div class="role-icon-wrap pharm-icon">🏪</div>
                <h2>صيدلاني</h2>
                <p>أدر صيدليتك ومخزونك ومبيعاتك بكفاءة</p>
                <ul class="role-features">
                    <li>إدارة المخزون</li>
                    <li>تتبع المبيعات والطلبات</li>
                    <li>تحليلات مفصلة</li>
                    <li>مبيعات QR الفوري</li>
                    <li>تواصل مع العملاء</li>
                </ul>
                <button class="btn btn-primary btn-full btn-lg">دخول كصيدلاني</button>
            </div>
        </div>

        <!-- Admin entry — small, less prominent -->
        <div style="margin-top:20px;text-align:center">
            <div class="role-card" onclick="window.location='admin/login.html'"
                style="display:inline-block;max-width:220px;padding:16px 20px;text-align:center;opacity:.75;cursor:pointer">
                <div class="role-icon-wrap"
                    style="background:#e2e8f0;font-size:20px;width:44px;height:44px;margin:0 auto 8px">🔧</div>
                <h2 style="font-size:14px;margin:0 0 4px">مدير النظام</h2>
                <p style="font-size:12px;margin:0;color:var(--text-muted)">لوحة الإدارة والإشراف</p>
            </div>
        </div>

        <p style="margin-top:20px;color:var(--text-muted);font-size:13px;text-align:center">
            PharmacyLink © 2025 — مشروع تخرج
        </p>
    </div>

    <script src="{{ asset('assest_pharmacy/js/firebase-config.js') }}"></script>
    <script src="{{ asset('assest_pharmacy/js/mock-data.js') }}"></script>
    <script src="{{ asset('assest_pharmacy/js/auth.js') }}"></script>
    <script>checkAuthAndRedirect();</script>
</body>

</html>
