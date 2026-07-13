<?php

namespace App\Helpers;

use App\Models\Utilisateur;
use App\Notifications\AdminNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class NotifHelper
{
    public static function notifyAdmins(string $sujet, array $lignes): void
    {
        // Résoudre l'auteur maintenant (pendant la requête, avant defer)
        $auteur = auth('utilisateurs')->user()?->nom ?? 'Système';

        // defer() envoie la notification APRÈS que la réponse HTTP soit retournée
        // → l'utilisateur ne patiente plus le délai SMTP (timeout Mailtrap, etc.)
        defer(function () use ($sujet, $lignes, $auteur) {
            try {
                $admins = Utilisateur::where('role', 'ADMIN')
                    ->whereNotNull('email')
                    ->where('statut', true)
                    ->get();

                if ($admins->isEmpty()) {
                    return;
                }

                Notification::send($admins, new AdminNotification($sujet, $lignes, $auteur));
            } catch (\Exception $e) {
                Log::error('Erreur envoi notification admin: ' . $e->getMessage());
            }
        });
    }
}
