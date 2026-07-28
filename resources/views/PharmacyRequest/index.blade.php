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

@php
    $requestLabel = $pharmacyRequest?->target_pharmacy_id
        ? ($pharmacyRequest->targetPharmacy->name_ar ?? '-')
        : ($pharmacyRequest->name_ar ?? '-');
@endphp

<div class="admin-card">
    @if(!$pharmacyRequest)
        <div class="admin-empty">
            <div class="admin-empty-icon">🏪</div>
            <div class="admin-empty-title">لا تدير أي صيدلية حالياً</div>
            <a href="{{ route('pharmacy_request.create') }}" class="admin-btn admin-btn-primary" style="margin-top:12px">+ تسجيل صيدلية / الانضمام إلى صيدلية شاغرة</a>
        </div>
    @elseif($pharmacyRequest->status === 'pending')
        <div class="admin-empty">
            <div class="admin-empty-icon">⏳</div>
            <div class="admin-empty-title">طلب صيدلية "{{ $requestLabel }}" قيد المراجعة</div>
            <p class="text-muted">سيتم إشعارك فور مراجعة الإدارة للطلب.</p>
        </div>
    @elseif($pharmacyRequest->status === 'rejected')
        <div class="admin-empty">
            <div class="admin-empty-icon">❌</div>
            <div class="admin-empty-title">تم رفض طلب صيدلية "{{ $requestLabel }}"</div>
            @if($pharmacyRequest->admin_notes)
                <p class="text-muted">السبب: {{ $pharmacyRequest->admin_notes }}</p>
            @endif
            <a href="{{ route('pharmacy_request.create') }}" class="admin-btn admin-btn-primary" style="margin-top:12px">إعادة تقديم الطلب</a>
        </div>
    @else
        <div class="admin-section-title">✅ صيدليتك الحالية</div>
        <div class="admin-info-row"><div class="admin-info-label">الاسم</div><div class="admin-info-value">{{ $pharmacyRequest->pharmacy->name_ar ?? '-' }}</div></div>
        <div class="admin-info-row"><div class="admin-info-label">الهاتف</div><div class="admin-info-value">{{ $pharmacyRequest->pharmacy->phone ?? '-' }}</div></div>
        <div class="admin-info-row"><div class="admin-info-label">العنوان</div><div class="admin-info-value">{{ $pharmacyRequest->pharmacy->address ?? '-' }}</div></div>
        <div class="admin-info-row"><div class="admin-info-label">الحالة</div><div class="admin-info-value">{{ $pharmacyRequest->pharmacy->status ?? '-' }}</div></div>

        <form action="{{ route('pharmacy_request.leave') }}" method="POST" style="margin-top:16px"
            onsubmit="return confirm('هل أنت متأكد من مغادرة هذه الصيدلية؟ ستفقد صلاحية الوصول إلى المشتريات والمبيعات حتى تنضم لصيدلية أخرى.');">
            @csrf
            <button type="submit" class="admin-btn admin-btn-danger">🚪 مغادرة الصيدلية</button>
        </form>
    @endif
</div>

<div class="admin-card">
    <div class="admin-section-title">📜 السجل الوظيفي</div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>الصيدلية</th>
                    <th>تاريخ البدء</th>
                    <th>تاريخ الانتهاء</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pharmacist->assignments as $assignment)
                    <tr>
                        <td>{{ $assignment->pharmacy->name_ar ?? '-' }}</td>
                        <td>{{ $assignment->started_at->format('Y-m-d') }}</td>
                        <td>
                            @if($assignment->ended_at)
                                {{ $assignment->ended_at->format('Y-m-d') }}
                            @else
                                <span class="admin-badge admin-badge-green">حالياً</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">
                            <div class="admin-empty">
                                <div class="admin-empty-icon">📜</div>
                                <div class="admin-empty-title">لا يوجد سجل وظيفي بعد</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@stop
