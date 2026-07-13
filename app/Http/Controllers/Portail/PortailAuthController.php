<?php

namespace App\Http\Controllers\Portail;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PortailAuthController extends Controller
{
    public function showLogin()
    {
        if (auth('clients')->check()) {
            return redirect()->route('portail.dashboard');
        }

        return view('portail.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'identifiant' => 'required|string',
            'password'    => 'required|string',
        ], [
            'identifiant.required' => 'Le téléphone ou l\'email est obligatoire.',
            'password.required'    => 'Le mot de passe est obligatoire.',
        ]);

        $client = Client::where(function ($q) use ($request) {
                $q->where('contact', $request->identifiant)
                  ->orWhere('email', $request->identifiant);
            })
            ->where(function ($q) {
                $q->where('valeursupprimee', '!=', 1)->orWhereNull('valeursupprimee');
            })
            ->first();

        if (! $client || ! $client->password || ! Hash::check($request->password, $client->password)) {
            return back()->withErrors(['identifiant' => 'Identifiants incorrects.'])->withInput($request->only('identifiant'));
        }

        if ($client->statut === 'SUSPENDU') {
            return back()->withErrors(['identifiant' => 'Ce compte est suspendu. Contactez GSF.'])->withInput($request->only('identifiant'));
        }

        auth('clients')->login($client, $request->boolean('remember'));

        $request->session()->regenerate();

        if ($client->must_change_password) {
            return redirect()->route('portail.password.change');
        }

        return redirect()->intended(route('portail.dashboard'));
    }

    public function logout(Request $request)
    {
        auth('clients')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portail.login');
    }

    public function showChangePassword()
    {
        return view('portail.auth.change-password');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ], [
            'password.required'  => 'Le nouveau mot de passe est obligatoire.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'password.min'       => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        $client = auth('clients')->user();
        $client->password = Hash::make($request->password);
        $client->must_change_password = false;
        $client->save();

        return redirect()->route('portail.dashboard')->with('success', 'Mot de passe modifié avec succès !');
    }

    public function manifest(): JsonResponse
    {
        return response()->json([
            'name' => 'GSF - Espace Client',
            'short_name' => 'GSF Portail',
            'start_url' => '/portail',
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => '#b91c1c',
            'icons' => [
                [
                    'src' => asset('images/logo/gsf-logo.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                ],
            ],
        ], 200, ['Content-Type' => 'application/manifest+json']);
    }
}
