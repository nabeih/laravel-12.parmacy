@extends('layouts.pharmacist')
@section('title', 'طلبات الأدوية')
@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">طلبات إضافة أدوية جديدة</h1>
        <p class="admin-page-sub">طلباتك لإضافة أدوية غير موجودة في الكتالوج الرئيسي</p>
    </div>
    <div>
        <a href="{{ route('medicine_request.create') }}" class="admin-btn admin-btn-primary">+ طلب دواء جديد</a>
    </div>
</div>

<div class="admin-card">
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>تاريخ الطلب</th>
                    <th>الحالة</th>
                    <th>ملاحظات الإدارة</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($requests as $req)
                    <tr>
                        <td>{{ $req->brand_name_ar }} <span class="text-muted">({{ $req->brand_name_en }})</span></td>
                        <td>{{ $req->created_at->format('Y-m-d') }}</td>
                        <td>
                            @if($req->status == 'approved')
                                <span class="admin-badge admin-badge-green">تمت الموافقة</span>
                            @elseif($req->status == 'rejected')
                                <span class="admin-badge admin-badge-red">مرفوض</span>
                            @else
                                <span class="admin-badge admin-badge-yellow">قيد المراجعة</span>
                            @endif
                        </td>
                        <td>{{ $req->admin_notes ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <div class="admin-empty">
                                <div class="admin-empty-icon">📋</div>
                                <div class="admin-empty-title">لا يوجد طلبات أدوية بعد</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@stop
