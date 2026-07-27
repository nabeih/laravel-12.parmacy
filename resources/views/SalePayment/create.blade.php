@extends('layouts.pharmacist')
@section('title', 'إضافة دفعة')
@section('content')

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">إضافة دفعة لفاتورة بيع</h1>
        <p class="admin-page-sub">تسجيل تحويل أو إيصال دفع لفاتورة بيع</p>
    </div>
    <div>
        <a href="{{ route('sale_payment.index') }}" class="admin-btn admin-btn-outline">&larr; العودة إلى القائمة</a>
    </div>
</div>

<div class="admin-card">
    <form action="{{ route('sale_payment.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="admin-form-grid">
            <div class="admin-form-group">
                <label class="admin-form-label">فاتورة البيع *</label>
                <select name="sale_id" class="admin-form-select @error('sale_id') error @enderror" required>
                    <option value="">اختر الفاتورة</option>
                    @foreach ($sales as $sale)
                        <option value="{{ $sale->id }}" {{ old('sale_id') == $sale->id ? 'selected' : '' }}>
                            فاتورة #{{ $sale->invoice_number }} — {{ number_format($sale->final_amount, 2) }}
                        </option>
                    @endforeach
                </select>
                @error('sale_id')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">طريقة الدفع *</label>
                <input type="text" name="payment_method" class="admin-form-input @error('payment_method') error @enderror"
                    value="{{ old('payment_method') }}" placeholder="نقداً / تحويل بنكي / ..." required>
                @error('payment_method')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">قناة التحويل</label>
                <select name="transaction_id" class="admin-form-select @error('transaction_id') error @enderror">
                    <option value="">بدون</option>
                    <option value="bank_transfer" {{ old('transaction_id') == 'bank_transfer' ? 'selected' : '' }}>تحويل بنكي</option>
                    <option value="palbay" {{ old('transaction_id') == 'palbay' ? 'selected' : '' }}>PalPay</option>
                    <option value="jawalbay" {{ old('transaction_id') == 'jawalbay' ? 'selected' : '' }}>JawalPay</option>
                </select>
                @error('transaction_id')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">رقم المرجع</label>
                <input type="text" name="reference_number" class="admin-form-input" value="{{ old('reference_number') }}">
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">اسم المرسل</label>
                <input type="text" name="sender_name" class="admin-form-input" value="{{ old('sender_name') }}">
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">تاريخ الدفع *</label>
                <input type="date" name="payment_date" class="admin-form-input @error('payment_date') error @enderror"
                    value="{{ old('payment_date') }}" required>
                @error('payment_date')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">صورة الإيصال</label>
                <input type="file" name="receipt_image" class="admin-form-input @error('receipt_image') error @enderror">
                @error('receipt_image')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="admin-form-group">
            <label class="admin-form-label">ملاحظات</label>
            <textarea name="notes" class="admin-form-textarea">{{ old('notes') }}</textarea>
        </div>

        <div class="admin-modal-footer" style="border-top:none;padding-top:0">
            <a href="{{ route('sale_payment.index') }}" class="admin-btn admin-btn-outline">إلغاء</a>
            <button type="submit" class="admin-btn admin-btn-primary">💾 حفظ</button>
        </div>
    </form>
</div>

@stop
