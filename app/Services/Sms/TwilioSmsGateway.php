<?php

namespace App\Services\Sms;

use App\Contracts\SmsGateway;
use App\Helpers\PhoneHelper;
use Illuminate\Support\Facades\Log;
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
            $client = new Client($sid, $token);

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
