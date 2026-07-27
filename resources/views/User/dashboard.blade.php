@extends('layouts.user')
@section('title', 'الرئيسية')
@section('content')

<h1 class="h3 fw-bold mb-1">مرحباً، {{ $user->name }} 👋</h1>
<p class="text-muted mb-4">بوابة المستخدم — ابحث عن الأدوية وتابع طلباتك</p>

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
    <div class="card-body text-center text-muted py-4">
        <div class="fs-1 mb-2">💊</div>
        <div class="fw-semibold">لا يوجد طلبات بعد</div>
    </div>
</div>

@stop
