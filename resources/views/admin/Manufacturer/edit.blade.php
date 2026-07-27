@extends('layouts.admin')

@section('title', 'تعديل شركة')
@section('content')

<form method="POST" action="{{ route('admin.manufacturer.update', $manufacturer->id) }}">
    @csrf
    @method('PUT')

    <label>الاسم</label>
    <input type="text" name="name_ar" placeholder="اسم الشركة" value="{{ old('name_ar', $manufacturer->name_ar) }}">

    <label>Name </label>
    <input type="text" name="name_en" placeholder="Name" value="{{ old('name_en', $manufacturer->name_en) }}">
    <label>العنوان</label>
    <input type="text" name="address" placeholder="العنوان" value="{{ old('address', $manufacturer->address) }}">
    <label>الهاتف</label>
    <input type="text" name="phone" placeholder="الهاتف" value="{{ old('phone', $manufacturer->phone) }}">

    <select name="is_active">
        <option value="1" {{ old('is_active', $manufacturer->is_active) == 1 ? 'selected' : '' }}>فعالة</option>
        <option value="0" {{ old('is_active', $manufacturer->is_active) == 0 ? 'selected' : '' }}>غير فعالة</option>
    </select>

    <button type="submit">تعديل</button>
</form>

@stop
