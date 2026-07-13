<?php

namespace App\Services\Sms;

use App\Contracts\SmsGateway;
use App\Helpers\PhoneHelper;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AfricasTalkingSmsGateway implements SmsGateway
{
    public function send(string $toPhoneNumber, string $message): bool
    {
        $username = config('services.africastalking.username');
        $apiKey = config('services.africastalking.api_key');
        $sandbox = config('services.africastalking.sandbox', false);

        if (! $username || ! $apiKey) {
            Log::error('AfricasTalkingSmsGateway: configuration incomplète (username/apiKey manquant).');
            return false;
        }

        $endpoint = $sandbox
            ? 'https://api.sandbox.africastalking.com/version1/messaging'
            : 'https://api.africastalking.com/version1/messaging';

        try {
            $response = Http::asForm()
                ->withHeaders([
                    'apiKey' => $apiKey,
                    'Accept' => 'application/json',
                ])
                ->post($endpoint, [
                    'username' => $username,
                    'to' => PhoneHelper::toE164Ci($toPhoneNumber),
                    'message' => $message,
                ]);

            if (! $response->successful()) {
                Log::error('AfricasTalkingSmsGateway: réponse HTTP en échec.', [
                    'to' => $toPhoneNumber,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }

            $recipients = $response->json('SMSMessageData.Recipients', []);
            $recipient = $recipients[0] ?? null;

            if (! $recipient || ! str_starts_with((string) ($recipient['status'] ?? ''), 'Success')) {
                Log::error('AfricasTalkingSmsGateway: échec de l\'envoi signalé par l\'API.', [
                    'to' => $toPhoneNumber,
                    'body' => $response->body(),
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('AfricasTalkingSmsGateway: exception lors de l\'envoi du SMS.', [
                'to' => $toPhoneNumber,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
