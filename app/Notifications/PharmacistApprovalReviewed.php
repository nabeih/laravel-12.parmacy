<?php

namespace App\Notifications;

use App\Models\Pharmacist;
use Illuminate\Notifications\Notification;

class PharmacistApprovalReviewed extends Notification
{
    public function __construct(protected Pharmacist $pharmacist)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $approved = $this->pharmacist->status === 'approved';

        $body = $approved
            ? 'تمت الموافقة على حسابك كصيدلي. يمكنك الآن تسجيل صيدليتك أو الانضمام إلى صيدلية شاغرة.'
            : 'تم رفض طلب اعتمادك كصيدلي' . ($this->pharmacist->admin_notes ? " — {$this->pharmacist->admin_notes}" : '.');

        return [
            'title' => $approved ? 'تمت الموافقة على حسابك' : 'تم رفض طلب الاعتماد',
            'body' => $body,
            'url' => route('pharmacist.dashboard'),
        ];
    }
}
