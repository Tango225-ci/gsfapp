<?php

namespace App\Http\Controllers;

use App\Models\Client;
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
        $validated = $request->validate([
            'nom_prenoms'    => 'required|string|max:255',
            'contact'        => 'required|string|max:50',
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

        return response()->json([
            'success' => true,
            'message' => 'Client créé avec succès',
            'client'  => $client
        ], 201);
    }

    /**
     * Modifier un client
     */
    public function update(Request $request, $id)
    {
        $client = Client::where('id', $id)
                        ->where('valeursupprimee', '!=', 1)
                        ->firstOrFail();

        $validated = $request->validate([
            'nom_prenoms'    => 'required|string|max:255',
            'contact'        => 'required|string|max:50',
            'email'          => 'nullable|email|max:255|unique:clients,email,' . $id,
            'adresse'        => 'nullable|string',
            'piece_identite' => 'nullable|string|max:100',
            'numero_piece'   => 'nullable|string|max:100',
            'date_validite'  => 'nullable|date',
            'statut'         => 'required|in:ACTIF,SUSPENDU,PROSPECT'
        ]);

        $client->update($validated);

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
        $client = Client::findOrFail($id);

        // On ne supprime pas, on met à jour le flag
        $client->update([
            'valeursupprimee' => 1
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Client supprimé (logiquement) avec succès'
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
