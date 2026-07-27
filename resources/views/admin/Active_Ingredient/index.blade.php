@extends('layouts.nav_admin')

@section('title', 'ingredient')

@section('content')
<h1>المواد الفعالة</h1>
<div class="admin-table-wrap">
    <a href="{{ route('activeingredient.create') }}">اضافة جديد</a>
    <a href="{{ route('activeingredient.trash') }}">🗑 سلة المهملات</a>
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <div class="admin-toolbar">
        <form method="GET" action="{{ route('activeingredient.index') }}" style="display:flex;gap:10px;flex:1">
            <input type="text" name="q" value="{{ $q ?? '' }}" class="admin-search"
                placeholder="ابحث بالاسم عربي أو إنجليزي...">
            <button type="submit" class="admin-btn admin-btn-primary">🔍 بحث</button>
            @if(($q ?? '') !== '')
                <a href="{{ route('activeingredient.index') }}" class="admin-btn admin-btn-outline">إلغاء</a>
            @endif
        </form>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                {{-- <th><input type="checkbox" id="select-all" onclick="_toggleAll(this)"></th> --}}
                <th>الاسم بالانجليزي</th>
                <th>الاسم بالعربي</th>
                <th>هل فعال</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody id="cat-tbody">
            {{-- dd($categories); --}}
            @foreach ($ingredients as $ingredient)
                <tr>
                    <td>{{ $ingredient->name_en }}</td>
                    <td>{{ $ingredient->name_ar }}</td>
                    <td>{{ $ingredient->is_active ? 'نعم' : 'لا' }}</td>
                    <td>
                        <a href="{{ route('activeingredient.edit', $ingredient->id) }}"
                            class="admin-btn admin-btn-sm admin-btn-outline">تعديل</a>

                        <form action="{{ route('activeingredient.destroy', $ingredient->id) }}" method="POST"
                            style="display:inline"
                            onsubmit="return confirm('هل أنت متأكد أنك تريد حذف هذا العنصر؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger">حذف</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            < {{-- @empty <span>{{ 'لايوجد بيانات' }}</span>

                @endforelse --}}
        </tbody>
    </table>
</div>

@stop
{{-- @section('script') --}}
