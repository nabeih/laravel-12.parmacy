<?php

namespace App\Notifications;

use App\Models\PharmacyRequest;
use Illuminate\Notifications\Notification;

class NewPharmacyRequestSubmitted extends Notification
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
        $pharmacistName = $this->pharmacyRequest->pharmacist->users->name ?? '-';

        return [
            'title' => 'طلب تسجيل صيدلية جديدة',
            'body' => "قام {$pharmacistName} بطلب تسجيل صيدلية \"{$this->pharmacyRequest->name_ar}\" وينتظر الموافقة.",
            'url' => route('admin.pharmacy_request.review', $this->pharmacyRequest->id),
        ];
    }
}
