@extends('layouts.nav_admin')
@section('title', 'تعديل شكل الدواء')

@section('content')
<h1>تعديل شكل الدواء</h1>
<div class="admin-form-wrap">
    <form action="{{ route('dosage_form.update', $dosage_form->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name_en">الاسم بالانجليزي</label>
            <input type="text" name="name_en" id="name_en" value="{{ old('name_en', $dosage_form->name_en) }}" required>
        </div>
        <div class="form-group">
            <label for="name_ar">الاسم بالعربي</label>
            <input type="text" name="name_ar" id="name_ar" value="{{ old('name_ar', $dosage_form->name_ar) }}" required>
        </div>
        <div class="form-group">
            <label for="is_active">هل فعال</label>
            <select name="is_active" id="is_active">
                <option value="1" {{ old('is_active', $dosage_form->is_active) == 1 ? 'selected' : '' }}>نعم</option>
                <option value="0" {{ old('is_active', $dosage_form->is_active) == 0 ? 'selected' : '' }}>لا</option>
            </select>
        </div>
        <button type="submit" class="admin-btn">تعديل</button>
    </form>
</div>
@stop
