<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SystemAlert extends Notification
{
    use Queueable;

    protected $title;
    protected $message;
    protected $url;
    protected $type;
    protected $metadata;

    public function __construct($title, $message, $url = null, $type = 'info', array $metadata = [])
    {
        $this->title = $title;
        $this->message = $message;
        $this->url = $url;
        $this->type = $type;
        $this->metadata = $metadata;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url ?: url()->current(),
            'type' => $this->type,
            'metadata' => $this->metadata,
        ];
    }
}
