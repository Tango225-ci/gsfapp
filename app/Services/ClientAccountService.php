<?php

namespace App\Services;

use App\Contracts\SmsGateway;
use App\Models\Client;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClientAccountService
{
    public function __construct(private SmsGateway $smsGateway) {}

    /**
     * Provisionne (ou re-provisionne) le compte portail d'un client :
     * génère un mot de passe temporaire, le hash, force le changement au
     * prochain login, puis transmet les identifiants par SMS.
     *
     * Retourne le mot de passe en clair (jamais persisté tel quel) pour
     * permettre son affichage/logging immédiat par l'appelant si besoin.
     */
    public function provision(Client $client): string
    {
        $plainPassword = Str::password(12);

        $client->password = Hash::make($plainPassword);
        $client->must_change_password = true;
        $client->portal_account_created_at = now();
        $client->save();

        $this->dispatchCredentials($client, $plainPassword);

        return $plainPassword;
    }

    public function hasAccount(Client $client): bool
    {
        return filled($client->password);
    }

    private function dispatchCredentials(Client $client, string $plainPassword): void
    {
        $message = "GSF : votre espace client est prêt. Identifiant : {$client->contact}. "
            . "Mot de passe temporaire : {$plainPassword}. Connectez-vous sur " . url('/portail/connexion');

        $this->smsGateway->send($client->contact, $message);
    }
}
