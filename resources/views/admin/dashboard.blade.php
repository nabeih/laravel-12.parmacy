@extends('layouts.nav_admin')

@section('title', 'Dashbord')

@section('content')
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">لوحة التحكم</h1>
        <p class="admin-page-sub" id="dash-date"></p>
    </div>
</div>

<!-- Stat Cards -->
<div class="admin-stats-grid" id="stat-cards"></div>

<!-- Charts -->
<div class="admin-charts-grid">
    <div class="admin-card">
        <div class="admin-card-title">📈 الطلبات اليومية — آخر 30 يوم</div>
        <canvas id="ordersChart" height="200"></canvas>
    </div>
    <div class="admin-card">
        <div class="admin-card-title">🏪 توزيع الصيدليات بالمدن</div>
        <canvas id="citiesChart" height="200"></canvas>
    </div>
</div>

<!-- Status distribution -->
<div class="admin-card" style="margin-bottom:20px">
    <div class="admin-card-title">📊 توزيع حالات الطلبات</div>
    <div style="max-width:200px;margin:0 auto">
        <canvas id="statusDistChart" height="110"></canvas>
    </div>
</div>

<!-- Recent tables -->
<div class="admin-tables-grid">
    <div class="admin-card">
        <div class="admin-card-title">📦 آخر الطلبات</div>
        <div class="admin-table-wrap">
            <table class="admin-table" id="recent-orders-tbl">
                <thead>
                    <tr>
                        <th>رقم الطلب</th>
                        <th>المستخدم</th>
                        <th>الصيدلية</th>
                        <th>الإجمالي</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    <div class="admin-card">
        <div class="admin-card-title">🏪 صيدليات تنتظر الموافقة</div>
        <div class="admin-table-wrap">
            <table class="admin-table" id="pending-pharm-tbl">
                <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>المدينة</th>
                        <th>تاريخ الطلب</th>
                        <th>إجراء</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>


@stop