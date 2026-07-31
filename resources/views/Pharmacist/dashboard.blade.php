@extends('layouts.pharmacist')
@section('title', 'لوحة تحكم الصيدلي')
@section('content')

<h1 class="h3 fw-bold mb-1">مرحباً، {{ auth()->user()->name }} 👋</h1>
<p class="text-muted mb-4">بوابة الصيدلي — إدارة صيدليتك ومبيعاتك ومخزونك</p>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if(auth()->user()->pharmacists?->pharmacies?->status == 'suspended')

    <div class="alert alert-danger">
        <h3>🚫 الصيدلية موقوفة</h3>
        <p>
            تم إيقاف هذه الصيدلية من قبل إدارة النظام.
            لا يمكنك إجراء عمليات البيع أو الشراء أو إدارة المخزون حتى يتم إعادة تفعيلها.
        </p>
    </div>

@endif
<div class="card border-0 shadow-sm">
    <div class="card-body">
        @if($pharmacist)
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-muted small">حالة الاعتماد</div>
                    <div class="fw-semibold">
                        @if($pharmacist->status == 'approved')
                            <span class="badge bg-success">معتمد</span>
                        @elseif($pharmacist->status == 'rejected')
                            <span class="badge bg-danger">مرفوض</span>
                        @else
                            <span class="badge bg-warning text-dark">قيد المراجعة</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">الصيدلية</div>
                    <div class="fw-semibold">{{ $pharmacist->pharmacies->name_ar ?? 'لم يتم ربط صيدلية بعد' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">رقم الترخيص</div>
                    <div class="fw-semibold">{{ $pharmacist->license_number }}</div>
                </div>
            </div>
        @else
            <div class="text-center text-muted py-4">
                <div class="fs-1 mb-2">🧑‍⚕️</div>
                <div class="fw-semibold">لا يوجد ملف صيدلي مرتبط بهذا الحساب بعد</div>
            </div>
        @endif
    </div>
</div>

@stop