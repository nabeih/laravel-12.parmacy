@extends('layouts.pharmacist')
@section('title', 'فواتير البيع')
@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">فواتير البيع</h1>
        <p class="admin-page-sub">مبيعات الصيدليات للعملاء</p>
    </div>
    <div style="display:flex;gap:8px">
        <a href="{{ route('sale.create') }}" class="admin-btn admin-btn-primary">+ إضافة فاتورة بيع</a>
    </div>
</div>

<div class="admin-card">
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>رقم الفاتورة</th>
                    <th>التاريخ</th>
                    <th>عدد الأصناف</th>
                    <th>الإجمالي</th>
                    <th>الخصم</th>
                    <th>الضريبة</th>
                    <th>الصافي</th>
                    <th>طريقة الدفع</th>
                    <th>إثبات الدفع</th>
                    <th>الحالة</th>
                    <th>أُضيفت بواسطة</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sales as $sale)
                    <tr>
                        <td>{{ $sale->invoice_number }}</td>
                        <td>{{ $sale->invoice_date }}</td>
                        <td><span class="admin-badge admin-badge-blue">{{ $sale->sale_items->count() }}</span></td>
                        <td>{{ number_format($sale->total_amount, 2) }}</td>
                        <td>{{ number_format($sale->discount, 2) }}</td>
                        <td>{{ number_format($sale->tax, 2) }}</td>
                        <td><strong>{{ number_format($sale->final_amount, 2) }}</strong></td>
                        <td>{{ $sale->payment_method }}</td>
                        <td>
                            @if($sale->receipt_image)
                                <a href="{{ asset('storage/' . $sale->receipt_image) }}" target="_blank" class="admin-btn admin-btn-sm admin-btn-outline">عرض</a>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($sale->status == 'complete')
                                <span class="admin-badge admin-badge-green">مكتملة</span>
                            @else
                                <span class="admin-badge admin-badge-red">ملغاة</span>
                            @endif
                        </td>
                        <td>{{ $sale->users->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11">
                            <div class="admin-empty">
                                <div class="admin-empty-icon">🧾</div>
                                <div class="admin-empty-title">لا يوجد فواتير بيع بعد</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@stop
