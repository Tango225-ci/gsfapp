<?php

namespace App\Services\Sms;

use App\Contracts\SmsGateway;
use App\Helpers\PhoneHelper;
use Illuminate\Support\Facades\Log;
use Twilio\Http\CurlClient;
use Twilio\Rest\Client;

class TwilioSmsGateway implements SmsGateway
{
    public function send(string $toPhoneNumber, string $message): bool
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');

        if (! $sid || ! $token || ! $from) {
            Log::error('TwilioSmsGateway: configuration incomplète (SID/token/numéro expéditeur manquant).');
            return false;
        }

        try {
            // Le SDK Twilio utilise par défaut un timeout HTTP de 60s (CurlClient::DEFAULT_TIMEOUT) :
            // en cas de lenteur réseau vers l'API Twilio, un envoi peut bloquer jusqu'à une minute.
            // On réduit ce délai à une valeur raisonnable pour un SMS.
            $client = new Client($sid, $token, null, null, new CurlClient([
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 5,
            ]));

            $client->messages->create(PhoneHelper::toE164Ci($toPhoneNumber), [
                'from' => $from,
                'body' => $message,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('TwilioSmsGateway: échec de l\'envoi du SMS.', [
                'to' => $toPhoneNumber,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
