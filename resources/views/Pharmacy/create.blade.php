{{-- @extends('layouts.nav_admin')
@section('title', 'إضافة صيدلية')
@section('content') --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>اضافة صيدلي</title>
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
            <h1 class="admin-page-title">إضافة صيدلية جديدة</h1>
            <p class="admin-page-sub">بيانات الصيدلية ومواعيد عملها</p>
        </div>
        <div>
            <a href="{{ route('pharmacy.index') }}" class="admin-btn admin-btn-outline">&larr; العودة إلى القائمة</a>
        </div>
    </div>

    <div class="admin-card">
        <form action="{{ route('pharmacy.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="admin-form-grid">
                <div class="admin-form-group">
                    <label class="admin-form-label">الصيدلي المسؤول *</label>
                    <select name="pharmacist_id" class="admin-form-select @error('pharmacist_id') error @enderror"
                        required>
                        <option value="">اختر الصيدلي</option>

                        @foreach ($pharmacists as $pharmacist)
                            <option value="{{ $pharmacist->id }}" {{ old('pharmacist_id') == $pharmacist->id ? 'selected' : '' }}>
                                {{ $pharmacist->users->name ?? ('صيدلي #' . $pharmacist->id) }}
                            </option>
                        @endforeach
                    </select>
                    @error('pharmacist_id')<span class="admin-form-error">{{ $message }}</span>@enderror
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">الاسم بالعربي *</label>
                    <input type="text" name="name_ar" class="admin-form-input @error('name_ar') error @enderror"
                        value="{{ old('name_ar') }}" required>
                    @error('name_ar')<span class="admin-form-error">{{ $message }}</span>@enderror
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">الاسم بالانجليزي *</label>
                    <input type="text" name="name_en" class="admin-form-input @error('name_en') error @enderror"
                        dir="ltr" value="{{ old('name_en') }}" required>
                    @error('name_en')<span class="admin-form-error">{{ $message }}</span>@enderror
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">الهاتف *</label>
                    <input type="text" name="phone" class="admin-form-input @error('phone') error @enderror" dir="ltr"
                        value="{{ old('phone') }}" required>
                    @error('phone')<span class="admin-form-error">{{ $message }}</span>@enderror
                </div>

                <div class="admin-form-group" style="grid-column: 1 / -1">
                    <label class="admin-form-label">العنوان *</label>
                    <input type="text" name="address" class="admin-form-input @error('address') error @enderror"
                        value="{{ old('address') }}" required>
                    @error('address')<span class="admin-form-error">{{ $message }}</span>@enderror
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">خط العرض (Latitude)</label>
                    <input type="number" step="0.00000001" name="latitude" class="admin-form-input" dir="ltr"
                        value="{{ old('latitude') }}">
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">خط الطول (Longitude)</label>
                    <input type="number" step="0.00000001" name="longitude" class="admin-form-input" dir="ltr"
                        value="{{ old('longitude') }}">
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">وقت الفتح</label>
                    <input type="time" name="opening_time" class="admin-form-input" value="{{ old('opening_time') }}">
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">وقت الإغلاق</label>
                    <input type="time" name="closing_time" class="admin-form-input" value="{{ old('closing_time') }}">
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">الحالة *</label>
                    <select name="status" class="admin-form-select @error('status') error @enderror" required>
                        <option value="opne" {{ old('status', 'opne') == 'opne' ? 'selected' : '' }}>مفتوحة</option>
                        <option value="closed" {{ old('status') == 'closed' ? 'selected' : '' }}>مغلقة</option>
                        <option value="suspended" {{ old('status') == 'suspended' ? 'selected' : '' }}>موقوفة</option>
                    </select>
                    @error('status')<span class="admin-form-error">{{ $message }}</span>@enderror
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">الشعار (Logo)</label>
                    <input type="file" name="logo" class="admin-form-input @error('logo') error @enderror">
                    @error('logo')<span class="admin-form-error">{{ $message }}</span>@enderror
                </div>

                <div class="admin-form-group" style="display:flex;align-items:center;padding-top:24px">
                    <label class="admin-form-check">
                        <input type="checkbox" name="is_verified" value="1" {{ old('is_verified') ? 'checked' : '' }}>
                        صيدلية موثقة
                    </label>
                </div>
            </div>

            <div class="admin-modal-footer" style="border-top:none;padding-top:0">
                <a href="{{ route('pharmacy.index') }}" class="admin-btn admin-btn-outline">إلغاء</a>
                <button type="submit" class="admin-btn admin-btn-primary">💾 حفظ</button>
            </div>
        </form>
    </div>
</body>

</html>
