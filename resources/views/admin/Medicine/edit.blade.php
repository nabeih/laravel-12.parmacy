@extends('layouts.nav_admin')
@section('title', 'الادوية')
@section('content')
<h1>تعديل الدواء</h1>
<form action="{{ route('medicine.update', $medicine->id) }}" method="POST">

    @csrf
    @method('PUT')
    <div class="form-group @error('brand_name_en') has-error @enderror">
        <label for="brand_name_en">الاسم بالانجليزي:</label>
        <input type="text" name="brand_name_en" id="brand_name_en" value="{{ old('brand_name_en', $medicine->brand_name_en) }}" required>
    </div>
    @error('brand_name_en')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror

    <div class="form-group @error('brand_name_ar') has-error @enderror">
        <label for="brand_name_ar">الاسم بالعربي:</label>
        <input type="text" name="brand_name_ar" id="brand_name_ar" value="{{ old('brand_name_ar', $medicine->brand_name_ar) }}" required>
    </div>
    @error('brand_name_ar')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror

    <div class="form-group @error('manufacturer') has-error @enderror">
        <label for="manufacturer">الشركة المصنعة:</label>
        <select name="manufacturer" id="manufacturer" required>
            @foreach ($manufacturers as $manufacturer)
                <option value="{{ $manufacturer->id }}" {{ old('manufacturer', $medicine->manufacturer_id) == $manufacturer->id ? 'selected' : '' }}>
                    {{ $manufacturer->name_en }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-group @error('category') has-error @enderror">
        <label for="category">التصنيف:</label>
        <select name="category" id="category" required>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" {{ old('category', $medicine->category_id) == $category->id ? 'selected' : '' }}>
                    {{ $category->name_en }}
                </option>
            @endforeach
        </select>
    </div>
    <hr>

    <h3>المواد الفعالة</h3>

    <table id="ingredientsTable" border="1" width="100%" cellpadding="5">
        <thead>
            <tr>
                <th>المادة الفعالة</th>
                <th>التركيز</th>
                <th>الوحدة</th>
                <th>حذف</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($medicine->activeIngredients as $medIngredient)
                <tr>
                    <td>
                        <select name="active_ingredient[]" required>
                            <option value="">اختر المادة الفعالة</option>
                            @foreach($active_ingredients as $ingredient)
                                <option value="{{ $ingredient->id }}" {{ $medIngredient->id == $ingredient->id ? 'selected' : '' }}>
                                    {{ $ingredient->name_en }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" step="0.01" name="strength_value[]" value="{{ $medIngredient->pivot->strength_value }}" required>
                    </td>
                    <td>
                        <select name="strength_unit[]" required>
                            <option value="mg" {{ $medIngredient->pivot->strength_unit == 'mg' ? 'selected' : '' }}>mg</option>
                            <option value="g" {{ $medIngredient->pivot->strength_unit == 'g' ? 'selected' : '' }}>g</option>
                            <option value="mcg" {{ $medIngredient->pivot->strength_unit == 'mcg' ? 'selected' : '' }}>mcg</option>
                            <option value="ml" {{ $medIngredient->pivot->strength_unit == 'ml' ? 'selected' : '' }}>ml</option>
                        </select>
                    </td>
                    <td>
                        <button type="button" class="removeRow">حذف</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td>
                        <select name="active_ingredient[]" required>
                            <option value="">اختر المادة الفعالة</option>
                            @foreach($active_ingredients as $ingredient)
                                <option value="{{ $ingredient->id }}">
                                    {{ $ingredient->name_en }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" step="0.01" name="strength_value[]" required>
                    </td>
                    <td>
                        <select name="strength_unit[]" required>
                            <option value="mg">mg</option>
                            <option value="g">g</option>
                            <option value="mcg">mcg</option>
                            <option value="ml">ml</option>
                        </select>
                    </td>
                    <td>
                        <button type="button" class="removeRow">حذف</button>
                    </td>
                </tr>
            @endforelse
        </tbody>

    </table>

    <br>

    <button type="button" id="addRow">
        + إضافة مادة فعالة
    </button>
    <div class="form-group @error('dosage_form') has-error @enderror ">
        <label for="dosage_form">شكل الدواء:</label>
        <select name="dosage_form" id="dosage_form" required>
            @foreach ($dosage_forms as $form)
                <option value="{{ $form->id }}" {{ old('dosage_form', $medicine->dosage_form_id) == $form->id ? 'selected' : '' }}>
                    {{ $form->name_en }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="form-group @error('reference_price') has-error @enderror">
        <label for="reference_price">التسعيرة:</label>
        <input type="number" name="reference_price" id="reference_price" step="0.01"
            value="{{ old('reference_price', $medicine->reference_price) }}" required>
    </div>
    @error('reference_price')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror

    <div class="form-group @error('barcode') has-error @enderror">
        <label for="barcode">الباركود:</label>
        <input type="text" name="barcode" id="barcode" value="{{ old('barcode', $medicine->barcode) }}" required>
    </div>
    @error('barcode')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror


    <div class="form-group @error('requires_prescription') has-error @enderror">
        <label for="requires_prescription">يتطلب وصفة طبية:</label>
        <input type="checkbox" value="1" name="requires_prescription" id="requires_prescription" {{ old('requires_prescription', $medicine->requires_prescription) ? 'checked' : '' }}>
    </div>
    @error('requires_prescription')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror


    <div class="form-group @error('is_active') has-error @enderror">
        <label for="is_active">هل فعال:</label>
        <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $medicine->is_active) ? 'checked' : '' }}>
    </div>
    @error('is_active')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror

    <div class="form-group @error('description_en') has-error @enderror">
        <label for="description_en">الوصف بالانجليزي:</label>
        <textarea name="description_en" id="description_en">{{ old('description_en', $medicine->description_en) }}</textarea>
    </div>

    <div class="form-group @error('description_ar') has-error @enderror">
        <label for="description_ar">الوصف بالعربي:</label>
        <textarea name="description_ar" id="description_ar">{{ old('description_ar', $medicine->description_ar) }}</textarea>
    </div>
    <div class="form-group @error('notes') has-error @enderror">
        <label for="notes">ملاحظة:</label>
        <textarea name="notes" id="notes">{{ old('notes', $medicine->notes) }}</textarea>
    </div>
    @error('notes')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror


    <button type="submit">حفظ التعديل</button>


</form>
<script>

    document.getElementById('addRow').addEventListener('click', function () {

        let tbody = document.querySelector('#ingredientsTable tbody');

        let row = tbody.rows[0].cloneNode(true);

        row.querySelectorAll('input').forEach(function (input) {

            input.value = '';

        });

        row.querySelectorAll('select').forEach(function (select) {

            select.selectedIndex = 0;

        });

        tbody.appendChild(row);

    });

    document.addEventListener('click', function (e) {

        if (e.target.classList.contains('removeRow')) {

            let rows = document.querySelectorAll('#ingredientsTable tbody tr');

            if (rows.length > 1) {

                e.target.closest('tr').remove();

            }

        }

    });

</script>
@stop
