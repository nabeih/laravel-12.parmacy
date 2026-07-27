@extends('layouts.nav_admin')

@section('title', 'سلة مهملات اشكال الادوية')

@section('content')
<h1>سلة مهملات اشكال الادوية</h1>
<div class="admin-table-wrap">
    <a href="{{ route('dosage_form.index') }}">&larr; العودة إلى القائمة</a>
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
            @forelse ($dosage_form as $dosages_forms)
                <tr>
                    <td>{{ $dosages_forms->name_en }}</td>
                    <td>{{ $dosages_forms->name_ar }}</td>
                    <td>{{ $dosages_forms->is_active ? 'نعم' : 'لا' }}</td>
                    <td>{{ $dosages_forms->deleted_at }}</td>
                    <td>
                        <form action="{{ route('dosage_form.restore', $dosages_forms->id) }}" method="POST"
                            style="display:inline">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="admin-btn admin-btn-sm admin-btn-primary">استعادة</button>
                        </form>

                        <form action="{{ route('dosage_form.force-delete', $dosages_forms->id) }}" method="POST"
                            style="display:inline"
                            onsubmit="return confirm('تحذير: سيتم حذف هذا العنصر نهائياً ولا يمكن التراجع عن هذا الإجراء. هل أنت متأكد؟');">
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
