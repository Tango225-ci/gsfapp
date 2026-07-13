<?php

namespace App\Services;

use App\Contracts\SmsGateway;
use App\Models\Client;
use App\Models\ClientOtpCode;
use Illuminate\Support\Facades\Hash;

class ClientOtpService
{
    public function __construct(private SmsGateway $smsGateway) {}

    /**
     * Génère un code à 6 chiffres, invalide les codes précédents non utilisés,
     * et l'envoie par SMS au numéro du client.
     */
    public function generateAndSend(Client $client): void
    {
        ClientOtpCode::where('id_client', $client->id)->whereNull('consumed_at')->delete();

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        ClientOtpCode::create([
            'id_client' => $client->id,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->smsGateway->send(
            $client->contact,
            "GSF : votre code de vérification est {$code}. Il expire dans 10 minutes. Ne le partagez avec personne."
        );
    }

    /**
     * Vérifie le code fourni pour ce client. Le consomme (usage unique) si valide.
     */
    public function verify(Client $client, string $code): bool
    {
        $otp = ClientOtpCode::where('id_client', $client->id)
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $otp || ! Hash::check($code, $otp->code_hash)) {
            return false;
        }

        $otp->update(['consumed_at' => now()]);

        return true;
    }
}
