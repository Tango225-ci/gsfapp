<?php

namespace App\Services\Sms;

use App\Contracts\SmsGateway;
use Illuminate\Support\Facades\Log;

/**
 * Driver par défaut en attendant le choix d'un vrai fournisseur SMS :
 * écrit le message dans les logs Laravel au lieu de l'envoyer réellement.
 */
class LogSmsGateway implements SmsGateway
{
    public function send(string $toPhoneNumber, string $message): bool
    {
        Log::channel(config('services.sms.log_channel', 'stack'))
            ->info('SMS (driver log — aucun fournisseur réel configuré)', [
                'to' => $toPhoneNumber,
                'message' => $message,
            ]);

        return true;
    }
}
