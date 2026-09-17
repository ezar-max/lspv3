<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AK01ReadyForSignature extends Notification
{
    use Queueable;

    protected $registrationId;
    protected $asesiName;

    public function __construct($registrationId, $asesiName)
    {
        $this->registrationId = $registrationId;
        $this->asesiName = $asesiName;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'FR.AK.01 Siap Diverifikasi',
            'message' => $this->asesiName . ' telah menyelesaikan dan menandatangani FR.AK.01.',
            'url' => route('asesor.ak01.detail', $this->registrationId),
            'type' => 'ak01_ready',
            'metadata' => [
                'registration_id' => $this->registrationId,
                'status' => 'disetujui_asesi',
            ],
        ];
    }
}
