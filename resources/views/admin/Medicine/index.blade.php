@extends('layouts.nav_admin')
@section('title', 'الادوية')

@section('content')
<h1>الادوية</h1>
<div class="admin-table-wrap">
    <a href="{{ route('medicine.create') }}">اضافة جديد</a>
    <a href="{{ route('medicine.trash') }}">🗑 سلة المهملات</a>
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <div class="admin-toolbar">
        <form method="GET" action="{{ route('medicine.index') }}" style="display:flex;gap:10px;flex:1">
            <input type="text" name="q" value="{{ $q ?? '' }}" class="admin-search"
                placeholder="ابحث بالاسم أو الشركة المصنعة أو التصنيف أو شكل الدواء أو المادة الفعالة...">
            <button type="submit" class="admin-btn admin-btn-primary">🔍 بحث</button>
            @if(($q ?? '') !== '')
                <a href="{{ route('medicine.index') }}" class="admin-btn admin-btn-outline">إلغاء</a>
            @endif
        </form>
    </div>
    <table class="admin-table">
        {{-- brand_name_en brand_name_ar manufacturer_id category_id dosage_form_id reference_price barcode
        requires_prescription
        description_en description_ar notes is_active --}}
        <thead>
            <tr>
                <th>الاسم بالانجليزي</th>
                <th>الاسم بالعربي</th>
                <th>الشركة المصنعة</th>
                <th> التصنيف</th>
                <th>المادة الفعالة</th>
                <th>شكل الدواء</th>
                <th>التسعيرة</th>
                <th>الباركود</th>
                <th>يتطلب وصفة طبية</th>
                <th>هل فعال</th>
                <th>الوصف</th>
                <th>الوصف بالانجليزي</th>
                <th>ملاحظة</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody id="cat-tbody">
            @foreach ($medicines as $medicine)
                <tr>
                    <td>{{ $medicine->brand_name_en }}</td>
                    <td>{{ $medicine->brand_name_ar }}</td>
                    <td>{{ $medicine->manufacturer->name_en ?? '-' }}</td>
                    <td>{{ $medicine->category->name_en ?? '-' }}</td>
                    <td>
                        @foreach($medicine->activeIngredients as $ingredient)
                            {{ $ingredient->name_en }} ({{ $ingredient->pivot->strength_value }}
                            {{ $ingredient->pivot->strength_unit }})<br>
                        @endforeach
                    </td>
                    <td>{{ $medicine->dosageForm->name_en ?? '-' }}</td>
                    <td>{{ $medicine->reference_price }}</td>
                    <td>{{ $medicine->barcode }}</td>
                    <td>{{ $medicine->requires_prescription ? 'نعم' : 'لا' }}</td>
                    <td>{{ $medicine->is_active ? 'نعم' : 'لا' }}</ td>
                    <td>{{ $medicine->description_en }}</td>
                    <td>{{ $medicine->description_ar }}</td>
                    <td>{{ $medicine->notes }}</td>

                    <td>
                        <a href="{{ route('medicine.edit', $medicine->id) }}"
                            class="admin-btn admin-btn-sm admin-btn-outline">تعديل</a>
                        <form action="{{ route('medicine.destroy', $medicine->id) }}" method="POST"
                            style="display:inline"
                            onsubmit="return confirm('هل أنت متأكد أنك تريد حذف هذا الدواء؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger">حذف</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@stop
