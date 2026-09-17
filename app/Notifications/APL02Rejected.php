<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class APL02Rejected extends Notification
{
    use Queueable;

    protected $registrationId;
    protected $customMessage;

    public function __construct($registrationId, $customMessage = null)
    {
        $this->registrationId = $registrationId;
        $this->customMessage = $customMessage;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Permohonan FR.APL.02 Ditolak',
            'message' => $this->customMessage ?: 'Permohonan FR.APL.02 Anda tidak dapat diterima berdasarkan peninjauan Asesor.',
            'url' => route('asesi.tahapan', ['step' => 2, 'pendaftaran_id' => $this->registrationId]),
            'type' => 'rejection',
            'metadata' => [
                'registration_id' => $this->registrationId,
                'status' => 'rejected',
            ],
        ];
    }
}
