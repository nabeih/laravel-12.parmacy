<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;

class OrderStatusUpdated extends Notification
{
    private const STATUS_LABELS = [
        'pending' => 'في الانتظار',
        'confirmed' => 'مؤكد',
        'processing' => 'قيد التحضير',
        'ready' => 'جاهز للاستلام',
        'delivered' => 'تم التسليم',
        'cancelled' => 'ملغي',
    ];

    public function __construct(protected Order $order)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $label = self::STATUS_LABELS[$this->order->status] ?? $this->order->status;

        return [
            'title' => 'تحديث حالة الطلب',
            'body' => "أصبحت حالة طلبك #{$this->order->id} الآن: {$label}.",
            'url' => route('user.orders'),
        ];
    }
}
