<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AdminNotification extends Notification
{
    public function __construct(
        private string $sujet,
        private array  $lignes,
        private string $auteur
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->sujet . ' — GSF')
            ->greeting('Bonjour ' . $notifiable->nom . ',');

        foreach ($this->lignes as $ligne) {
            $mail->line($ligne);
        }

        return $mail
            ->line(' ')
            ->line('*Action effectuée par : ' . $this->auteur . '*')
            ->salutation('L\'équipe GSF');
    }
}
