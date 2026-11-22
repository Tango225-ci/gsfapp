<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Helpers\LoginThrottle;
use App\Models\Utilisateur;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (auth('utilisateurs')->check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ], [
            'login.required'    => 'Le nom d\'utilisateur est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);

        if (LoginThrottle::tooManyAttempts('staff', $request->login)) {
            return back()->withErrors(['login' => LoginThrottle::lockoutMessage('staff', $request->login)])->withInput($request->only('login'));
        }

        $utilisateur = Utilisateur::where('login', $request->login)->first();

        if (! $utilisateur || ! Hash::check($request->password, $utilisateur->password)) {
            LoginThrottle::hit('staff', $request->login);

            return back()->withErrors(['login' => 'Identifiants incorrects.'])->withInput($request->only('login'));
        }

        if (! $utilisateur->statut) {
            return back()->withErrors(['login' => 'Ce compte est désactivé. Contactez un administrateur.'])->withInput($request->only('login'));
        }

        LoginThrottle::clear('staff', $request->login);

        auth('utilisateurs')->login($utilisateur, $request->boolean('remember'));

        $request->session()->regenerate();

        if ($utilisateur->must_change_password) {
            return redirect()->route('password.change');
        }

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        auth('utilisateurs')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showChangePassword()
    {
        return view('auth.change-password');
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

        $utilisateur = auth('utilisateurs')->user();
        $utilisateur->password = Hash::make($request->password);
        $utilisateur->must_change_password = false;
        $utilisateur->save();

        return redirect()->route('dashboard')->with('success', 'Mot de passe modifié avec succès !');
    }

    public function showProfile()
    {
        return view('pages.profile', ['title' => 'Mon profil']);
    }

    public function updateProfile(Request $request)
    {
        $utilisateur = auth('utilisateurs')->user();

        $rules = [
            'nom'   => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:utilisateurs,email,' . $utilisateur->id_utilisateur . ',id_utilisateur',
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()];
        }

        $request->validate($rules, [
            'nom.required'       => 'Le nom est obligatoire.',
            'email.required'     => 'L\'email est obligatoire.',
            'email.unique'       => 'Cet email est déjà utilisé.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
        ]);

        $utilisateur->nom   = $request->nom;
        $utilisateur->email = $request->email;

        if ($request->filled('password')) {
            $utilisateur->password = Hash::make($request->password);
        }

        $utilisateur->save();

        return back()->with('success', 'Profil mis à jour avec succès.');
    }
}
