@extends('layouts.nav_admin')

@section('title', 'سلة مهملات المواد الفعالة')

@section('content')
<h1>سلة مهملات المواد الفعالة</h1>
<div class="admin-table-wrap">
    <a href="{{ route('activeingredient.index') }}">&larr; العودة إلى القائمة</a>
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
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
            @forelse ($ingredients as $ingredient)
                <tr>
                    <td>{{ $ingredient->name_en }}</td>
                    <td>{{ $ingredient->name_ar }}</td>
                    <td>{{ $ingredient->is_active ? 'نعم' : 'لا' }}</td>
                    <td>{{ $ingredient->deleted_at }}</td>
                    <td>
                        <form action="{{ route('activeingredient.restore', $ingredient->id) }}" method="POST"
                            style="display:inline">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="admin-btn admin-btn-sm admin-btn-primary">استعادة</button>
                        </form>

                        <form action="{{ route('activeingredient.force-delete', $ingredient->id) }}" method="POST"
                            style="display:inline"
                            onsubmit="return confirm('تحذير: سيتم حذف هذه المادة نهائياً ولا يمكن التراجع عن هذا الإجراء. هل أنت متأكد؟');">
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

@stop
