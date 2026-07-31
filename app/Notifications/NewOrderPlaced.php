<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;

class NewOrderPlaced extends Notification
{
    public function __construct(protected Order $order)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'طلب جديد',
            'body' => "لديك طلب جديد رقم #{$this->order->id} بقيمة {$this->order->total} د.أ.",
            'url' => route('pharmacist_order.index'),
        ];
    }
}
