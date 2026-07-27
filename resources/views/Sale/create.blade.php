@extends('layouts.pharmacist')
@section('title', 'إضافة فاتورة بيع')
@section('content')

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">إضافة فاتورة بيع</h1>
        <p class="admin-page-sub">تسجيل عملية بيع جديدة وخصم الكمية من المخزون تلقائياً</p>
    </div>
    <div>
        <a href="{{ route('sale.index') }}" class="admin-btn admin-btn-outline">&larr; العودة إلى القائمة</a>
    </div>
</div>

<div class="admin-card">
    <form action="{{ route('sale.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="admin-form-grid">
            <div class="admin-form-group">
                <label class="admin-form-label">رقم الفاتورة *</label>
                <input type="number" name="invoice_number" class="admin-form-input @error('invoice_number') error @enderror"
                    value="{{ old('invoice_number') }}" required>
                @error('invoice_number')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">طريقة الدفع *</label>
                <select name="payment_method" id="payment_method" class="admin-form-select @error('payment_method') error @enderror" required>
                    <option value="cash" {{ old('payment_method', 'cash') == 'cash' ? 'selected' : '' }}>نقداً</option>
                    <option value="bank" {{ old('payment_method') == 'bank' ? 'selected' : '' }}>تحويل بنكي</option>
                    <option value="palpay" {{ old('payment_method') == 'palpay' ? 'selected' : '' }}>PalPay</option>
                </select>
                @error('payment_method')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group" id="receipt-image-group" style="display:none">
                <label class="admin-form-label">إثبات الدفع (صورة أو PDF) *</label>
                <input type="file" name="receipt_image" class="admin-form-input @error('receipt_image') error @enderror">
                @error('receipt_image')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">الحالة *</label>
                <select name="status" class="admin-form-select @error('status') error @enderror" required>
                    <option value="complete" {{ old('status', 'complete') == 'complete' ? 'selected' : '' }}>مكتملة</option>
                    <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>ملغاة</option>
                </select>
                @error('status')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">الخصم</label>
                <input type="number" step="0.01" min="0" name="discount" id="discount" class="admin-form-input" value="{{ old('discount', 0) }}">
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">الضريبة</label>
                <input type="number" step="0.01" min="0" name="tax" id="tax" class="admin-form-input" value="{{ old('tax', 0) }}">
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
                            <th>المتوفر</th>
                            <th>سعر البيع التقديري</th>
                            <th>الكمية</th>
                            <th>الإجمالي الفرعي</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $oldSaleMedicineIds = old('medicine_id', [null]); @endphp
                        @foreach ($oldSaleMedicineIds as $i => $oldMedicineId)
                            <tr>
                                <td>
                                    <select name="medicine_id[]" class="admin-form-select medicine-select @error('medicine_id.' . $i) error @enderror" required>
                                        <option value="">اختر الدواء</option>
                                        @foreach ($medicines as $row)
                                            <option value="{{ $row->medicine_id }}"
                                                data-quantity="{{ $row->available_quantity }}"
                                                data-price="{{ $row->preview_price }}"
                                                {{ $oldMedicineId == $row->medicine_id ? 'selected' : '' }}>
                                                {{ $row->medicine->brand_name_en ?? ('دواء #' . $row->medicine_id) }}
                                                (متوفر {{ $row->available_quantity }}، أقرب انتهاء {{ $row->next_expiry }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('medicine_id.' . $i)<span class="admin-form-error">{{ $message }}</span>@enderror
                                </td>
                                <td class="qty-available">-</td>
                                <td class="unit-price">-</td>
                                <td>
                                    <input type="number" min="1" name="quantity[]" class="admin-form-input qty-input @error('quantity.' . $i) error @enderror"
                                        value="{{ old('quantity.' . $i) }}" required>
                                    @error('quantity.' . $i)<span class="admin-form-error">{{ $message }}</span>@enderror
                                </td>
                                <td class="row-subtotal">0.00</td>
                                <td><button type="button" class="admin-btn admin-btn-sm admin-btn-danger removeRow">حذف</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button type="button" id="addRow" class="admin-btn admin-btn-outline" style="margin-top:10px">+ إضافة صنف</button>

            <div style="text-align:left;margin-top:14px;font-size:14px;color:#374151">
                الإجمالي المقدر: <strong id="grandTotal">0.00</strong>
            </div>
        </div>

        <div class="admin-modal-footer" style="border-top:none;padding-top:0">
            <a href="{{ route('sale.index') }}" class="admin-btn admin-btn-outline">إلغاء</a>
            <button type="submit" class="admin-btn admin-btn-primary">💾 حفظ الفاتورة</button>
        </div>
    </form>
</div>

<script>
    function recalcRow(row) {
        let select = row.querySelector('.medicine-select');
        let opt = select.options[select.selectedIndex];
        let qtyInput = row.querySelector('.qty-input');
        let available = opt ? (opt.dataset.quantity || 0) : 0;
        let price = opt ? (opt.dataset.price || 0) : 0;

        row.querySelector('.qty-available').textContent = opt && opt.value ? available : '-';
        row.querySelector('.unit-price').textContent = opt && opt.value ? Number(price).toFixed(2) : '-';

        let qty = parseFloat(qtyInput.value) || 0;
        let subtotal = qty * parseFloat(price || 0);
        row.querySelector('.row-subtotal').textContent = subtotal.toFixed(2);

        recalcGrandTotal();
    }

    function recalcGrandTotal() {
        let subtotals = document.querySelectorAll('.row-subtotal');
        let total = 0;
        subtotals.forEach(el => total += parseFloat(el.textContent) || 0);
        let discount = parseFloat(document.getElementById('discount').value) || 0;
        let tax = parseFloat(document.getElementById('tax').value) || 0;
        document.getElementById('grandTotal').textContent = (total - discount + tax).toFixed(2);
    }

    document.getElementById('itemsTable').addEventListener('change', function (e) {
        if (e.target.classList.contains('medicine-select') || e.target.classList.contains('qty-input')) {
            recalcRow(e.target.closest('tr'));
        }
    });
    document.getElementById('itemsTable').addEventListener('input', function (e) {
        if (e.target.classList.contains('qty-input')) {
            recalcRow(e.target.closest('tr'));
        }
    });
    document.getElementById('discount').addEventListener('input', recalcGrandTotal);
    document.getElementById('tax').addEventListener('input', recalcGrandTotal);

    // Restore available-qty/price display for rows brought back after a validation error.
    document.querySelectorAll('#itemsTable tbody tr').forEach(function (row) {
        if (row.querySelector('.medicine-select').value) {
            recalcRow(row);
        }
    });

    document.getElementById('addRow').addEventListener('click', function () {
        let tbody = document.querySelector('#itemsTable tbody');
        let row = tbody.rows[0].cloneNode(true);
        row.querySelectorAll('input').forEach(input => input.value = '');
        row.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
        row.querySelector('.qty-available').textContent = '-';
        row.querySelector('.unit-price').textContent = '-';
        row.querySelector('.row-subtotal').textContent = '0.00';
        tbody.appendChild(row);
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('removeRow')) {
            let rows = document.querySelectorAll('#itemsTable tbody tr');
            if (rows.length > 1) {
                e.target.closest('tr').remove();
                recalcGrandTotal();
            }
        }
    });

    // Proof of payment is only relevant (and required) for non-cash sales.
    function toggleReceiptField() {
        let paymentMethod = document.getElementById('payment_method').value;
        let group = document.getElementById('receipt-image-group');
        let input = group.querySelector('input[type="file"]');
        let isNonCash = paymentMethod !== 'cash';

        group.style.display = isNonCash ? 'block' : 'none';
        input.required = isNonCash;
        if (!isNonCash) {
            input.value = '';
        }
    }

    document.getElementById('payment_method').addEventListener('change', toggleReceiptField);
    toggleReceiptField();
</script>

@stop
