@extends('layouts.nav_admin')
@section('title', 'مراجعة صيدلي')
@section('content')

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">مراجعة اعتماد صيدلي</h1>
        <p class="admin-page-sub">{{ $pharmacist->users->name ?? '-' }} — {{ $pharmacist->users->email ?? '-' }}</p>
    </div>
    <div>
        <a href="{{ route('pharmacist.index') }}" class="admin-btn admin-btn-outline">&larr; العودة إلى القائمة</a>
    </div>
</div>

<div class="admin-card">
    <div class="admin-info-row"><div class="admin-info-label">الرقم الوطني</div><div class="admin-info-value">{{ $pharmacist->national_id }}</div></div>
    <div class="admin-info-row"><div class="admin-info-label">رقم النقابة</div><div class="admin-info-value">{{ $pharmacist->syndicate_number }}</div></div>
    <div class="admin-info-row"><div class="admin-info-label">رقم الترخيص</div><div class="admin-info-value">{{ $pharmacist->license_number }}</div></div>
    <div class="admin-info-row"><div class="admin-info-label">جامعة التخرج</div><div class="admin-info-value">{{ $pharmacist->graduation_university }}</div></div>
    <div class="admin-info-row"><div class="admin-info-label">سنة التخرج</div><div class="admin-info-value">{{ $pharmacist->graduation_year }}</div></div>
    @if($pharmacist->notes)
        <div class="admin-info-row"><div class="admin-info-label">ملاحظات الصيدلي</div><div class="admin-info-value">{{ $pharmacist->notes }}</div></div>
    @endif

    <div class="admin-section-title" style="margin-top:20px">📎 المستندات</div>
    <div style="display:flex;gap:8px">
        <a href="{{ asset('storage/' . $pharmacist->certificate_file) }}" target="_blank" class="admin-btn admin-btn-outline">الشهادة</a>
        <a href="{{ asset('storage/' . $pharmacist->syndicate_file) }}" target="_blank" class="admin-btn admin-btn-outline">النقابة</a>
        <a href="{{ asset('storage/' . $pharmacist->license_file) }}" target="_blank" class="admin-btn admin-btn-outline">الترخيص</a>
    </div>

    <div class="admin-modal-footer" style="border-top:none;padding-top:20px">
        <form action="{{ route('pharmacist.approve', $pharmacist->id) }}" method="POST">
            @csrf
            <button type="submit" class="admin-btn admin-btn-primary">✅ اعتماد الصيدلي</button>
        </form>
    </div>
</div>

<div class="admin-card">
    <div class="admin-section-title">❌ رفض الطلب</div>
    <form action="{{ route('pharmacist.reject', $pharmacist->id) }}" method="POST"
        onsubmit="return confirm('هل أنت متأكد من رفض هذا الصيدلي؟');">
        @csrf
        <div class="admin-form-group">
            <label class="admin-form-label">سبب الرفض (اختياري)</label>
            <textarea name="admin_notes" class="admin-form-textarea"></textarea>
        </div>
        <button type="submit" class="admin-btn admin-btn-danger">رفض</button>
    </form>
</div>

@stop
