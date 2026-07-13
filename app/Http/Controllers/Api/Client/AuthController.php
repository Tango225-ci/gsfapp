<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'identifiant' => 'required|string',
            'password' => 'required|string',
        ], [
            'identifiant.required' => 'Le téléphone ou l\'email est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
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
            return response()->json(['message' => 'Identifiants incorrects.'], 422);
        }

        if ($client->statut === 'SUSPENDU') {
            return response()->json(['message' => 'Ce compte est suspendu. Contactez GSF.'], 403);
        }

        $token = $client->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'client' => new ClientResource($client),
            'must_change_password' => (bool) $client->must_change_password,
        ]);
    }

    public function me(Request $request)
    {
        return new ClientResource($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['success' => true]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ], [
            'password.required' => 'Le nouveau mot de passe est obligatoire.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        $client = $request->user();
        $client->password = Hash::make($request->password);
        $client->must_change_password = false;
        $client->save();

        return response()->json(['success' => true, 'client' => new ClientResource($client)]);
    }
}
