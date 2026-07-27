<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmacyLink — تسجيل مستخدم جديد</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assest_pharmacy/css/bootstrap.min.css') }}">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #7e22ce, #2563eb);
            min-height: 100vh;
        }

        .login-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, .25);
        }

        .login-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #f3e8ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            margin: 0 auto 12px;
        }

        .btn-user {
            background: #7e22ce;
            border-color: #7e22ce;
        }

        .btn-user:hover {
            background: #6b1bad;
            border-color: #6b1bad;
        }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center py-4">
    <div class="container" style="max-width: 460px;">
        <div class="card login-card p-4">
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="login-icon">👤</div>
                    <h4 class="fw-bold mb-1">تسجيل مستخدم جديد</h4>
                    <p class="text-muted small mb-0">PharmacyLink — ابحث عن الأدوية والصيدليات القريبة منك</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger py-2">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register.user.submit') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">الاسم الكامل</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">البريد الإلكتروني</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email') }}" placeholder="you@example.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">كلمة المرور</label>
                        <input type="password" name="password"
                            class="form-control @error('password') is-invalid @enderror" placeholder="••••••••"
                            required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">تأكيد كلمة المرور</label>
                        <input type="password" name="password_confirmation" class="form-control"
                            placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="btn btn-user w-100 text-white fw-semibold py-2">إنشاء الحساب</button>
                </form>

                <div class="text-center mt-3">
                    <a href="{{ route('login.user') }}" class="small text-decoration-none">&larr; لديك حساب بالفعل؟
                        دخول</a>
                </div>
                <div class="text-center mt-2">
                    <a href="{{ route('login') }}" class="small text-decoration-none">&larr; العودة إلى الصفحة
                        الرئيسية</a>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('assest_pharmacy/js/bootstrap.bundle.min.js') }}"></script>
</body>

</html>
