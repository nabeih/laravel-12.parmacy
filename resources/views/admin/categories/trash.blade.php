@extends('layouts.nav_admin')

@section('title', 'سلة مهملات الاقسام')

@section('content')

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">سلة مهملات الاقسام</h1>
        <p class="admin-page-sub">الاقسام المحذوفة، يمكن استعادتها أو حذفها نهائياً</p>
    </div>
    <div style="display:flex;gap:8px">
        <a href="{{ route('category.index') }}" class="admin-btn admin-btn-outline">&larr; العودة إلى القائمة</a>
    </div>
</div>

<div class="admin-card">
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>الاسم بالانجليزي</th>
                    <th>الاسم بالعربي</th>
                    <th>هل فعال</th>
                    <th>تاريخ الحذف</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <td>{{ $category->name_en }}</td>
                        <td>{{ $category->name_ar }}</td>
                        <td>{{ $category->is_active ? 'نعم' : 'لا' }}</td>
                        <td>{{ $category->deleted_at }}</td>
                        <td>
                            <form action="{{ route('category.restore', $category->id) }}" method="POST"
                                style="display:inline">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="admin-btn admin-btn-sm admin-btn-primary">استعادة</button>
                            </form>

                            <form action="{{ route('category.force-delete', $category->id) }}" method="POST"
                                style="display:inline"
                                onsubmit="return confirm('تحذير: سيتم حذف هذا القسم نهائياً ولا يمكن التراجع عن هذا الإجراء. هل أنت متأكد؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger">حذف نهائي</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">لا يوجد عناصر محذوفة</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
