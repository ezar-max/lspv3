<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class APL02NeedsRevision extends Notification
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
            'title' => 'FR.APL.02 Memerlukan Perbaikan',
            'message' => $this->customMessage ?: 'FR.APL.02 Anda memerlukan perbaikan berdasarkan hasil pemeriksaan Asesor.',
            'url' => route('asesi.tahapan', ['step' => 2, 'pendaftaran_id' => $this->registrationId]),
            'type' => 'revision',
            'metadata' => [
                'registration_id' => $this->registrationId,
                'status' => 'revision',
            ],
        ];
    }
}
