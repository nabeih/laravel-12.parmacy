@extends('layouts.user')
@section('title', 'إتمام الطلب')
@section('content')

<h1 class="h3 fw-bold mb-1">🧾 إتمام الطلب</h1>
<p class="text-muted mb-4">راجع طلبك وأدخل عنوان التوصيل — الدفع عند الاستلام</p>

<div id="checkout-main">
    <div class="row g-4">
        <div class="col-md-7">
            <div class="card mb-3">
                <div id="cart-pharmacy-name" class="fw-semibold mb-3"></div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>الدواء</th>
                                <th>الكمية</th>
                                <th>السعر</th>
                                <th>الإجمالي</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="checkout-items"></tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card">
                <div class="form-group">
                    <label>عنوان التوصيل <span class="required">*</span></label>
                    <input type="text" id="co-address" class="form-control" placeholder="المدينة، الحي، الشارع">
                </div>
                <div class="form-group">
                    <label>رقم الهاتف <span class="required">*</span></label>
                    <input type="text" id="co-phone" class="form-control" placeholder="05XXXXXXXX">
                </div>
                <div class="form-group">
                    <label>ملاحظات (اختياري)</label>
                    <textarea id="co-notes" class="form-control"></textarea>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">الإجمالي</span>
                    <strong id="co-total" style="color:var(--primary);font-size:18px">₪0.00</strong>
                </div>
                <div id="co-msg" class="error-msg mb-2"></div>
                <button type="button" class="btn btn-primary btn-full" id="co-confirm">✓ تأكيد الطلب (الدفع عند الاستلام)</button>
            </div>
        </div>
    </div>
</div>

@stop

@push('scripts')
    <script src="{{ asset('assest_pharmacy/js/cart.js') }}"></script>
    <script src="{{ asset('assest_pharmacy/js/user/checkout.js') }}"></script>
    <script>document.addEventListener('DOMContentLoaded', initCheckout);</script>
@endpush
