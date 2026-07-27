@extends('layouts.admin')

@section('title', 'الشركات')
@section('content')

<form method="POST" action="{{ route('admin.manufacturer.store') }}">
    @csrf

    <label>الاسم</label>
    <input type="text" name="name_ar" placeholder="اسم الشركة">

    <label>Name </label>
    <input type="text" name="name_en" placeholder="Name">
    <label>العنوان</label>
    <input type="text" name="address" placeholder="العنوان">
    <label>الهاتف</label>
    <input type="text" name="phone" placeholder="الهاتف">

    <select name="is_active">
        <option value="1">فعالة</option>
        <option value="0">غير فعالة</option>
    </select>

    <button type="submit">Add</button>
</form>

@stop
