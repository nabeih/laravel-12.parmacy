<?php

namespace App\Notifications;

use App\Models\Pharmacist;
use Illuminate\Notifications\Notification;

class NewPharmacistRegistered extends Notification
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
        $name = $this->pharmacist->users->name ?? '-';

        return [
            'title' => 'تسجيل صيدلي جديد',
            'body' => "قام {$name} بالتسجيل كصيدلي وينتظر اعتماد حسابه.",
            'url' => route('pharmacist.index'),
        ];
    }
}
