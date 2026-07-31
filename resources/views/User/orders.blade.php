@extends('layouts.user')
@section('title', 'طلباتي')
@section('content')

<div class="page-header">
    <h1 class="h3 fw-bold mb-1">🛒 طلباتي</h1>
    <p class="text-muted mb-0">تابع حالة طلباتك من الصيدليات</p>
</div>

<div id="order-tabs" class="filter-tabs">
    <button type="button" class="filter-tab active" data-status="">الكل</button>
    <button type="button" class="filter-tab" data-status="pending">في الانتظار</button>
    <button type="button" class="filter-tab" data-status="confirmed">مؤكد</button>
    <button type="button" class="filter-tab" data-status="processing">قيد التحضير</button>
    <button type="button" class="filter-tab" data-status="ready">جاهز</button>
    <button type="button" class="filter-tab" data-status="delivered">تم التسليم</button>
    <button type="button" class="filter-tab" data-status="cancelled">ملغي</button>
</div>

<div id="orders-container" class="orders-grid">
    <div class="loading-state"><div class="spinner"></div><p>جاري التحميل...</p></div>
</div>

<div class="modal-overlay" id="order-modal">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3>تفاصيل الطلب</h3>
            <button type="button" class="modal-close" id="modal-close">✕</button>
        </div>
        <div class="modal-body" id="order-detail-body"></div>
        <div class="modal-footer">
            <button type="button" class="btn btn-ghost" id="modal-cancel">إغلاق</button>
        </div>
    </div>
</div>

@stop

@push('scripts')
    <script src="{{ asset('assest_pharmacy/js/user/orders.js') }}"></script>
    <script>document.addEventListener('DOMContentLoaded', initUserOrders);</script>
@endpush
