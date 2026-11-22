<?php

namespace App\Jobs;

use App\Contracts\SmsGateway;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Envoie un SMS en tâche de fond. Un envoi SMS (Twilio/Africa's Talking) est
 * un appel réseau qui peut prendre plusieurs secondes, voire échouer
 * lentement (timeout) : le faire passer par la queue évite de bloquer la
 * réponse HTTP de l'action qui a déclenché l'envoi (validation de paiement,
 * relance d'échéance, etc.).
 */
class SendClientSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 20;

    public function __construct(
        public string $toPhoneNumber,
        public string $message,
    ) {}

    public function handle(SmsGateway $smsGateway): void
    {
        $smsGateway->send($this->toPhoneNumber, $this->message);
    }
}
