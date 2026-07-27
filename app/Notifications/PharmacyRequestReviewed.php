<?php

namespace App\Notifications;

use App\Models\PharmacyRequest;
use Illuminate\Notifications\Notification;

class PharmacyRequestReviewed extends Notification
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
        $approved = $this->pharmacyRequest->status === 'approved';

        $body = $approved
            ? "تمت الموافقة على تسجيل صيدلية \"{$this->pharmacyRequest->name_ar}\" وأصبحت متاحة الآن."
            : "تم رفض طلب تسجيل صيدلية \"{$this->pharmacyRequest->name_ar}\"" . ($this->pharmacyRequest->admin_notes ? " — {$this->pharmacyRequest->admin_notes}" : '.');

        return [
            'title' => $approved ? 'تمت الموافقة على صيدليتك' : 'تم رفض طلب الصيدلية',
            'body' => $body,
            'url' => route('pharmacy_request.index'),
        ];
    }
}
