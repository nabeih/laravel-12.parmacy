<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmacyLink — تسجيل صيدلي جديد</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assest_pharmacy/css/bootstrap.min.css') }}">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f766e, #22c55e);
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
            background: #d1fae5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            margin: 0 auto 12px;
        }

        .btn-pharmacist {
            background: #0f766e;
            border-color: #0f766e;
        }

        .btn-pharmacist:hover {
            background: #0c5c56;
            border-color: #0c5c56;
        }

        .section-title {
            font-weight: 600;
            font-size: 14px;
            color: #0f766e;
            margin: 20px 0 10px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center py-4">
    <div class="container" style="max-width: 560px;">
        <div class="card login-card p-4">
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="login-icon">🧑‍⚕️</div>
                    <h4 class="fw-bold mb-1">تسجيل صيدلي جديد</h4>
                    <p class="text-muted small mb-0">PharmacyLink — سيتم مراجعة طلبك من قبل الإدارة بعد التسجيل</p>
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

                <form method="POST" action="{{ route('register.pharmacist.submit') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">الاسم الكامل</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" required autofocus>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">البريد الإلكتروني</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">كلمة المرور</label>
                            <input type="password" name="password"
                                class="form-control @error('password') is-invalid @enderror" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">تأكيد كلمة المرور</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>

                    <div class="section-title">🎓 بيانات الاعتماد المهني</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">الرقم الوطني</label>
                            <input type="text" name="national_id"
                                class="form-control @error('national_id') is-invalid @enderror"
                                value="{{ old('national_id') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">رقم النقابة</label>
                            <input type="text" name="syndicate_number"
                                class="form-control @error('syndicate_number') is-invalid @enderror"
                                value="{{ old('syndicate_number') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">رقم الترخيص</label>
                            <input type="text" name="license_number"
                                class="form-control @error('license_number') is-invalid @enderror"
                                value="{{ old('license_number') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">سنة التخرج</label>
                            <input type="number" name="graduation_year"
                                class="form-control @error('graduation_year') is-invalid @enderror"
                                value="{{ old('graduation_year') }}" min="1950" max="{{ date('Y') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">جامعة التخرج</label>
                            <input type="text" name="graduation_university"
                                class="form-control @error('graduation_university') is-invalid @enderror"
                                value="{{ old('graduation_university') }}" required>
                        </div>
                    </div>

                    <div class="section-title">📎 المستندات الرسمية</div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">ملف الشهادة</label>
                            <input type="file" name="certificate_file"
                                class="form-control @error('certificate_file') is-invalid @enderror" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">ملف النقابة</label>
                            <input type="file" name="syndicate_file"
                                class="form-control @error('syndicate_file') is-invalid @enderror" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">ملف الترخيص</label>
                            <input type="file" name="license_file"
                                class="form-control @error('license_file') is-invalid @enderror" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">ملاحظات (اختياري)</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-pharmacist w-100 text-white fw-semibold py-2 mt-4">إنشاء
                        الحساب وإرسال الطلب</button>
                </form>

                <div class="text-center mt-3">
                    <a href="{{ route('login.pharmacist') }}" class="small text-decoration-none">&larr; لديك حساب
                        بالفعل؟ دخول</a>
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
