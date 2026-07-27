@extends('layouts.pharmacist')
@section('title', 'إضافة فاتورة شراء')
@section('content')

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">إضافة فاتورة شراء</h1>
        <p class="admin-page-sub">تسجيل فاتورة شراء جديدة من مورد وإضافة الأصناف</p>
    </div>
    <div>
        <a href="{{ route('purchase.index') }}" class="admin-btn admin-btn-outline">&larr; العودة إلى القائمة</a>
    </div>
</div>

<div class="admin-card">
    <form action="{{ route('purchase.store') }}" method="POST">
        @csrf

        <div class="admin-form-grid">
            <div class="admin-form-group">
                <label class="admin-form-label">رقم الفاتورة *</label>
                <input type="text" name="invoice_number" class="admin-form-input @error('invoice_number') error @enderror"
                    value="{{ old('invoice_number') }}" required>
                @error('invoice_number')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">اسم المورد *</label>
                <input type="text" name="supplier_name" class="admin-form-input @error('supplier_name') error @enderror"
                    value="{{ old('supplier_name') }}" required>
                @error('supplier_name')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">تاريخ الشراء *</label>
                <input type="date" name="purchase_date" class="admin-form-input @error('purchase_date') error @enderror"
                    value="{{ old('purchase_date') }}" required>
                @error('purchase_date')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

        </div>

        <div class="admin-form-group">
            <label class="admin-form-label">ملاحظات</label>
            <textarea name="notes" class="admin-form-textarea">{{ old('notes') }}</textarea>
        </div>

        <div class="admin-section">
            <div class="admin-section-title">💊 أصناف الفاتورة</div>

            <div class="admin-table-wrap">
                <table class="admin-table" id="itemsTable">
                    <thead>
                        <tr>
                            <th>الدواء</th>
                            <th>الشركة المصنعة</th>
                            <th>التصنيف</th>
                            <th>شكل الدواء</th>
                            <th>المادة الفعالة</th>
                            <th>الباركود</th>
                            <th>الكمية</th>
                            <th>سعر الشراء</th>
                            <th>سعر البيع</th>
                            <th>رقم الدفعة/اللوط</th>
                            <th>تاريخ التصنيع</th>
                            <th>تاريخ الانتهاء</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $oldMedicineIds = old('medicine_id', [null]); @endphp
                        @foreach ($oldMedicineIds as $i => $oldMedicineId)
                            <tr>
                                <td>
                                    <select name="medicine_id[]" class="admin-form-select medicine-select" required>
                                        <option value="">اختر الدواء</option>
                                        @foreach ($medicines as $medicine)
                                            <option value="{{ $medicine->id }}"
                                                data-manufacturer="{{ $medicine->manufacturer->name_ar ?? '-' }}"
                                                data-category="{{ $medicine->category->name_ar ?? '-' }}"
                                                data-dosage-form="{{ $medicine->dosageForm->name_ar ?? '-' }}"
                                                data-active-ingredient="{{ $medicine->activeIngredients->map(fn($ai) => $ai->name_ar . ' ' . rtrim(rtrim(number_format($ai->pivot->strength_value, 2), '0'), '.') . $ai->pivot->strength_unit)->implode('، ') ?: '-' }}"
                                                data-barcode="{{ $medicine->barcode ?? '-' }}"
                                                data-reference-price="{{ $medicine->reference_price }}"
                                                {{ $oldMedicineId == $medicine->id ? 'selected' : '' }}>
                                                {{ $medicine->brand_name_en }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="med-manufacturer text-muted">-</td>
                                <td class="med-category text-muted">-</td>
                                <td class="med-dosage-form text-muted">-</td>
                                <td class="med-active-ingredient text-muted">-</td>
                                <td class="med-barcode text-muted">-</td>
                                <td>
                                    <input type="number" min="1" name="quantity[]" class="admin-form-input @error('quantity.' . $i) error @enderror"
                                        value="{{ old('quantity.' . $i) }}" required>
                                    @error('quantity.' . $i)<span class="admin-form-error">{{ $message }}</span>@enderror
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" name="purchase_price[]" class="admin-form-input @error('purchase_price.' . $i) error @enderror"
                                        value="{{ old('purchase_price.' . $i) }}" required>
                                    @error('purchase_price.' . $i)<span class="admin-form-error">{{ $message }}</span>@enderror
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" name="selling_price[]"
                                        class="admin-form-input selling-price-input @error('selling_price.' . $i) error @enderror" value="{{ old('selling_price.' . $i) }}" required>
                                    @error('selling_price.' . $i)<span class="admin-form-error">{{ $message }}</span>@enderror
                                </td>
                                <td>
                                    <input type="text" name="lot_number[]" class="admin-form-input @error('lot_number.' . $i) error @enderror" placeholder="اختياري"
                                        value="{{ old('lot_number.' . $i) }}">
                                    @error('lot_number.' . $i)<span class="admin-form-error">{{ $message }}</span>@enderror
                                </td>
                                <td>
                                    <input type="date" name="manufacturing_date[]" class="admin-form-input @error('manufacturing_date.' . $i) error @enderror"
                                        value="{{ old('manufacturing_date.' . $i) }}">
                                    @error('manufacturing_date.' . $i)<span class="admin-form-error">{{ $message }}</span>@enderror
                                </td>
                                <td>
                                    <input type="date" name="expiry_date[]" class="admin-form-input @error('expiry_date.' . $i) error @enderror"
                                        value="{{ old('expiry_date.' . $i) }}" required>
                                    @error('expiry_date.' . $i)<span class="admin-form-error">{{ $message }}</span>@enderror
                                </td>
                                <td><button type="button" class="admin-btn admin-btn-sm admin-btn-danger removeRow">حذف</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button type="button" id="addRow" class="admin-btn admin-btn-outline" style="margin-top:10px">+ إضافة صنف</button>
        </div>

        <div class="admin-modal-footer" style="border-top:none;padding-top:0">
            <a href="{{ route('purchase.index') }}" class="admin-btn admin-btn-outline">إلغاء</a>
            <button type="submit" class="admin-btn admin-btn-primary">💾 حفظ الفاتورة</button>
        </div>
    </form>
</div>

<script>
    function fillMedicineReadonly(row) {
        let select = row.querySelector('.medicine-select');
        let opt = select.options[select.selectedIndex];

        row.querySelector('.med-manufacturer').textContent = opt && opt.value ? (opt.dataset.manufacturer || '-') : '-';
        row.querySelector('.med-category').textContent = opt && opt.value ? (opt.dataset.category || '-') : '-';
        row.querySelector('.med-dosage-form').textContent = opt && opt.value ? (opt.dataset.dosageForm || '-') : '-';
        row.querySelector('.med-active-ingredient').textContent = opt && opt.value ? (opt.dataset.activeIngredient || '-') : '-';
        row.querySelector('.med-barcode').textContent = opt && opt.value ? (opt.dataset.barcode || '-') : '-';
    }

    // Only called when the pharmacist actively picks/changes a medicine — resets
    // the selling price to the catalog default. Must NOT run on page load, or it
    // would clobber a selling price restored after a validation error.
    function fillMedicineDetails(row) {
        fillMedicineReadonly(row);
        let select = row.querySelector('.medicine-select');
        let opt = select.options[select.selectedIndex];
        let priceInput = row.querySelector('.selling-price-input');
        if (opt && opt.value) {
            priceInput.value = Number(opt.dataset.referencePrice || 0).toFixed(2);
        }
    }

    document.getElementById('itemsTable').addEventListener('change', function (e) {
        if (e.target.classList.contains('medicine-select')) {
            fillMedicineDetails(e.target.closest('tr'));
        }
    });

    // Restore the read-only catalog info for any rows brought back after a
    // validation error (their medicine select is already pre-selected server-side).
    document.querySelectorAll('#itemsTable tbody tr').forEach(function (row) {
        if (row.querySelector('.medicine-select').value) {
            fillMedicineReadonly(row);
        }
    });

    document.getElementById('addRow').addEventListener('click', function () {
        let tbody = document.querySelector('#itemsTable tbody');
        let row = tbody.rows[0].cloneNode(true);
        row.querySelectorAll('input').forEach(input => input.value = '');
        row.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
        row.querySelector('.med-manufacturer').textContent = '-';
        row.querySelector('.med-category').textContent = '-';
        row.querySelector('.med-dosage-form').textContent = '-';
        row.querySelector('.med-active-ingredient').textContent = '-';
        row.querySelector('.med-barcode').textContent = '-';
        tbody.appendChild(row);
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('removeRow')) {
            let rows = document.querySelectorAll('#itemsTable tbody tr');
            if (rows.length > 1) {
                e.target.closest('tr').remove();
            }
        }
    });
</script>

@stop
