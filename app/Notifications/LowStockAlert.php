<?php

namespace App\Notifications;

use App\Models\Batch;
use Illuminate\Notifications\Notification;

class LowStockAlert extends Notification
{
    public function __construct(protected Batch $batch)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $medicineName = $this->batch->medicines->brand_name_ar ?? '-';

        return [
            'title' => 'مخزون منخفض',
            'body' => "الكمية المتبقية من \"{$medicineName}\" منخفضة ({$this->batch->quantity} وحدة فقط).",
            'url' => route('batch.index'),
        ];
    }
}
