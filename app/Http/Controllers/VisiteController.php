<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\Visite;
use Illuminate\Http\Request;

class VisiteController extends Controller
{
    /**
     * Récupérer toutes les visites (non supprimées)
     */
    public function index()
    {
        // On récupère uniquement les visites qui n'ont pas la valeur 1 dans 'valeursupprimee'
        $visites = Visite::where('valeursupprimee', '!=', 1)
                         ->orWhereNull('valeursupprimee')
                         ->orderBy('date_visite', 'desc')
                         ->orderBy('heure_arrivee', 'desc')
                         ->get();

        return response()->json($visites);
    }

    /**
     * Créer une nouvelle visite
     */
    public function store(Request $request)
    {
        PermissionHelper::abort403('visites', 'create');

        $validated = $request->validate([
            'nom_visiteur'      => 'required|string|max:255',
            'contact_visiteur'  => 'required|string|max:20',
            'adresse_visiteur'  => 'required|string|max:255',
            'date_visite'       => 'required|date',
            'heure_arrivee'     => 'nullable|date_format:H:i',
            'heure_depart'      => 'required|date_format:H:i',
            'motif'             => 'required|string|max:255'
        ]);

        // Par défaut, on s'assure que valeursupprimee est à 0
        $validated['valeursupprimee'] = 0;

        $visite = Visite::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Visite créée avec succès',
            'visite'  => $visite
        ], 201);
    }

    /**
     * Modifier une visite
     */
    public function update(Request $request, $id)
    {
        PermissionHelper::abort403('visites', 'update');

        $visite = Visite::where('id_visite', $id)
                        ->where('valeursupprimee', '!=', 1)
                        ->firstOrFail();

        $validated = $request->validate([
            'nom_visiteur'      => 'required|string|max:255',
            'contact_visiteur'  => 'required|string|max:20',
            'adresse_visiteur'  => 'required|string|max:255',
            'date_visite'       => 'required|date',
            'heure_arrivee'     => 'nullable|date_format:H:i',
            'heure_depart'      => 'required|date_format:H:i',
            'motif'             => 'required|string|max:255'
        ]);

        $visite->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Visite modifiée avec succès',
            'visite'  => $visite
        ]);
    }

    /**
     * Suppression logique : modifie la colonne valeursupprimee au lieu de supprimer la ligne
     */
    public function destroy($id)
    {
        PermissionHelper::abort403('visites', 'delete');

        $visite = Visite::findOrFail($id);

        // On ne supprime pas, on met à jour le flag
        $visite->update([
            'valeursupprimee' => 1
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Visite supprimée (logiquement) avec succès'
        ]);
    }

    /**
     * Récupérer une visite spécifique (si elle n'est pas supprimée)
     */
    public function show($id)
    {
        $visite = Visite::where('id_visite', $id)
                        ->where(function($query) {
                            $query->where('valeursupprimee', '!=', 1)
                                  ->orWhereNull('valeursupprimee');
                        })
                        ->firstOrFail();

        return response()->json($visite);
    }

    /**
     * Récupérer les visites par date
     */
    public function getByDate(Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        $visites = Visite::where('date_visite', $request->date)
                         ->where('valeursupprimee', '!=', 1)
                         ->orWhereNull('valeursupprimee')
                         ->orderBy('heure_arrivee', 'asc')
                         ->get();

        return response()->json($visites);
    }

    /**
     * Récupérer les visites entre deux dates
     */
    public function getByDateRange(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date'
        ]);

        $visites = Visite::whereBetween('date_visite', [$request->start_date, $request->end_date])
                         ->where('valeursupprimee', '!=', 1)
                         ->orWhereNull('valeursupprimee')
                         ->orderBy('date_visite', 'desc')
                         ->orderBy('heure_arrivee', 'asc')
                         ->get();

        return response()->json($visites);
    }

    /**
     * Récupérer les visites d'un visiteur spécifique
     */
    public function getByVisitor($nom_visiteur)
    {
        $visites = Visite::where('nom_visiteur', 'LIKE', "%{$nom_visiteur}%")
                         ->where('valeursupprimee', '!=', 1)
                         ->orWhereNull('valeursupprimee')
                         ->orderBy('date_visite', 'desc')
                         ->get();

        return response()->json($visites);
    }

    /**
     * Statistiques des visites
     */
    public function getStatistics()
    {
        $stats = [
            'total_visites' => Visite::where('valeursupprimee', '!=', 1)
                                     ->orWhereNull('valeursupprimee')
                                     ->count(),
            'visites_mois' => Visite::whereYear('date_visite', date('Y'))
                                    ->whereMonth('date_visite', date('m'))
                                    ->where('valeursupprimee', '!=', 1)
                                    ->orWhereNull('valeursupprimee')
                                    ->count(),
            'visites_semaine' => Visite::whereBetween('date_visite', [now()->startOfWeek(), now()->endOfWeek()])
                                        ->where('valeursupprimee', '!=', 1)
                                        ->orWhereNull('valeursupprimee')
                                        ->count(),
            'visites_aujourdhui' => Visite::whereDate('date_visite', now()->toDateString())
                                           ->where('valeursupprimee', '!=', 1)
                                           ->orWhereNull('valeursupprimee')
                                           ->count()
        ];

        return response()->json($stats);
    }
}
