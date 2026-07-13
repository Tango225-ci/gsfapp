<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UtilisateurController extends Controller
{
    public function index()
    {
        $utilisateurs = Utilisateur::orderBy('nom')->get()->map(fn ($u) => [
            'id'             => $u->id,
            'nom'            => $u->nom,
            'login'          => $u->login,
            'email'          => $u->email,
            'role'           => $u->role,
            'role_label'     => $u->role_label,
            'statut'         => $u->statut,
            'statut_label'   => $u->statut_label,
            'date_creation'  => $u->date_creation,
            'must_change_password' => $u->must_change_password,
        ]);

        return response()->json(['success' => true, 'utilisateurs' => $utilisateurs]);
    }

    public function store(Request $request)
    {
        PermissionHelper::abort403('utilisateurs', 'create');

        $validated = $request->validate([
            'nom'      => 'required|string|max:255',
            'login'    => 'required|string|max:100|unique:utilisateurs,login',
            'email'    => 'required|email|max:255|unique:utilisateurs,email',
            'password' => ['required', 'confirmed', Password::min(6)],
            'role'     => 'required|in:ADMIN,ASSISTANT,DIRECTEUR',
            'statut'   => 'boolean',
        ]);

        $utilisateur = Utilisateur::create([
            'nom'                  => $validated['nom'],
            'login'                => $validated['login'],
            'email'                => $validated['email'],
            'password'             => Hash::make($validated['password']),
            'role'                 => $validated['role'],
            'statut'               => $validated['statut'] ?? true,
            'must_change_password' => true,
        ]);

        return response()->json([
            'success'     => true,
            'message'     => 'Utilisateur créé avec succès.',
            'utilisateur' => ['id' => $utilisateur->id, 'nom' => $utilisateur->nom],
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        PermissionHelper::abort403('utilisateurs', 'update');

        $utilisateur = Utilisateur::findOrFail($id);

        $rules = [
            'nom'    => 'required|string|max:255',
            'login'  => 'required|string|max:100|unique:utilisateurs,login,' . $id . ',id_utilisateur',
            'email'  => 'required|email|max:255|unique:utilisateurs,email,' . $id . ',id_utilisateur',
            'role'   => 'required|in:ADMIN,ASSISTANT,DIRECTEUR',
            'statut' => 'boolean',
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['confirmed', Password::min(6)];
        }

        $validated = $request->validate($rules);

        $utilisateur->nom    = $validated['nom'];
        $utilisateur->login  = $validated['login'];
        $utilisateur->email  = $validated['email'];
        $utilisateur->role   = $validated['role'];
        $utilisateur->statut = $validated['statut'] ?? $utilisateur->statut;

        if ($request->filled('password')) {
            $utilisateur->password = Hash::make($validated['password']);
            $utilisateur->must_change_password = true;
        }

        $utilisateur->save();

        return response()->json(['success' => true, 'message' => 'Utilisateur modifié avec succès.']);
    }

    public function destroy(int $id)
    {
        PermissionHelper::abort403('utilisateurs', 'delete');

        $utilisateur = Utilisateur::findOrFail($id);
        $utilisateur->delete();

        return response()->json(['success' => true, 'message' => 'Utilisateur supprimé.']);
    }

    public function toggleStatus(int $id)
    {
        PermissionHelper::abort403('utilisateurs', 'update');

        $utilisateur = Utilisateur::findOrFail($id);
        $utilisateur->statut = ! $utilisateur->statut;
        $utilisateur->save();

        return response()->json([
            'success' => true,
            'statut'  => $utilisateur->statut,
            'message' => $utilisateur->statut ? 'Utilisateur activé.' : 'Utilisateur désactivé.',
        ]);
    }
}
