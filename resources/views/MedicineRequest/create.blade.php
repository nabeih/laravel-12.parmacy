@extends('layouts.pharmacist')
@section('title', 'طلب دواء جديد')
@section('content')

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">طلب إضافة دواء جديد</h1>
        <p class="admin-page-sub">سيتم إرسال الطلب إلى الإدارة لمراجعته وإضافته إلى الكتالوج الرئيسي عند الموافقة</p>
    </div>
    <div>
        <a href="{{ route('medicine_request.index') }}" class="admin-btn admin-btn-outline">&larr; العودة إلى الطلبات</a>
    </div>
</div>

<div class="admin-card">
    <form action="{{ route('medicine_request.store') }}" method="POST">
        @csrf

        <div class="admin-form-grid">
            <div class="admin-form-group">
                <label class="admin-form-label">الاسم بالإنجليزي *</label>
                <input type="text" name="brand_name_en" class="admin-form-input @error('brand_name_en') error @enderror"
                    value="{{ old('brand_name_en') }}" required>
                @error('brand_name_en')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">الاسم بالعربي *</label>
                <input type="text" name="brand_name_ar" class="admin-form-input @error('brand_name_ar') error @enderror"
                    value="{{ old('brand_name_ar') }}" required>
                @error('brand_name_ar')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">الشركة المصنعة *</label>
                <select name="manufacturer" class="admin-form-select @error('manufacturer') error @enderror" required>
                    <option value="">اختر الشركة المصنعة</option>
                    @foreach ($manufacturers as $manufacturer)
                        <option value="{{ $manufacturer->id }}" {{ old('manufacturer') == $manufacturer->id ? 'selected' : '' }}>
                            {{ $manufacturer->name_ar }}
                        </option>
                    @endforeach
                </select>
                @error('manufacturer')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">التصنيف *</label>
                <select name="category" class="admin-form-select @error('category') error @enderror" required>
                    <option value="">اختر التصنيف</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name_ar }}
                        </option>
                    @endforeach
                </select>
                @error('category')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">شكل الدواء *</label>
                <select name="dosage_form" class="admin-form-select @error('dosage_form') error @enderror" required>
                    <option value="">اختر شكل الدواء</option>
                    @foreach ($dosage_forms as $form)
                        <option value="{{ $form->id }}" {{ old('dosage_form') == $form->id ? 'selected' : '' }}>
                            {{ $form->name_ar }}
                        </option>
                    @endforeach
                </select>
                @error('dosage_form')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">السعر المرجعي *</label>
                <input type="number" step="0.01" min="0" name="reference_price"
                    class="admin-form-input @error('reference_price') error @enderror" value="{{ old('reference_price') }}" required>
                @error('reference_price')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">الباركود</label>
                <input type="text" name="barcode" class="admin-form-input @error('barcode') error @enderror"
                    value="{{ old('barcode') }}">
                @error('barcode')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group" style="display:flex;align-items:center;padding-top:24px">
                <label class="admin-form-check">
                    <input type="checkbox" name="requires_prescription" value="1" {{ old('requires_prescription') ? 'checked' : '' }}>
                    يتطلب وصفة طبية
                </label>
            </div>
        </div>

        <div class="admin-section">
            <div class="admin-section-title">🧪 المواد الفعالة</div>
            <div class="admin-table-wrap">
                <table class="admin-table" id="ingredientsTable">
                    <thead>
                        <tr>
                            <th>المادة الفعالة</th>
                            <th>التركيز</th>
                            <th>الوحدة</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $oldIngredientIds = old('active_ingredient', [null]); @endphp
                        @foreach ($oldIngredientIds as $i => $oldIngredientId)
                            <tr>
                                <td>
                                    <select name="active_ingredient[]" class="admin-form-select @error('active_ingredient.' . $i) error @enderror" required>
                                        <option value="">اختر المادة الفعالة</option>
                                        @foreach ($active_ingredients as $ingredient)
                                            <option value="{{ $ingredient->id }}" {{ $oldIngredientId == $ingredient->id ? 'selected' : '' }}>
                                                {{ $ingredient->name_ar }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('active_ingredient.' . $i)<span class="admin-form-error">{{ $message }}</span>@enderror
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" name="strength_value[]" class="admin-form-input @error('strength_value.' . $i) error @enderror"
                                        value="{{ old('strength_value.' . $i) }}" required>
                                    @error('strength_value.' . $i)<span class="admin-form-error">{{ $message }}</span>@enderror
                                </td>
                                <td>
                                    <select name="strength_unit[]" class="admin-form-select @error('strength_unit.' . $i) error @enderror" required>
                                        @foreach (['mg', 'g', 'mcg', 'ml'] as $unit)
                                            <option value="{{ $unit }}" {{ old('strength_unit.' . $i) == $unit ? 'selected' : '' }}>{{ $unit }}</option>
                                        @endforeach
                                    </select>
                                    @error('strength_unit.' . $i)<span class="admin-form-error">{{ $message }}</span>@enderror
                                </td>
                                <td><button type="button" class="admin-btn admin-btn-sm admin-btn-danger removeRow">حذف</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button type="button" id="addRow" class="admin-btn admin-btn-outline" style="margin-top:10px">+ إضافة مادة فعالة</button>
        </div>

        <div class="admin-form-group" style="margin-top:16px">
            <label class="admin-form-label">الوصف بالإنجليزي</label>
            <textarea name="description_en" class="admin-form-textarea">{{ old('description_en') }}</textarea>
        </div>

        <div class="admin-form-group">
            <label class="admin-form-label">الوصف بالعربي</label>
            <textarea name="description_ar" class="admin-form-textarea">{{ old('description_ar') }}</textarea>
        </div>

        <div class="admin-form-group">
            <label class="admin-form-label">ملاحظات لسبب الطلب (اختياري)</label>
            <textarea name="notes" class="admin-form-textarea" placeholder="لماذا تحتاج هذا الدواء في الكتالوج؟">{{ old('notes') }}</textarea>
        </div>

        <div class="admin-modal-footer" style="border-top:none;padding-top:0">
            <a href="{{ route('medicine_request.index') }}" class="admin-btn admin-btn-outline">إلغاء</a>
            <button type="submit" class="admin-btn admin-btn-primary">📤 إرسال الطلب للمراجعة</button>
        </div>
    </form>
</div>

<script>
    document.getElementById('addRow').addEventListener('click', function () {
        let tbody = document.querySelector('#ingredientsTable tbody');
        let row = tbody.rows[0].cloneNode(true);
        row.querySelectorAll('input').forEach(input => input.value = '');
        row.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
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
