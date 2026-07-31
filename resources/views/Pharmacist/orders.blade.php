@extends('layouts.pharmacist')
@section('title', 'الطلبات')
@section('content')

@php
    $statusLabels = [
        'pending' => ['bg-warning text-dark', 'في الانتظار'],
        'confirmed' => ['bg-info text-dark', 'مؤكد'],
        'processing' => ['bg-primary', 'قيد التحضير'],
        'ready' => ['bg-secondary', 'جاهز للاستلام'],
        'delivered' => ['bg-success', 'تم التسليم'],
        'cancelled' => ['bg-danger', 'ملغي'],
    ];
    $nextActions = [
        'pending' => [['confirmed', 'تأكيد', 'btn-primary'], ['cancelled', 'إلغاء', 'btn-outline-danger']],
        'confirmed' => [['processing', 'بدء التحضير', 'btn-primary'], ['cancelled', 'إلغاء', 'btn-outline-danger']],
        'processing' => [['ready', 'جاهز للاستلام', 'btn-primary'], ['cancelled', 'إلغاء', 'btn-outline-danger']],
        'ready' => [['delivered', 'تم التسليم', 'btn-success']],
        'delivered' => [],
        'cancelled' => [],
    ];
@endphp

<h1 class="h3 fw-bold mb-1">🛒 طلبات العملاء</h1>
<p class="text-muted mb-4">طلبات العملاء لصيدليتك — الدفع عند الاستلام</p>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body">
        @forelse ($orders as $order)
            <div class="border-bottom pb-3 mb-3">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                    <div>
                        <span class="fw-bold">#{{ $order->id }}</span>
                        <span class="badge {{ $statusLabels[$order->status][0] }} ms-2">{{ $statusLabels[$order->status][1] }}</span>
                        <div class="text-muted small mt-1">{{ $order->user->name }} — {{ $order->phone }}</div>
                        <div class="text-muted small">📍 {{ $order->delivery_address }}</div>
                        @if($order->notes)
                            <div class="text-muted small">📝 {{ $order->notes }}</div>
                        @endif
                    </div>
                    <div class="text-end">
                        <div class="fw-bold">{{ number_format($order->total, 2) }} د.أ</div>
                        <div class="text-muted small">{{ $order->created_at->diffForHumans() }}</div>
                    </div>
                </div>
                <ul class="small mb-2">
                    @foreach ($order->items as $item)
                        <li>{{ $item->name_ar }} × {{ $item->qty }} — {{ number_format($item->price * $item->qty, 2) }} د.أ</li>
                    @endforeach
                </ul>
                @if(count($nextActions[$order->status] ?? []))
                    <div class="d-flex gap-2 flex-wrap">
                        @foreach ($nextActions[$order->status] as [$status, $label, $class])
                            <form method="POST" action="{{ route('pharmacist_order.updateStatus', $order) }}">
                                @csrf
                                <input type="hidden" name="status" value="{{ $status }}">
                                <button type="submit" class="btn btn-sm {{ $class }}">{{ $label }}</button>
                            </form>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center text-muted py-4">
                <div class="fs-1 mb-2">🛒</div>
                <div class="fw-semibold">لا توجد طلبات بعد</div>
            </div>
        @endforelse
    </div>
</div>

@stop
