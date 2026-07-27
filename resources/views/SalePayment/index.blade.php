@extends('layouts.pharmacist')
@section('title', 'مدفوعات المبيعات')
@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">مدفوعات المبيعات</h1>
        <p class="admin-page-sub">سجل مدفوعات فواتير البيع (تحويلات، إيصالات...)</p>
    </div>
    <div style="display:flex;gap:8px">
        <a href="{{ route('sale_payment.create') }}" class="admin-btn admin-btn-primary">+ إضافة دفعة</a>
    </div>
</div>

<div class="admin-card">
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>رقم فاتورة البيع</th>
                    <th>طريقة الدفع</th>
                    <th>قناة التحويل</th>
                    <th>رقم المرجع</th>
                    <th>اسم المرسل</th>
                    <th>تاريخ الدفع</th>
                    <th>الإيصال</th>
                    <th>ملاحظات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments as $payment)
                    <tr>
                        <td>{{ $payment->sales->invoice_number ?? '-' }}</td>
                        <td>{{ $payment->payment_method }}</td>
                        <td>{{ $payment->transaction_id ?? '-' }}</td>
                        <td>{{ $payment->reference_number ?? '-' }}</td>
                        <td>{{ $payment->sender_name ?? '-' }}</td>
                        <td>{{ $payment->payment_date }}</td>
                        <td>
                            @if($payment->receipt_image)
                                <a href="{{ asset('storage/' . $payment->receipt_image) }}" target="_blank" class="admin-btn admin-btn-sm admin-btn-outline">عرض</a>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $payment->notes }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="admin-empty">
                                <div class="admin-empty-icon">💳</div>
                                <div class="admin-empty-title">لا يوجد مدفوعات بعد</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@stop
