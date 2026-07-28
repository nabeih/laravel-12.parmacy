@extends('layouts.nav_admin')
@section('title', 'الصيادلة')
@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">الصيادلة</h1>
        <p class="admin-page-sub">طلبات انضمام الصيادلة وحالة اعتمادهم</p>
    </div>
    <div style="display:flex;gap:8px">
        <a href="{{ route('pharmacist.create') }}" class="admin-btn admin-btn-primary">+ إضافة صيدلي</a>
    </div>
</div>

<div class="admin-card">
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>المستخدم</th>
                    <th>الرقم الوطني</th>
                    <th>رقم النقابة</th>
                    <th>رقم الترخيص</th>
                    <th>جامعة التخرج</th>
                    <th>سنة التخرج</th>
                    <th>الحالة</th>
                    <th>هل فعال</th>
                    <th>المستندات</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pharmacists as $pharmacist)
                    <tr>
                        <td>{{ $pharmacist->users->name ?? '-' }}</td>
                        <td>{{ $pharmacist->national_id }}</td>
                        <td>{{ $pharmacist->syndicate_number }}</td>
                        <td>{{ $pharmacist->license_number }}</td>
                        <td>{{ $pharmacist->graduation_university }}</td>
                        <td>{{ $pharmacist->graduation_year }}</td>
                        <td>
                            @if($pharmacist->status == 'approved')
                                <span class="admin-badge admin-badge-green">معتمد</span>
                            @elseif($pharmacist->status == 'rejected')
                                <span class="admin-badge admin-badge-red">مرفوض</span>
                            @else
                                <span class="admin-badge admin-badge-yellow">قيد المراجعة</span>
                            @endif
                        </td>
                        <td>{{ $pharmacist->is_active ? 'نعم' : 'لا' }}</td>
                        <td>
                            <a href="{{ asset('storage/' . $pharmacist->certificate_file) }}" target="_blank" class="admin-btn admin-btn-sm admin-btn-outline">الشهادة</a>
                            <a href="{{ asset('storage/' . $pharmacist->syndicate_file) }}" target="_blank" class="admin-btn admin-btn-sm admin-btn-outline">النقابة</a>
                            <a href="{{ asset('storage/' . $pharmacist->license_file) }}" target="_blank" class="admin-btn admin-btn-sm admin-btn-outline">الترخيص</a>
                        </td>
                        <td>
                            @if($pharmacist->status == 'pending')
                                <a href="{{ route('pharmacist.review', $pharmacist->id) }}" class="admin-btn admin-btn-sm admin-btn-outline">مراجعة</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10">
                            <div class="admin-empty">
                                <div class="admin-empty-icon">🧑‍⚕️</div>
                                <div class="admin-empty-title">لا يوجد صيادلة بعد</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@stop
