@extends('layouts.nav_admin')
@section('title', 'طلبات الصيدليات')
@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">طلبات الصيدليات</h1>
        <p class="admin-page-sub">طلبات الصيادلة لتسجيل صيدليات جديدة</p>
    </div>
    <div style="display:flex;gap:8px">
        <a href="{{ route('admin.pharmacy_request.index') }}"
            class="admin-btn {{ !$status ? 'admin-btn-primary' : 'admin-btn-outline' }}">الكل</a>
        <a href="{{ route('admin.pharmacy_request.index', ['status' => 'pending']) }}"
            class="admin-btn {{ $status == 'pending' ? 'admin-btn-primary' : 'admin-btn-outline' }}">قيد المراجعة</a>
        <a href="{{ route('admin.pharmacy_request.index', ['status' => 'approved']) }}"
            class="admin-btn {{ $status == 'approved' ? 'admin-btn-primary' : 'admin-btn-outline' }}">معتمدة</a>
        <a href="{{ route('admin.pharmacy_request.index', ['status' => 'rejected']) }}"
            class="admin-btn {{ $status == 'rejected' ? 'admin-btn-primary' : 'admin-btn-outline' }}">مرفوضة</a>
    </div>
</div>

<div class="admin-card">
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>الصيدلي</th>
                    <th>الهاتف</th>
                    <th>تاريخ الطلب</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($requests as $req)
                    <tr>
                        <td>{{ $req->name_ar }} <span class="text-muted">({{ $req->name_en }})</span></td>
                        <td>{{ $req->pharmacist->users->name ?? '-' }}</td>
                        <td>{{ $req->phone }}</td>
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
                        <td>
                            @if($req->status == 'pending')
                                <a href="{{ route('admin.pharmacy_request.review', $req->id) }}"
                                    class="admin-btn admin-btn-sm admin-btn-outline">مراجعة</a>
                                <form action="{{ route('admin.pharmacy_request.reject', $req->id) }}" method="POST"
                                    style="display:inline" onsubmit="return confirm('هل أنت متأكد من رفض هذا الطلب؟');">
                                    @csrf
                                    <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger">رفض</button>
                                </form>
                            @else
                                <span class="text-muted">{{ $req->admin_notes ?? '-' }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="admin-empty">
                                <div class="admin-empty-icon">🏪</div>
                                <div class="admin-empty-title">لا يوجد طلبات صيدليات</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@stop
