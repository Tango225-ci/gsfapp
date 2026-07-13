<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Équivalent API de ForceClientPasswordChange (portail web) : le web
 * redirige vers l'écran de changement de mot de passe ; une API JSON n'a
 * pas de notion de redirection, donc on renvoie un 423 explicite que l'app
 * mobile interprète pour naviguer elle-même vers son propre écran.
 */
class EnsureClientPasswordNotForced
{
    public function handle(Request $request, Closure $next): Response
    {
        $client = $request->user();

        if ($client && $client->must_change_password) {
            return response()->json([
                'message' => 'Changement de mot de passe obligatoire.',
                'error' => 'password_change_required',
            ], 423);
        }

        return $next($request);
    }
}
