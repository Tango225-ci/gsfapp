<?php

namespace App\Contracts;

interface SmsGateway
{
    /**
     * Envoie un SMS. Retourne true si l'envoi a été tenté avec succès.
     */
    public function send(string $toPhoneNumber, string $message): bool;
}
