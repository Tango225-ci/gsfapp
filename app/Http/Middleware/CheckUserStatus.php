<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->status !== 'actif') {
       //     Auth::logout();
       //     return redirect('/login')->with('error', 'Votre compte est inactif.');
        }

        return $next($request);
    }
}
