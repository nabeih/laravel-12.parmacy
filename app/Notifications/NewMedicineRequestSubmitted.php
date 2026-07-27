<?php

namespace App\Notifications;

use App\Models\MedicineRequest;
use Illuminate\Notifications\Notification;

class NewMedicineRequestSubmitted extends Notification
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
        $pharmacyName = $this->medicineRequest->pharmacy->name_ar ?? '-';

        return [
            'title' => 'طلب دواء جديد',
            'body' => "صيدلية {$pharmacyName} طلبت إضافة \"{$this->medicineRequest->brand_name_ar}\" إلى الكتالوج.",
            'url' => route('admin.medicine_request.review', $this->medicineRequest->id),
        ];
    }
}
