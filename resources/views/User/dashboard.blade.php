@extends('layouts.user')
@section('title', 'الرئيسية')
@section('content')

<h1 class="h3 fw-bold mb-1">مرحباً، {{ $user->name }} 👋</h1>
<p class="text-muted mb-4">بوابة المستخدم — ابحث عن الأدوية وتابع صحتك وطلباتك</p>

@php
    $now = now()->format('H:i');
    $nextDose = null;
    $nextTime = null;
    foreach ($activeDoses as $dose) {
        foreach (($dose->times ?? []) as $t) {
            if ($t > $now && (! $nextTime || $t < $nextTime)) {
                $nextTime = $t;
                $nextDose = $dose;
            }
        }
    }
@endphp

@if($nextDose)
    <div class="next-dose-card">
        <h3>الجرعة القادمة</h3>
        <div class="dose-name">{{ $nextDose->name_ar }}</div>
        <div class="dose-time">الموعد: {{ $nextTime }}</div>
        <div class="next-dose-actions">
            <a href="{{ route('user.doses') }}" class="btn-white">عرض كل الجرعات</a>
        </div>
    </div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <a href="{{ route('user.scanner') }}" class="card h-100 text-decoration-none text-dark d-block">
            <div class="fs-1 mb-2">🔍</div>
            <div class="fw-semibold">مسح الروشتة</div>
            <div class="text-muted small">استخرج الأدوية من صورة الروشتة بالذكاء الاصطناعي</div>
        </a>
    </div>
    <div class="col-md-3 col-sm-6">
        <a href="{{ route('user.assistant') }}" class="card h-100 text-decoration-none text-dark d-block">
            <div class="fs-1 mb-2">🤖</div>
            <div class="fw-semibold">المساعد الذكي</div>
            <div class="text-muted small">اسأل عن الأدوية والجرعات والتفاعلات</div>
        </a>
    </div>
    <div class="col-md-3 col-sm-6">
        <a href="{{ route('user.doses') }}" class="card h-100 text-decoration-none text-dark d-block">
            <div class="fs-1 mb-2">💊</div>
            <div class="fw-semibold">جرعاتي</div>
            <div class="text-muted small">{{ $activeDoses->count() }} دواء نشط</div>
        </a>
    </div>
    <div class="col-md-3 col-sm-6">
        <a href="{{ route('user.orders') }}" class="card h-100 text-decoration-none text-dark d-block">
            <div class="fs-1 mb-2">🛒</div>
            <div class="fw-semibold">طلباتي</div>
            <div class="text-muted small">{{ $recentOrders->count() }} طلب حديث</div>
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <div class="fw-semibold">🔍 تصفح الصيدليات والأدوية</div>
            <div class="text-muted small">ابحث عن دواء أو صيدلية بالقرب منك</div>
        </div>
        <a href="{{ route('user.search') }}" class="btn btn-primary btn-sm">فتح البحث</a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h2 class="h6 fw-bold mb-3">آخر الطلبات</h2>
        @forelse ($recentOrders as $order)
            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                <div>
                    <span class="fw-semibold">#{{ $order->id }}</span>
                    <span class="text-muted small ms-2">{{ $order->pharmacy->name_ar ?? '-' }}</span>
                </div>
                <div class="text-muted small">{{ number_format($order->total, 2) }} د.أ — {{ $order->created_at->diffForHumans() }}</div>
            </div>
        @empty
            <div class="text-center text-muted py-4">
                <div class="fs-1 mb-2">💊</div>
                <div class="fw-semibold">لا يوجد طلبات بعد</div>
            </div>
        @endforelse
    </div>
</div>

@stop
