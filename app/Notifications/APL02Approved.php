<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class APL02Approved extends Notification
{
    use Queueable;

    protected $registrationId;

    public function __construct($registrationId)
    {
        $this->registrationId = $registrationId;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'FR.APL.02 Disetujui',
            'message' => 'FR.APL.02 Anda telah disetujui. FR.AK.01 sekarang tersedia untuk diisi dan ditandatangani.',
            'url' => route('asesi.ak01', ['pendaftaran_id' => $this->registrationId]),
            'type' => 'approved',
            'metadata' => [
                'registration_id' => $this->registrationId,
                'status' => 'approved',
            ],
        ];
    }
}
