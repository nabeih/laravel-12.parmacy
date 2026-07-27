@extends('layouts.pharmacist')
@section('title', 'تسجيل صيدلية')
@section('content')

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">تسجيل صيدلية جديدة</h1>
        <p class="admin-page-sub">سيتم إرسال الطلب إلى الإدارة لمراجعته والموافقة عليه</p>
    </div>
    <div>
        <a href="{{ route('pharmacy_request.index') }}" class="admin-btn admin-btn-outline">&larr; رجوع</a>
    </div>
</div>

<div class="admin-card">
    <form action="{{ route('pharmacy_request.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="admin-form-grid">
            <div class="admin-form-group">
                <label class="admin-form-label">الاسم بالعربي *</label>
                <input type="text" name="name_ar" class="admin-form-input @error('name_ar') error @enderror"
                    value="{{ old('name_ar') }}" required>
                @error('name_ar')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">الاسم بالانجليزي *</label>
                <input type="text" name="name_en" class="admin-form-input @error('name_en') error @enderror" dir="ltr"
                    value="{{ old('name_en') }}" required>
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
                <label class="admin-form-label">الشعار (Logo)</label>
                <input type="file" name="logo" class="admin-form-input @error('logo') error @enderror">
                @error('logo')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">وثيقة الترخيص/السجل التجاري</label>
                <input type="file" name="license_document" class="admin-form-input @error('license_document') error @enderror">
                @error('license_document')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="admin-modal-footer" style="border-top:none;padding-top:0">
            <a href="{{ route('pharmacy_request.index') }}" class="admin-btn admin-btn-outline">إلغاء</a>
            <button type="submit" class="admin-btn admin-btn-primary">📤 إرسال الطلب للمراجعة</button>
        </div>
    </form>
</div>

@stop
