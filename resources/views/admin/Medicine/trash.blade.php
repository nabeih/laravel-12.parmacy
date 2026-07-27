@extends('layouts.nav_admin')
@section('title', 'سلة مهملات الادوية')

@section('content')
<h1>سلة مهملات الادوية</h1>
<div class="admin-table-wrap">
    <a href="{{ route('medicine.index') }}">&larr; العودة إلى القائمة</a>
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
                <th>الشركة المصنعة</th>
                <th>التصنيف</th>
                <th>شكل الدواء</th>
                <th>التسعيرة</th>
                <th>الباركود</th>
                <th>تاريخ الحذف</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($medicines as $medicine)
                <tr>
                    <td>{{ $medicine->brand_name_en }}</td>
                    <td>{{ $medicine->brand_name_ar }}</td>
                    <td>{{ $medicine->manufacturer->name_en ?? '-' }}</td>
                    <td>{{ $medicine->category->name_en ?? '-' }}</td>
                    <td>{{ $medicine->dosageForm->name_en ?? '-' }}</td>
                    <td>{{ $medicine->reference_price }}</td>
                    <td>{{ $medicine->barcode }}</td>
                    <td>{{ $medicine->deleted_at }}</td>
                    <td>
                        <form action="{{ route('medicine.restore', $medicine->id) }}" method="POST"
                            style="display:inline">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="admin-btn admin-btn-sm admin-btn-primary">استعادة</button>
                        </form>

                        <form action="{{ route('medicine.force-delete', $medicine->id) }}" method="POST"
                            style="display:inline"
                            onsubmit="return confirm('تحذير: سيتم حذف هذا الدواء نهائياً ولا يمكن التراجع عن هذا الإجراء. هل أنت متأكد؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger">حذف نهائي</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">لا يوجد عناصر محذوفة</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@stop
