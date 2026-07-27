@extends('layouts.nav_admin')
@section('title', 'اضافة شكل الدواء')

@section('content')
<h1>اضف شكل دواء جديد للقائمة</h1>
<div class="admin-form-wrap">
    <h2>اضافة شكل الدواء</h2>
    <form action="{{ route('dosage_form.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name_en">الاسم بالانجليزي</label>
            <input type="text" name="name_en" id="name_en" required>
        </div>
        <div class="form-group">
            <label for="name_ar">الاسم بالعربي</label>
            <input type="text" name="name_ar" id="name_ar" required>
        </div>
        <div class="form-group">
            <label for="is_active">هل فعال</label>
            <select name="is_active" id="is_active">
                <option value="1">نعم</option>
                <option value="0">لا</option>
            </select>
        </div>
        <button type="submit" class="admin-btn">اضافة</button>
    </form>
</div>
@stop
