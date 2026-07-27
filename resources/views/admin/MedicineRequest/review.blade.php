@extends('layouts.nav_admin')
@section('title', 'مراجعة طلب دواء')
@section('content')

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">مراجعة طلب دواء</h1>
        <p class="admin-page-sub">
            مُقدَّم من {{ $medicineRequest->requestedBy->name ?? '-' }} — صيدلية {{ $medicineRequest->pharmacy->name_ar ?? '-' }}
            @if($medicineRequest->notes)
                <br>ملاحظة الصيدلي: {{ $medicineRequest->notes }}
            @endif
        </p>
    </div>
    <div>
        <a href="{{ route('admin.medicine_request.index') }}" class="admin-btn admin-btn-outline">&larr; العودة إلى القائمة</a>
    </div>
</div>

<div class="admin-card">
    <div class="admin-section-title">✅ الموافقة وإضافة الدواء إلى الكتالوج</div>
    <p class="admin-page-sub" style="margin-bottom:16px">راجع البيانات التالية وعدّلها إذا لزم قبل إضافتها نهائياً إلى الكتالوج الرئيسي.</p>

    <form action="{{ route('admin.medicine_request.approve', $medicineRequest->id) }}" method="POST">
        @csrf

        <div class="admin-form-grid">
            <div class="admin-form-group">
                <label class="admin-form-label">الاسم بالإنجليزي *</label>
                <input type="text" name="brand_name_en" class="admin-form-input @error('brand_name_en') error @enderror"
                    value="{{ old('brand_name_en', $medicineRequest->brand_name_en) }}" required>
                @error('brand_name_en')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">الاسم بالعربي *</label>
                <input type="text" name="brand_name_ar" class="admin-form-input @error('brand_name_ar') error @enderror"
                    value="{{ old('brand_name_ar', $medicineRequest->brand_name_ar) }}" required>
                @error('brand_name_ar')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">الشركة المصنعة *</label>
                <select name="manufacturer" class="admin-form-select @error('manufacturer') error @enderror" required>
                    @foreach ($manufacturers as $manufacturer)
                        <option value="{{ $manufacturer->id }}"
                            {{ old('manufacturer', $medicineRequest->manufacturer_id) == $manufacturer->id ? 'selected' : '' }}>
                            {{ $manufacturer->name_ar }}
                        </option>
                    @endforeach
                </select>
                @error('manufacturer')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">التصنيف *</label>
                <select name="category" class="admin-form-select @error('category') error @enderror" required>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ old('category', $medicineRequest->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name_ar }}
                        </option>
                    @endforeach
                </select>
                @error('category')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">شكل الدواء *</label>
                <select name="dosage_form" class="admin-form-select @error('dosage_form') error @enderror" required>
                    @foreach ($dosage_forms as $form)
                        <option value="{{ $form->id }}"
                            {{ old('dosage_form', $medicineRequest->dosage_form_id) == $form->id ? 'selected' : '' }}>
                            {{ $form->name_ar }}
                        </option>
                    @endforeach
                </select>
                @error('dosage_form')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">السعر المرجعي *</label>
                <input type="number" step="0.01" min="0" name="reference_price"
                    class="admin-form-input @error('reference_price') error @enderror"
                    value="{{ old('reference_price', $medicineRequest->reference_price) }}" required>
                @error('reference_price')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">الباركود</label>
                <input type="text" name="barcode" class="admin-form-input @error('barcode') error @enderror"
                    value="{{ old('barcode', $medicineRequest->barcode) }}">
                @error('barcode')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group" style="display:flex;align-items:center;padding-top:24px">
                <label class="admin-form-check">
                    <input type="checkbox" name="requires_prescription" value="1"
                        {{ old('requires_prescription', $medicineRequest->requires_prescription) ? 'checked' : '' }}>
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
                        @foreach ($medicineRequest->activeIngredients as $reqIngredient)
                            <tr>
                                <td>
                                    <select name="active_ingredient[]" class="admin-form-select" required>
                                        <option value="">اختر المادة الفعالة</option>
                                        @foreach ($active_ingredients as $ingredient)
                                            <option value="{{ $ingredient->id }}"
                                                {{ $reqIngredient->id == $ingredient->id ? 'selected' : '' }}>
                                                {{ $ingredient->name_ar }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" step="0.01" min="0" name="strength_value[]" class="admin-form-input"
                                        value="{{ $reqIngredient->pivot->strength_value }}" required></td>
                                <td>
                                    <select name="strength_unit[]" class="admin-form-select" required>
                                        @foreach (['mg', 'g', 'mcg', 'ml'] as $unit)
                                            <option value="{{ $unit }}" {{ $reqIngredient->pivot->strength_unit == $unit ? 'selected' : '' }}>
                                                {{ $unit }}
                                            </option>
                                        @endforeach
                                    </select>
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
            <textarea name="description_en" class="admin-form-textarea">{{ old('description_en', $medicineRequest->description_en) }}</textarea>
        </div>

        <div class="admin-form-group">
            <label class="admin-form-label">الوصف بالعربي</label>
            <textarea name="description_ar" class="admin-form-textarea">{{ old('description_ar', $medicineRequest->description_ar) }}</textarea>
        </div>

        <div class="admin-form-group">
            <label class="admin-form-label">ملاحظات (تُحفظ على الدواء)</label>
            <textarea name="notes" class="admin-form-textarea">{{ old('notes', $medicineRequest->notes) }}</textarea>
        </div>

        <div class="admin-modal-footer" style="border-top:none;padding-top:0">
            <button type="submit" class="admin-btn admin-btn-primary">✅ الموافقة وإضافة إلى الكتالوج</button>
        </div>
    </form>
</div>

<div class="admin-card">
    <div class="admin-section-title">❌ رفض الطلب</div>
    <form action="{{ route('admin.medicine_request.reject', $medicineRequest->id) }}" method="POST"
        onsubmit="return confirm('هل أنت متأكد من رفض هذا الطلب؟');">
        @csrf
        <div class="admin-form-group">
            <label class="admin-form-label">سبب الرفض (اختياري)</label>
            <textarea name="admin_notes" class="admin-form-textarea"></textarea>
        </div>
        <button type="submit" class="admin-btn admin-btn-danger">رفض الطلب</button>
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
