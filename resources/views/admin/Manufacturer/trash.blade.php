@extends('layouts.nav_admin')
@section('title', 'سلة مهملات الشركات المصنعة')
@section('content')

<style>
    table {
        border-collapse: collapse;
        width: 100%;
    }

    th,
    td {
        text-align: left;
        padding: 8px;
    }

    tr:nth-child(even) {
        background-color: #D6EEEE;
    }
</style>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<h2>سلة مهملات الشركات المصنعة</h2>
<p>
    <a href="{{ route('admin.manufacturer') }}" class="btn btn-secondary">&larr; العودة إلى القائمة</a>
</p>

<table>
    <tr>
        <th>اسم الشركة</th>
        <th>name comp</th>
        <th>فعالة</th>
        <th>تاريخ الحذف</th>
        <th>اجراء</th>
    </tr>
    @forelse ($manufacturers as $manufacturer)
        <tr>
            <td>{{ $manufacturer->name_ar }}</td>
            <td>{{ $manufacturer->name_en }}</td>
            <td>{{ $manufacturer->is_active ? 'نعم' : 'لا' }}</td>
            <td>{{ $manufacturer->deleted_at }}</td>
            <td>
                <form method="post" action="{{ route('admin.manufacturer.restore', $manufacturer->id) }}" style="display:inline">
                    @csrf
                    @method('put')
                    <button type="submit" class="btn btn-success btn-sm">استعادة</button>
                </form>

                <form method="post" action="{{ route('admin.manufacturer.force-delete', $manufacturer->id) }}" style="display:inline"
                    onsubmit="return confirm('تحذير: سيتم حذف هذه الشركة نهائياً ولا يمكن التراجع عن هذا الإجراء. هل أنت متأكد؟');">
                    @csrf
                    @method('delete')
                    <button type="submit" class="btn btn-danger btn-sm">حذف نهائي</button>
                </form>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5">لا يوجد عناصر محذوفة</td>
        </tr>
    @endforelse
</table>

@stop
