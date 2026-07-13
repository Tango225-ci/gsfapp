<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\ClientOtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;

class PasswordResetController extends Controller
{
    private function findClient(string $identifiant): ?Client
    {
        return Client::where(function ($q) use ($identifiant) {
                $q->where('contact', $identifiant)->orWhere('email', $identifiant);
            })
            ->where(function ($q) {
                $q->where('valeursupprimee', '!=', 1)->orWhereNull('valeursupprimee');
            })
            ->first();
    }

    public function sendCode(Request $request, ClientOtpService $otpService)
    {
        $request->validate([
            'identifiant' => 'required|string',
        ], [
            'identifiant.required' => 'Le téléphone ou l\'email est obligatoire.',
        ]);

        $client = $this->findClient($request->identifiant);

        // Message générique dans tous les cas : ne pas révéler si le compte existe.
        if (! $client) {
            return response()->json(['success' => true]);
        }

        $key = 'otp-send:' . $client->id;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'message' => "Trop de demandes. Réessayez dans " . ceil($seconds / 60) . " minute(s).",
            ], 429);
        }
        RateLimiter::hit($key, 900);

        $otpService->generateAndSend($client);

        return response()->json(['success' => true]);
    }

    public function verify(Request $request, ClientOtpService $otpService)
    {
        $request->validate([
            'identifiant' => 'required|string',
            'code' => 'required|string|size:6',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ], [
            'identifiant.required' => 'Le téléphone ou l\'email est obligatoire.',
            'code.required' => 'Le code est obligatoire.',
            'code.size' => 'Le code doit contenir 6 chiffres.',
            'password.required' => 'Le nouveau mot de passe est obligatoire.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
        ]);

        $client = $this->findClient($request->identifiant);

        $key = 'otp-verify:' . ($client->id ?? $request->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json(['message' => 'Trop de tentatives. Recommencez la procédure depuis le début.'], 429);
        }

        if (! $client || ! $otpService->verify($client, $request->code)) {
            RateLimiter::hit($key, 600);

            return response()->json(['message' => 'Code invalide ou expiré.'], 422);
        }

        RateLimiter::clear($key);

        $client->password = Hash::make($request->password);
        $client->must_change_password = false;
        $client->save();

        return response()->json(['success' => true]);
    }
}
