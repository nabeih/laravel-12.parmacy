@extends('layouts.pharmacist')
@section('title', 'دفعات المخزون')
@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">دفعات المخزون</h1>
        <p class="admin-page-sub">تُنشأ الدفعات تلقائياً عند إضافة فاتورة شراء</p>
    </div>
</div>

<div class="admin-card">
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>الدواء</th>
                    <th>الكمية</th>
                    <th>سعر الشراء</th>
                    <th>سعر البيع</th>
                    <th>رقم الدفعة/اللوط</th>
                    <th>تاريخ التصنيع</th>
                    <th>تاريخ الانتهاء</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($batches as $batch)
                    <tr>
                        <td>{{ $batch->medicines->brand_name_en ?? '-' }}</td>
                        <td>{{ $batch->quantity }}</td>
                        <td>{{ number_format($batch->purchase_price, 2) }}</td>
                        <td>{{ number_format($batch->selling_price, 2) }}</td>
                        <td>{{ $batch->lot_number ?? '-' }}</td>
                        <td>{{ $batch->manufacturing_date ?? '-' }}</td>
                        <td>{{ $batch->expiry_date }}</td>
                        <td>
                            @if($batch->is_active)
                                <span class="admin-badge admin-badge-green">فعالة</span>
                            @else
                                <span class="admin-badge admin-badge-gray">غير فعالة</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="admin-empty">
                                <div class="admin-empty-icon">📦</div>
                                <div class="admin-empty-title">لا يوجد دفعات مخزون بعد</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@stop
