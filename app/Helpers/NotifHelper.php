<?php

namespace App\Helpers;

use App\Jobs\SendAdminNotificationJob;

class NotifHelper
{
    public static function notifyAdmins(string $sujet, array $lignes): void
    {
        // Résoudre l'auteur maintenant (pendant la requête, avant la mise en queue)
        $auteur = auth('utilisateurs')->user()?->nom ?? 'Système';

        // La queue envoie la notification dans un processus séparé (php artisan
        // queue:work), indépendant de la réponse HTTP. Contrairement à defer(),
        // ça reste non-bloquant même sous le serveur de développement (defer()
        // ne s'exécute vraiment après la réponse que derrière PHP-FPM).
        SendAdminNotificationJob::dispatch($sujet, $lignes, $auteur);
    }
}
