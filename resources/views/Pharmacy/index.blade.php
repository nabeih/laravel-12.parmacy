@extends('layouts.nav_admin')
@section('title', 'الصيدليات')
@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">الصيدليات</h1>
        <p class="admin-page-sub">قائمة الصيدليات المسجلة على المنصة — تُضاف عبر موافقة الإدارة على طلبات الصيادلة</p>
    </div>
    <div>
        <a href="{{ route('admin.pharmacy_request.index') }}" class="admin-btn admin-btn-primary">📋 طلبات الصيدليات</a>
    </div>
</div>

<div class="admin-card">
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>الشعار</th>
                    <th>الاسم بالعربي</th>
                    <th>الاسم بالانجليزي</th>
                    <th>الصيدلي المسؤول</th>
                    <th>الهاتف</th>
                    <th>العنوان</th>
                    <th>مواعيد العمل</th>
                    <th>الحالة</th>
                    <th>موثقة</th>
                    <th>الاجراء</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pharmacies as $pharmacy)
                    <tr>
                        <td>
                            @if($pharmacy->logo)
                                <img src="{{ asset('storage/' . $pharmacy->logo) }}" alt="logo"
                                    style="width:36px;height:36px;border-radius:8px;object-fit:cover">
                            @else
                                🏪
                            @endif
                        </td>
                        <td>{{ $pharmacy->name_ar }}</td>
                        <td>{{ $pharmacy->name_en }}</td>
                        <td>{{ $pharmacy->pharmacists->users->name ?? '-' }}</td>
                        <td>{{ $pharmacy->phone }}</td>
                        <td>{{ $pharmacy->address }}</td>
                        <td>{{ $pharmacy->opening_time }} - {{ $pharmacy->closing_time }}</td>
                        <td>
                            @if($pharmacy->status == 'opne')
                                <span class="admin-badge admin-badge-green">مفتوحة</span>
                            @elseif($pharmacy->status == 'closed')
                                <span class="admin-badge admin-badge-gray">مغلقة</span>
                            @else
                                <span class="admin-badge admin-badge-red">موقوفة</span>
                            @endif
                        </td>
                        <td>{{ $pharmacy->is_verified ? 'نعم' : 'لا' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            <div class="admin-empty">
                                <div class="admin-empty-icon">🏪</div>
                                <div class="admin-empty-title">لا يوجد صيدليات بعد</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@stop
