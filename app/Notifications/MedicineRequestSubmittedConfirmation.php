<?php

namespace App\Notifications;

use App\Models\MedicineRequest;
use Illuminate\Notifications\Notification;

class MedicineRequestSubmittedConfirmation extends Notification
{
    public function __construct(protected MedicineRequest $medicineRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'طلبك قيد المراجعة',
            'body' => "تم استلام طلبك لإضافة \"{$this->medicineRequest->brand_name_ar}\" وهو الآن قيد المراجعة من قبل الإدارة.",
            'url' => route('medicine_request.index'),
        ];
    }
}
