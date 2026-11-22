<?php

namespace App\Jobs;

use App\Models\Utilisateur;
use App\Notifications\AdminNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

/**
 * Envoie la notification email aux admins en tâche de fond. L'envoi d'email
 * (SMTP) est un appel réseau qui ne doit jamais bloquer la réponse HTTP de
 * l'action staff qui l'a déclenché (création/validation d'un paiement, etc.).
 */
class SendAdminNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 20;

    public function __construct(
        public string $sujet,
        public array $lignes,
        public string $auteur,
    ) {}

    public function handle(): void
    {
        $admins = Utilisateur::where('role', 'ADMIN')
            ->whereNotNull('email')
            ->where('statut', true)
            ->get();

        if ($admins->isEmpty()) {
            return;
        }

        Notification::send($admins, new AdminNotification($this->sujet, $this->lignes, $this->auteur));
    }
}
