{{-- @extends('layouts.nav_admin') --}}
{{-- @section('title', 'إضافة مستخدم')
@section('content') --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="{{ asset('assest_pharmacy/css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('assest_pharmacy/css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('assest_pharmacy/css/chat.css') }}">
    <link rel="stylesheet" href="{{ asset('assest_pharmacy/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assest_pharmacy/css/notifications.css') }}">
    <link rel="stylesheet" href="{{ asset('assest_pharmacy/css/sidebar.css') }}">
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-firestore-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-database-compat.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>

<body>
    <div class="admin-page-header">
        <div>
            <h1 class="admin-page-title">إضافة مستخدم جديد</h1>
            <p class="admin-page-sub">إنشاء حساب مستخدم جديد على المنصة</p>
        </div>
        <div>
            <a href="{{ route('user.index') }}" class="admin-btn admin-btn-outline">&larr; العودة إلى القائمة</a>
        </div>
    </div>

    <div class="admin-card">
        <form action="{{ route('user.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="admin-form-grid">
                <div class="admin-form-group">
                    <label class="admin-form-label">الاسم *</label>
                    <input type="text" name="name" class="admin-form-input @error('name') error @enderror"
                        value="{{ old('name') }}" required>
                    @error('name')<span class="admin-form-error">{{ $message }}</span>@enderror
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">البريد الإلكتروني *</label>
                    <input type="email" name="email" class="admin-form-input @error('email') error @enderror" dir="ltr"
                        value="{{ old('email') }}" required>
                    @error('email')<span class="admin-form-error">{{ $message }}</span>@enderror
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">كلمة المرور *</label>
                    <input type="password" name="password" class="admin-form-input @error('password') error @enderror"
                        required>
                    @error('password')<span class="admin-form-error">{{ $message }}</span>@enderror
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">تأكيد كلمة المرور *</label>
                    <input type="password" name="password_confirmation" class="admin-form-input" required>
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">الدور *</label>
                    <select name="role" class="admin-form-select @error('role') error @enderror" required>
                        <option value="customer" {{ old('role', 'customer') == 'customer' ? 'selected' : '' }}>عميل
                        </option>
                        <option value="pharmacist" {{ old('role') == 'pharmacist' ? 'selected' : '' }}>صيدلي</option>
                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>مدير</option>
                    </select>
                    @error('role')<span class="admin-form-error">{{ $message }}</span>@enderror
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">الصورة الشخصية</label>
                    <input type="file" name="avatar" class="admin-form-input @error('avatar') error @enderror">
                    @error('avatar')<span class="admin-form-error">{{ $message }}</span>@enderror
                </div>

                <div class="admin-form-group" style="display:flex;align-items:center;padding-top:24px">
                    <label class="admin-form-check">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        حساب فعال
                    </label>
                </div>
            </div>

            <div class="admin-modal-footer" style="border-top:none;padding-top:0">
                <a href="{{ route('user.index') }}" class="admin-btn admin-btn-outline">إلغاء</a>
                <button type="submit" class="admin-btn admin-btn-primary">💾 حفظ</button>
            </div>
        </form>
    </div>
</body>

</html>
