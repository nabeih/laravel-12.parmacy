@extends('layouts.user')
@section('title', 'الصيدليات والأدوية')
@section('content')

<h1 class="h3 fw-bold mb-1">الصيدليات والأدوية</h1>
<p class="text-muted mb-4">تصفح الصيدليات والأدوية المتوفرة على المنصة أو ابحث عمّا تريد</p>

<form method="GET" action="{{ route('user.search') }}" class="card border-0 shadow-sm mb-4">
    <div class="card-body d-flex gap-2">
        <input type="text" name="q" value="{{ $q }}" class="form-control"
            placeholder="ابحث باسم الصيدلية أو الدواء...">
        <button type="submit" class="btn btn-primary px-4">🔍 بحث</button>
        @if ($q !== '')
            <a href="{{ route('user.search') }}" class="btn btn-outline-secondary">إلغاء</a>
        @endif
    </div>
</form>

<h2 class="h5 fw-bold mb-3">🏪 الصيدليات</h2>
<div class="row g-3 mb-4">
    @forelse ($pharmacies as $pharmacy)
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h3 class="h6 fw-bold mb-0">{{ $pharmacy->name_ar }}</h3>
                        @if ($pharmacy->status == 'opne')
                            <span class="badge bg-success">مفتوحة</span>
                        @elseif ($pharmacy->status == 'closed')
                            <span class="badge bg-secondary">مغلقة</span>
                        @else
                            <span class="badge bg-danger">موقوفة</span>
                        @endif
                    </div>
                    <div class="text-muted small mb-1">{{ $pharmacy->name_en }}</div>
                    <div class="small mb-1">📍 {{ $pharmacy->address }}</div>
                    <div class="small mb-1">📞 {{ $pharmacy->phone }}</div>
                    @if ($pharmacy->opening_time && $pharmacy->closing_time)
                        <div class="small text-muted">🕒 {{ $pharmacy->opening_time }} - {{ $pharmacy->closing_time }}</div>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center text-muted py-4">
                    <div class="fs-1 mb-2">🏪</div>
                    <div class="fw-semibold">لا توجد صيدليات مطابقة</div>
                </div>
            </div>
        </div>
    @endforelse
</div>
{{ $pharmacies->appends(['q' => $q])->links() }}

<h2 class="h5 fw-bold mb-3 mt-4">💊 الأدوية</h2>
<div class="row g-3 mb-4">
    @forelse ($medicines as $medicine)
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h3 class="h6 fw-bold mb-0">{{ $medicine->brand_name_ar }}</h3>
                        @if ($medicine->requires_prescription)
                            <span class="badge bg-warning text-dark">يتطلب وصفة</span>
                        @endif
                    </div>
                    <div class="text-muted small mb-1">{{ $medicine->brand_name_en }}</div>
                    <div class="small mb-1">🏭 {{ $medicine->manufacturer->name_ar ?? '-' }}</div>
                    <div class="small mb-1">🗂️ {{ $medicine->category->name_ar ?? '-' }} —
                        {{ $medicine->dosageForm->name_ar ?? '-' }}</div>
                    <div class="fw-semibold mt-2">{{ number_format($medicine->reference_price, 2) }} د.أ</div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center text-muted py-4">
                    <div class="fs-1 mb-2">💊</div>
                    <div class="fw-semibold">لا توجد أدوية مطابقة</div>
                </div>
            </div>
        </div>
    @endforelse
</div>
{{ $medicines->appends(['q' => $q])->links() }}

@stop
