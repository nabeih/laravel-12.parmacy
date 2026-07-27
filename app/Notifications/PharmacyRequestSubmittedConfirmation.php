<?php

namespace App\Notifications;

use App\Models\PharmacyRequest;
use Illuminate\Notifications\Notification;

class PharmacyRequestSubmittedConfirmation extends Notification
{
    public function __construct(protected PharmacyRequest $pharmacyRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'طلب الصيدلية قيد المراجعة',
            'body' => "تم استلام طلب تسجيل صيدلية \"{$this->pharmacyRequest->name_ar}\" وهو الآن قيد المراجعة من قبل الإدارة.",
            'url' => route('pharmacy_request.index'),
        ];
    }
}
