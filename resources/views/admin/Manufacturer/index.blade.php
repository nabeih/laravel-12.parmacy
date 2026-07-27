@extends('layouts.nav_admin')
@section('title', 'الشركات المصنعة ')
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

@if(session('danger'))
    <div class="alert alert-success">
        {{ session('danger') }}
    </div>
@endif

<h2>الشركات المصنعة للادوية</h2>
<p>
    <a href="{{ route('admin.manufacturer.create') }}" class="btn btn-success">اضافة شركة جديدة</a>
    <a href="{{ route('admin.manufacturer.trash') }}" class="btn btn-secondary">🗑 سلة المهملات</a>
</p>

<div class="admin-toolbar">
    <form method="GET" action="{{ route('admin.manufacturer') }}" style="display:flex;gap:10px;flex:1">
        <input type="text" name="q" value="{{ $q ?? '' }}" class="admin-search"
            placeholder="ابحث باسم الشركة عربي أو إنجليزي...">
        <button type="submit" class="admin-btn admin-btn-primary">🔍 بحث</button>
        @if(($q ?? '') !== '')
            <a href="{{ route('admin.manufacturer') }}" class="admin-btn admin-btn-outline">إلغاء</a>
        @endif
    </form>
</div>

<table>
    <tr>
        <th>اسم الشركة</th>
        <th>name comp</th>
        <th>فعالة</th>
        <th>اجراء</th>
    </tr>
    @foreach ($manufacturers as $manufacturer)
        <tr>
            <td>{{ $manufacturer->name_ar }}</td>
            <td>{{ $manufacturer->name_en }}</td>
            <td>{{ $manufacturer->is_active ? 'نعم' : 'لا' }}</td>
            <td>
                <a href="{{ route('admin.manufacturer.edit', $manufacturer->id) }}" class="btn btn-primary">تعديل</a>

                <form method="post" action="{{ route('manufacturer.delete', $manufacturer->id) }}">
                    @csrf
                    @method('delete')
                    <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i>حذف</button>
                </form>


            </td>
        </tr>
    @endforeach
</table>



@stop