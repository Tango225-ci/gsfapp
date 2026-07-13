<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceClientPasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        $client = auth('clients')->user();

        if ($client && $client->must_change_password) {
            if (! $request->routeIs('portail.password.change') && ! $request->routeIs('portail.password.change.update') && ! $request->routeIs('portail.logout')) {
                return redirect()->route('portail.password.change');
            }
        }

        return $next($request);
    }
}
