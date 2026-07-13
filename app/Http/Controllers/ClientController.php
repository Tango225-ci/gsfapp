<?php

namespace App\Http\Controllers;

use App\Helpers\NotifHelper;
use App\Helpers\PermissionHelper;
use App\Models\Client;
use App\Services\ClientAccountService;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Récupérer tous les clients (non supprimés)
     */
    public function index()
    {
        // On récupère uniquement les clients qui n'ont pas la valeur 1 dans 'valeursupprimee'
        $clients = Client::where('valeursupprimee', '!=', 1)
                         ->orWhereNull('valeursupprimee')
                         ->get();

        return response()->json($clients);
    }

    /**
     * Créer un nouveau client
     */
    public function store(Request $request)
    {
        PermissionHelper::abort403('clients', 'create');

        $validated = $request->validate([
            'nom_prenoms'    => 'required|string|max:255',
            'contact'        => 'required|string|max:50|unique:clients,contact',
            'email'          => 'nullable|email|max:255|unique:clients,email',
            'adresse'        => 'nullable|string',
            'piece_identite' => 'nullable|string|max:100',
            'numero_piece'   => 'nullable|string|max:100',
            'date_validite'  => 'nullable|date',
            'statut'         => 'required|in:ACTIF,SUSPENDU,PROSPECT'
        ]);

        // Par défaut, on s'assure que valeursupprimee est à 0
        $validated['valeursupprimee'] = 0;

        $client = Client::create($validated);

        $plainPassword = app(ClientAccountService::class)->provision($client);

        NotifHelper::notifyAdmins('Nouveau client créé', [
            '**Nouveau client enregistré dans le système.**',
            'Nom : ' . $client->nom_prenoms,
            'Contact : ' . $client->contact,
            'Email : ' . ($client->email ?? '—'),
            'Statut : ' . $client->statut,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Client créé avec succès',
            'client'  => $client,
            'portail' => [
                'identifiant' => $client->contact,
                'password'    => $plainPassword,
                'login_url'   => url('/portail/connexion'),
            ],
        ], 201);
    }

    /**
     * Modifier un client
     */
    public function update(Request $request, $id)
    {
        PermissionHelper::abort403('clients', 'update');

        $client = Client::where('id', $id)
                        ->where('valeursupprimee', '!=', 1)
                        ->firstOrFail();

        $validated = $request->validate([
            'nom_prenoms'    => 'required|string|max:255',
            'contact'        => 'required|string|max:50|unique:clients,contact,' . $id,
            'email'          => 'nullable|email|max:255|unique:clients,email,' . $id,
            'adresse'        => 'nullable|string',
            'piece_identite' => 'nullable|string|max:100',
            'numero_piece'   => 'nullable|string|max:100',
            'date_validite'  => 'nullable|date',
            'statut'         => 'required|in:ACTIF,SUSPENDU,PROSPECT'
        ]);

        $client->update($validated);

        NotifHelper::notifyAdmins('Client modifié', [
            '**Un client a été modifié.**',
            'Nom : ' . $client->nom_prenoms,
            'Contact : ' . $client->contact,
            'Email : ' . ($client->email ?? '—'),
            'Statut : ' . $client->statut,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Client modifié avec succès',
            'client'  => $client
        ]);
    }

    /**
     * Suppression logique : modifie la colonne valeursupprimee au lieu de supprimer la ligne
     */
    public function destroy($id)
    {
        PermissionHelper::abort403('clients', 'delete');

        $client = Client::findOrFail($id);

        // On ne supprime pas, on met à jour le flag
        $client->update([
            'valeursupprimee' => 1
        ]);

        NotifHelper::notifyAdmins('Client supprimé', [
            '**Un client a été supprimé du système.**',
            'Nom : ' . $client->nom_prenoms,
            'Contact : ' . ($client->contact ?? '—'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Client supprimé (logiquement) avec succès'
        ]);
    }

    /**
     * Réinitialiser (ou activer pour la première fois) le compte portail d'un client :
     * génère un nouveau mot de passe temporaire, force son changement à la prochaine
     * connexion, et tente l'envoi par SMS. Retourne le mot de passe en clair pour que
     * l'agent puisse le communiquer manuellement tant qu'aucun fournisseur SMS réel
     * n'est branché.
     */
    public function resetPortailPassword($id)
    {
        PermissionHelper::abort403('clients', 'update');

        $client = Client::where('id', $id)
                        ->where(function ($query) {
                            $query->where('valeursupprimee', '!=', 1)
                                  ->orWhereNull('valeursupprimee');
                        })
                        ->firstOrFail();

        $plainPassword = app(ClientAccountService::class)->provision($client);

        NotifHelper::notifyAdmins('Mot de passe portail réinitialisé', [
            '**Le mot de passe de l\'espace client d\'un client a été réinitialisé.**',
            'Nom : ' . $client->nom_prenoms,
            'Contact : ' . $client->contact,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mot de passe portail réinitialisé avec succès',
            'password' => $plainPassword,
        ]);
    }

    /**
     * Récupérer un client spécifique (s'il n'est pas supprimé)
     */
    public function show($id)
    {
        $client = Client::where('id', $id)
                        ->where(function($query) {
                            $query->where('valeursupprimee', '!=', 1)
                                  ->orWhereNull('valeursupprimee');
                        })
                        ->firstOrFail();

        return response()->json($client);
    }
}
