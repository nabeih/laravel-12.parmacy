@extends('layouts.nav_admin')
@section('title', 'مراجعة طلب صيدلية')
@section('content')

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">مراجعة طلب صيدلية</h1>
        <p class="admin-page-sub">مُقدَّم من {{ $pharmacyRequest->pharmacist->users->name ?? '-' }}</p>
    </div>
    <div>
        <a href="{{ route('admin.pharmacy_request.index') }}" class="admin-btn admin-btn-outline">&larr; العودة إلى القائمة</a>
    </div>
</div>

<div class="admin-card">
    @if($pharmacyRequest->logo)
        <div class="admin-form-group">
            <label class="admin-form-label">الشعار</label><br>
            <img src="{{ asset('storage/' . $pharmacyRequest->logo) }}" style="width:60px;height:60px;border-radius:8px;object-fit:cover">
        </div>
    @endif
    @if($pharmacyRequest->license_document)
        <div class="admin-form-group">
            <a href="{{ asset('storage/' . $pharmacyRequest->license_document) }}" target="_blank" class="admin-btn admin-btn-sm admin-btn-outline">📎 عرض وثيقة الترخيص</a>
        </div>
    @endif

    <div class="admin-section-title">✅ الموافقة وإضافة الصيدلية</div>
    <form action="{{ route('admin.pharmacy_request.approve', $pharmacyRequest->id) }}" method="POST">
        @csrf
        <div class="admin-form-grid">
            <div class="admin-form-group">
                <label class="admin-form-label">الاسم بالعربي *</label>
                <input type="text" name="name_ar" class="admin-form-input @error('name_ar') error @enderror"
                    value="{{ old('name_ar', $pharmacyRequest->name_ar) }}" required>
                @error('name_ar')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">الاسم بالانجليزي *</label>
                <input type="text" name="name_en" class="admin-form-input @error('name_en') error @enderror" dir="ltr"
                    value="{{ old('name_en', $pharmacyRequest->name_en) }}" required>
                @error('name_en')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">الهاتف *</label>
                <input type="text" name="phone" class="admin-form-input @error('phone') error @enderror" dir="ltr"
                    value="{{ old('phone', $pharmacyRequest->phone) }}" required>
                @error('phone')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>
            <div class="admin-form-group" style="grid-column: 1 / -1">
                <label class="admin-form-label">العنوان *</label>
                <input type="text" name="address" class="admin-form-input @error('address') error @enderror"
                    value="{{ old('address', $pharmacyRequest->address) }}" required>
                @error('address')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">خط العرض</label>
                <input type="number" step="0.00000001" name="latitude" class="admin-form-input" dir="ltr"
                    value="{{ old('latitude', $pharmacyRequest->latitude) }}">
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">خط الطول</label>
                <input type="number" step="0.00000001" name="longitude" class="admin-form-input" dir="ltr"
                    value="{{ old('longitude', $pharmacyRequest->longitude) }}">
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">وقت الفتح</label>
                <input type="time" name="opening_time" class="admin-form-input" value="{{ old('opening_time', $pharmacyRequest->opening_time) }}">
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">وقت الإغلاق</label>
                <input type="time" name="closing_time" class="admin-form-input" value="{{ old('closing_time', $pharmacyRequest->closing_time) }}">
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
            <div class="admin-form-group" style="display:flex;align-items:center;padding-top:24px">
                <label class="admin-form-check">
                    <input type="checkbox" name="is_verified" value="1" checked>
                    صيدلية موثقة
                </label>
            </div>
        </div>
        <div class="admin-modal-footer" style="border-top:none;padding-top:0">
            <button type="submit" class="admin-btn admin-btn-primary">✅ الموافقة وإضافة الصيدلية</button>
        </div>
    </form>
</div>

<div class="admin-card">
    <div class="admin-section-title">❌ رفض الطلب</div>
    <form action="{{ route('admin.pharmacy_request.reject', $pharmacyRequest->id) }}" method="POST"
        onsubmit="return confirm('هل أنت متأكد من رفض هذا الطلب؟');">
        @csrf
        <div class="admin-form-group">
            <label class="admin-form-label">سبب الرفض (اختياري)</label>
            <textarea name="admin_notes" class="admin-form-textarea"></textarea>
        </div>
        <button type="submit" class="admin-btn admin-btn-danger">رفض الطلب</button>
    </form>
</div>

@stop
