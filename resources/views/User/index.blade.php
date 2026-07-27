@extends('layouts.nav_admin')
@section('title', 'المستخدمون')
@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">المستخدمون</h1>
        <p class="admin-page-sub">حسابات المستخدمين على المنصة (مدراء، صيادلة، عملاء)</p>
    </div>
    <div style="display:flex;gap:8px">
        <a href="{{ route('user.create') }}" class="admin-btn admin-btn-primary">+ إضافة مستخدم</a>
    </div>
</div>

<div class="admin-card">
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>الصورة</th>
                    <th>الاسم</th>
                    <th>البريد الإلكتروني</th>
                    <th>الدور</th>
                    <th>البريد موثق</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>
                            @if($user->avatar)
                                <img src="{{ asset('storage/' . $user->avatar) }}" alt="avatar" style="width:32px;height:32px;border-radius:50%;object-fit:cover">
                            @else
                                👤
                            @endif
                        </td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->role == 'admin')
                                <span class="admin-badge admin-badge-purple">مدير</span>
                            @elseif($user->role == 'pharmacist')
                                <span class="admin-badge admin-badge-blue">صيدلي</span>
                            @else
                                <span class="admin-badge admin-badge-gray">عميل</span>
                            @endif
                        </td>
                        <td>{{ $user->email_verified_at ? 'نعم' : 'لا' }}</td>
                        <td>
                            @if($user->is_active)
                                <span class="admin-badge admin-badge-green">فعال</span>
                            @else
                                <span class="admin-badge admin-badge-red">موقوف</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="admin-empty">
                                <div class="admin-empty-icon">👥</div>
                                <div class="admin-empty-title">لا يوجد مستخدمون بعد</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@stop
