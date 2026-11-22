<?php

namespace App\Http\Controllers\Portail;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\ClientOtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;

class PortailPasswordResetController extends Controller
{
    public function showRequest()
    {
        $client = auth('clients')->user();

        return view('portail.auth.otp-request', ['client' => $client]);
    }

    public function sendCode(Request $request, ClientOtpService $otpService)
    {
        $client = auth('clients')->user();

        if (! $client) {
            $request->validate([
                'identifiant' => 'required|string',
            ], [
                'identifiant.required' => 'Le numéro client ou l\'email est obligatoire.',
            ]);

            $client = Client::where(function ($q) use ($request) {
                    $q->where('reference_client', strtoupper(trim($request->identifiant)))->orWhere('email', $request->identifiant);
                })
                ->where(function ($q) {
                    $q->where('valeursupprimee', '!=', 1)->orWhereNull('valeursupprimee');
                })
                ->first();
        }

        // Message générique dans tous les cas : ne pas révéler si le compte existe.
        if (! $client) {
            session(['otp_sent_generic' => true]);

            return redirect()->route('portail.otp.verify.show');
        }

        $key = 'otp-send:' . $client->id;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);

            return back()->withErrors(['identifiant' => "Trop de demandes. Réessayez dans " . ceil($seconds / 60) . " minute(s)."]);
        }
        RateLimiter::hit($key, 900);

        $otpService->generateAndSend($client);

        session(['otp_client_id' => $client->id]);

        return redirect()->route('portail.otp.verify.show');
    }

    public function showVerify()
    {
        if (! session('otp_client_id') && ! session('otp_sent_generic')) {
            return redirect()->route('portail.otp.request');
        }

        return view('portail.auth.otp-verify');
    }

    public function confirmReset(Request $request, ClientOtpService $otpService)
    {
        $request->validate([
            'code' => 'required|string|size:6',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ], [
            'code.required' => 'Le code est obligatoire.',
            'code.size' => 'Le code doit contenir 6 chiffres.',
            'password.required' => 'Le nouveau mot de passe est obligatoire.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
        ]);

        $clientId = session('otp_client_id');
        $client = $clientId ? Client::find($clientId) : null;

        $key = 'otp-verify:' . ($clientId ?? $request->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['code' => 'Trop de tentatives. Recommencez la procédure depuis le début.']);
        }

        if (! $client || ! $otpService->verify($client, $request->code)) {
            RateLimiter::hit($key, 600);

            return back()->withErrors(['code' => 'Code invalide ou expiré.']);
        }

        RateLimiter::clear($key);

        $client->password = Hash::make($request->password);
        $client->must_change_password = false;
        $client->save();

        session()->forget(['otp_client_id', 'otp_sent_generic']);

        if (auth('clients')->check()) {
            return redirect()->route('portail.profile')->with('success', 'Mot de passe modifié avec succès.');
        }

        return redirect()->route('portail.login')->with('success', 'Mot de passe modifié avec succès. Vous pouvez vous connecter.');
    }
}
