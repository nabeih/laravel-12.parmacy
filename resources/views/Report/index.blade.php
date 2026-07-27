@extends('layouts.pharmacist')
@section('title', 'التقارير')
@section('content')

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">التقارير</h1>
        <p class="admin-page-sub">ملخص أداء صيدليتك خلال الفترة المحددة</p>
    </div>
</div>

<div class="admin-card">
    <form method="GET" action="{{ route('report.index') }}" class="admin-toolbar">
        <div class="admin-form-group" style="margin-bottom:0">
            <label class="admin-form-label">من</label>
            <input type="date" name="from" class="admin-form-input" value="{{ $from->toDateString() }}">
        </div>
        <div class="admin-form-group" style="margin-bottom:0">
            <label class="admin-form-label">إلى</label>
            <input type="date" name="to" class="admin-form-input" value="{{ $to->toDateString() }}">
        </div>
        <button type="submit" class="admin-btn admin-btn-primary" style="align-self:flex-end">تطبيق</button>
    </form>
</div>

<div class="admin-stats-grid">
    <div class="admin-stat-card blue">
        <div>
            <div class="admin-stat-value">{{ $salesCount }}</div>
            <div class="admin-stat-label">عدد فواتير البيع</div>
        </div>
        <div class="admin-stat-icon">🧾</div>
    </div>
    <div class="admin-stat-card green">
        <div>
            <div class="admin-stat-value">{{ number_format($salesRevenue, 2) }}</div>
            <div class="admin-stat-label">إيرادات المبيعات</div>
        </div>
        <div class="admin-stat-icon">💰</div>
    </div>
    <div class="admin-stat-card orange">
        <div>
            <div class="admin-stat-value">{{ $purchasesCount }}</div>
            <div class="admin-stat-label">عدد فواتير الشراء</div>
        </div>
        <div class="admin-stat-icon">🧾</div>
    </div>
    <div class="admin-stat-card red">
        <div>
            <div class="admin-stat-value">{{ number_format($purchasesSpend, 2) }}</div>
            <div class="admin-stat-label">إنفاق المشتريات</div>
        </div>
        <div class="admin-stat-icon">📦</div>
    </div>
    <div class="admin-stat-card purple">
        <div>
            <div class="admin-stat-value">{{ $totalStockQty }}</div>
            <div class="admin-stat-label">إجمالي وحدات المخزون الفعال</div>
        </div>
        <div class="admin-stat-icon">📊</div>
    </div>
</div>

<div class="admin-tables-grid">
    <div class="admin-card">
        <div class="admin-card-title">🏆 الأكثر مبيعاً</div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>الدواء</th>
                        <th>الكمية المباعة</th>
                        <th>الإيرادات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($topMedicines as $row)
                        <tr>
                            <td>{{ $row->medicines->brand_name_ar ?? ('دواء #' . $row->medicine_id) }}</td>
                            <td>{{ $row->total_qty }}</td>
                            <td>{{ number_format($row->total_revenue, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">
                                <div class="admin-empty">
                                    <div class="admin-empty-icon">🏆</div>
                                    <div class="admin-empty-title">لا يوجد مبيعات خلال هذه الفترة</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-title">⚠️ مخزون منخفض (10 وحدات أو أقل)</div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>الدواء</th>
                        <th>الكمية المتبقية</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lowStockBatches as $batch)
                        <tr>
                            <td>{{ $batch->medicines->brand_name_ar ?? '-' }}</td>
                            <td><span class="admin-badge admin-badge-red">{{ $batch->quantity }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2">
                                <div class="admin-empty">
                                    <div class="admin-empty-icon">✅</div>
                                    <div class="admin-empty-title">لا يوجد نقص في المخزون</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-title">⏳ دفعات قاربت على الانتهاء (خلال 30 يوم)</div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>الدواء</th>
                    <th>الكمية</th>
                    <th>تاريخ الانتهاء</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($nearExpiryBatches as $batch)
                    <tr>
                        <td>{{ $batch->medicines->brand_name_ar ?? '-' }}</td>
                        <td>{{ $batch->quantity }}</td>
                        <td><span class="admin-badge admin-badge-yellow">{{ $batch->expiry_date }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">
                            <div class="admin-empty">
                                <div class="admin-empty-icon">✅</div>
                                <div class="admin-empty-title">لا يوجد دفعات قريبة من الانتهاء</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@stop
