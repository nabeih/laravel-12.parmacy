@extends('layouts.pharmacist')
@section('title', 'صيدليتي')
@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">صيدليتي</h1>
        <p class="admin-page-sub">حالة تسجيل صيدليتك في النظام</p>
    </div>
</div>

<div class="admin-card">
    @if(!$pharmacyRequest)
        <div class="admin-empty">
            <div class="admin-empty-icon">🏪</div>
            <div class="admin-empty-title">لم تقم بتسجيل صيدلية بعد</div>
            <a href="{{ route('pharmacy_request.create') }}" class="admin-btn admin-btn-primary" style="margin-top:12px">+ تسجيل صيدلية جديدة</a>
        </div>
    @elseif($pharmacyRequest->status === 'pending')
        <div class="admin-empty">
            <div class="admin-empty-icon">⏳</div>
            <div class="admin-empty-title">طلب تسجيل صيدلية "{{ $pharmacyRequest->name_ar }}" قيد المراجعة</div>
            <p class="text-muted">سيتم إشعارك فور مراجعة الإدارة للطلب.</p>
        </div>
    @elseif($pharmacyRequest->status === 'rejected')
        <div class="admin-empty">
            <div class="admin-empty-icon">❌</div>
            <div class="admin-empty-title">تم رفض طلب تسجيل صيدلية "{{ $pharmacyRequest->name_ar }}"</div>
            @if($pharmacyRequest->admin_notes)
                <p class="text-muted">السبب: {{ $pharmacyRequest->admin_notes }}</p>
            @endif
            <a href="{{ route('pharmacy_request.create') }}" class="admin-btn admin-btn-primary" style="margin-top:12px">إعادة تقديم الطلب</a>
        </div>
    @else
        <div class="admin-section-title">✅ صيدليتك المعتمدة</div>
        <div class="admin-info-row"><div class="admin-info-label">الاسم</div><div class="admin-info-value">{{ $pharmacyRequest->pharmacy->name_ar ?? '-' }}</div></div>
        <div class="admin-info-row"><div class="admin-info-label">الهاتف</div><div class="admin-info-value">{{ $pharmacyRequest->pharmacy->phone ?? '-' }}</div></div>
        <div class="admin-info-row"><div class="admin-info-label">العنوان</div><div class="admin-info-value">{{ $pharmacyRequest->pharmacy->address ?? '-' }}</div></div>
        <div class="admin-info-row"><div class="admin-info-label">الحالة</div><div class="admin-info-value">{{ $pharmacyRequest->pharmacy->status ?? '-' }}</div></div>
    @endif
</div>

@stop
