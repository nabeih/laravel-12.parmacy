<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmacyLink — دخول المدير</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assest_pharmacy/css/bootstrap.min.css') }}">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1e3a5f, #2563eb);
            min-height: 100vh;
        }
        .login-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,.25);
        }
        .login-icon {
            width: 64px; height: 64px; border-radius: 50%;
            background: #dbeafe; display: flex; align-items: center; justify-content: center;
            font-size: 30px; margin: 0 auto 12px;
        }
        .btn-manager { background: #1e3a5f; border-color: #1e3a5f; }
        .btn-manager:hover { background: #162d4a; border-color: #162d4a; }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center">
    <div class="container" style="max-width: 420px;">
        <div class="card login-card p-4">
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="login-icon">🔧</div>
                    <h4 class="fw-bold mb-1">لوحة الإدارة</h4>
                    <p class="text-muted small mb-0">PharmacyLink — دخول المدير</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger py-2">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.manager.submit') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">البريد الإلكتروني</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email') }}" placeholder="admin@pharmalink.com" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">كلمة المرور</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember">تذكرني</label>
                    </div>
                    <button type="submit" class="btn btn-manager w-100 text-white fw-semibold py-2">دخول إلى لوحة الإدارة</button>
                </form>

                <div class="text-center mt-3">
                    <a href="{{ route('login') }}" class="small text-decoration-none">&larr; العودة إلى الصفحة الرئيسية</a>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('assest_pharmacy/js/bootstrap.bundle.min.js') }}"></script>
</body>

</html>
