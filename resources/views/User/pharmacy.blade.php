@extends('layouts.user')
@section('title', $pharmacy->name_ar)
@section('content')

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="h3 fw-bold mb-1">🏪 {{ $pharmacy->name_ar }}</h1>
        <p class="text-muted mb-0">📍 {{ $pharmacy->address }}</p>
    </div>
    <button type="button" class="btn btn-outline" onclick="openCart()">
        🛒 السلة <span id="cart-badge" class="badge badge-primary" style="display:none"></span>
    </button>
</div>

<div class="row g-3">
    @forelse ($items as $item)
        <div class="col-md-4">
            <div class="card h-100">
                <div class="fw-bold mb-1">{{ $item['medicine']->brand_name_ar }}</div>
                <div class="text-muted small mb-2">{{ $item['medicine']->brand_name_en }}</div>
                <div class="fw-semibold mb-2">{{ number_format($item['price'], 2) }} د.أ</div>
                <div class="text-muted small mb-3">متوفر: {{ $item['quantity'] }}</div>
                <button type="button" class="btn btn-primary btn-sm w-100"
                    onclick='_addToCartFromCard({{ $pharmacy->id }}, @json($pharmacy->name_ar), {{ $item["medicine"]->id }}, @json($item["medicine"]->brand_name_ar), @json($item["medicine"]->brand_name_en), {{ $item["price"] }})'>
                    + أضف للسلة
                </button>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="empty-state">
                <span class="empty-icon">💊</span>
                <p>لا توجد أدوية متوفرة حالياً في هذه الصيدلية</p>
            </div>
        </div>
    @endforelse
</div>

@stop

@push('scripts')
    <script src="{{ asset('assest_pharmacy/js/cart.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => { injectCartDrawer(); updateCartBadge(); });

        function _addToCartFromCard(pharmacyId, pharmacyName, medicineId, nameAr, nameEn, price) {
            const medicine = { id: medicineId, nameAr, nameEn, price: Number(price) };
            const result = addToCart(pharmacyId, pharmacyName, medicine);
            if (result.conflict) {
                if (confirm(`سلتك تحتوي على أدوية من "${result.existingPharmacy}". هل تريد إفراغ السلة والبدء من هذه الصيدلية؟`)) {
                    clearCart();
                    addToCart(pharmacyId, pharmacyName, medicine);
                    openCart();
                }
                return;
            }
            showToast('تمت الإضافة للسلة ✓');
            openCart();
        }
    </script>
@endpush
