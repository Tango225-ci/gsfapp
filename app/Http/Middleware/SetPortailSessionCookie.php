<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Le portail client (/portail/*) et l'espace staff partagent le même
 * domaine. Sans séparation, les deux zones utiliseraient le même cookie
 * de session : une connexion (staff ou client) régénère la session pour
 * la sécurité, ce qui invaliderait silencieusement le jeton CSRF affiché
 * dans un autre onglet resté ouvert sur l'autre zone.
 *
 * On donne donc au portail un cookie de session dédié, complètement
 * isolé de celui du staff. Doit s'exécuter avant StartSession (prepend
 * sur le groupe "web" dans bootstrap/app.php).
 */
class SetPortailSessionCookie
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('portail*')) {
            config([
                'session.cookie' => 'gsf_portail_session',
                'session.path'   => '/portail',
            ]);
        }

        return $next($request);
    }
}
