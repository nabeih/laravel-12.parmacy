@extends('layouts.user')
@section('title', 'مسح الروشتة')
@section('content')

<div class="page-header">
    <h1 class="h3 fw-bold mb-1">🔍 مسح الروشتة الطبية</h1>
    <p class="text-muted mb-0">ارفع صورة الروشتة واستخرج الأدوية تلقائياً بالذكاء الاصطناعي</p>
</div>

<div class="demo-banner">
    <strong>تنبيه:</strong> هذه الأداة تستخرج الأدوية آلياً بالذكاء الاصطناعي ولا تُعد تشخيصاً طبياً. راجع الصيدلاني أو الطبيب دائماً للتأكد من الجرعات.
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card">
            <div class="upload-zone" id="upload-zone" style="border:2px dashed var(--border);border-radius:var(--radius);">
                <input type="file" id="file-input" accept="image/*" style="display:none">
                <div id="upload-zone-inner" class="upload-zone-inner">
                    <span class="upload-icon">🔍</span>
                    <p class="upload-title">اسحب صورة الروشتة هنا</p>
                    <p class="upload-sub">أو اضغط لرفع ملف</p>
                    <p class="upload-types">JPG, PNG حتى 10 ميجابايت</p>
                </div>
                <img id="preview-img" class="upload-preview" style="display:none">
            </div>
            <div class="d-flex gap-2 mt-3">
                <button type="button" class="btn btn-primary flex-grow-1" id="btn-scan" disabled>🔍 استخراج الأدوية</button>
                <button type="button" class="btn btn-ghost" id="btn-clear" style="display:none">إفراغ</button>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h3 class="h6 fw-bold mb-0">الأدوية المكتشفة</h3>
                <span class="badge badge-primary" id="med-count">0</span>
            </div>
            <div id="scan-status" class="small mb-2"></div>
            <div id="medicine-results" class="medicine-results">
                <div class="scan-placeholder">
                    <div style="font-size:40px;margin-bottom:8px">📋</div>
                    <p>قم برفع صورة روشتة للبدء</p>
                </div>
            </div>
        </div>
    </div>
</div>

@stop

@push('scripts')
    <script src="{{ asset('assest_pharmacy/js/ai-client.js') }}"></script>
    <script src="{{ asset('assest_pharmacy/js/user/scanner.js') }}"></script>
    <script>document.addEventListener('DOMContentLoaded', initScanner);</script>
@endpush
