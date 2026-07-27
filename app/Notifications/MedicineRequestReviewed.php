<?php

namespace App\Notifications;

use App\Models\MedicineRequest;
use Illuminate\Notifications\Notification;

class MedicineRequestReviewed extends Notification
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
        $approved = $this->medicineRequest->status === 'approved';

        $body = $approved
            ? "تمت الموافقة على طلبك لإضافة \"{$this->medicineRequest->brand_name_ar}\" وأصبح متاحاً في الكتالوج."
            : "تم رفض طلبك لإضافة \"{$this->medicineRequest->brand_name_ar}\"" . ($this->medicineRequest->admin_notes ? " — {$this->medicineRequest->admin_notes}" : '.');

        return [
            'title' => $approved ? 'تمت الموافقة على طلب الدواء' : 'تم رفض طلب الدواء',
            'body' => $body,
            'url' => route('medicine_request.index'),
        ];
    }
}
