@extends('layouts.pharmacist')
@section('title', 'فواتير الشراء')
@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">فواتير الشراء</h1>
        <p class="admin-page-sub">مشتريات الصيدليات من الموردين</p>
    </div>
    <div style="display:flex;gap:8px">
        <a href="{{ route('purchase.create') }}" class="admin-btn admin-btn-primary">+ إضافة فاتورة شراء</a>
    </div>
</div>

<div class="admin-card">
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>رقم الفاتورة</th>
                    <th>المورد</th>
                    <th>تاريخ الشراء</th>
                    <th>عدد الأصناف</th>
                    <th>الإجمالي</th>
                    <th>ملاحظات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($purchases as $purchase)
                    <tr>
                        <td>{{ $purchase->invoice_number }}</td>
                        <td>{{ $purchase->supplier_name }}</td>
                        <td>{{ $purchase->purchase_date }}</td>
                        <td><span class="admin-badge admin-badge-blue">{{ $purchase->purchase_items->count() }}</span></td>
                        <td>{{ number_format($purchase->total_amount, 2) }}</td>
                        <td>{{ $purchase->notes }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="admin-empty">
                                <div class="admin-empty-icon">🧾</div>
                                <div class="admin-empty-title">لا يوجد فواتير شراء بعد</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@stop
