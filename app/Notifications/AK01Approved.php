<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AK01Approved extends Notification
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
            'title' => 'FR.AK.01 Disahkan Asesor',
            'message' => 'Formulir FR.AK.01 Persetujuan Asesmen & Kerahasiaan telah disetujui dan disahkan oleh Asesor Penguji.',
            'url' => route('asesi.ak01', ['id' => $this->registrationId]),
            'type' => 'ak01_approved',
            'metadata' => [
                'registration_id' => $this->registrationId,
                'status' => 'selesai',
            ],
        ];
    }
}
