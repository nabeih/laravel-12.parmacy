@extends('layouts.user')
@section('title', 'جرعاتي')
@section('content')

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="h3 fw-bold mb-1">💊 جرعاتي</h1>
        <p class="text-muted mb-0">تابع مواعيد أدويتك اليومية</p>
    </div>
    <button type="button" class="btn btn-primary" id="btn-add-dose">+ إضافة دواء</button>
</div>

<div id="doses-list">
    <div class="loading-state"><div class="spinner"></div><p>جاري التحميل...</p></div>
</div>

<div class="modal-overlay" id="dose-modal">
    <div class="modal">
        <div class="modal-header">
            <h3 id="dose-modal-title">إضافة دواء</h3>
            <button type="button" class="modal-close" id="dose-modal-close">✕</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>اسم الدواء (عربي) <span class="required">*</span></label>
                <input type="text" id="df-nameAr" class="form-control">
            </div>
            <div class="form-group">
                <label>اسم الدواء (إنجليزي)</label>
                <input type="text" id="df-nameEn" class="form-control">
            </div>
            <div class="form-group">
                <label>الجرعة</label>
                <input type="text" id="df-dosage" class="form-control" placeholder="مثال: 500mg">
            </div>
            <div class="form-group">
                <label>مواعيد الجرعة <span class="required">*</span></label>
                <div id="times-container" style="display:flex;flex-direction:column;gap:8px;margin-bottom:8px"></div>
                <button type="button" class="btn btn-sm btn-outline" id="btn-add-time">+ إضافة موعد</button>
            </div>
            <div class="form-group">
                <label>حتى تاريخ</label>
                <input type="date" id="df-until" class="form-control">
            </div>
            <div class="form-group">
                <label>ملاحظات</label>
                <textarea id="df-notes" class="form-control"></textarea>
            </div>
            <div id="dose-msg" class="error-msg"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-ghost" id="dose-modal-cancel">إلغاء</button>
            <button type="button" class="btn btn-primary" id="dose-modal-save">حفظ</button>
        </div>
    </div>
</div>

@stop

@push('scripts')
    <script src="{{ asset('assest_pharmacy/js/user/doses.js') }}"></script>
    <script>document.addEventListener('DOMContentLoaded', initDoses);</script>
@endpush
