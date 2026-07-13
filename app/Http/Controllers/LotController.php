<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\Lot;
use App\Models\Lotissement;
use App\Models\Contrat;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class LotController extends Controller
{
    /**
     * Récupérer tous les lots (pour le tableau principal)
     */
    public function index()
    {
        try {
            $lots = Lot::with('lotissement')
                       ->where('valeursupprimee', '!=', 1)
                       ->orderBy('numero_lot', 'asc')
                       ->get();

            return response()->json($lots);
        } catch (\Exception $e) {
            Log::error('Erreur chargement lots: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des lots'
            ], 500);
        }
    }

    /**
     * Récupérer tous les lots (y compris réservés/vendus) - pour l'admin
     */
    public function indexAll()
    {
        try {
            $lots = Lot::with('lotissement')
                       ->where('valeursupprimee', '!=', 1)
                       ->orderBy('numero_lot', 'asc')
                       ->get();

            return response()->json($lots);
        } catch (\Exception $e) {
            Log::error('Erreur chargement tous les lots: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des lots'
            ], 500);
        }
    }

    /**
     * Récupérer les lots disponibles pour un select
     */
    public function getDisponibles()
    {
        try {
            $lots = Lot::with('lotissement')
                       ->where('statut', 'DISPONIBLE')
                       ->where('valeursupprimee', '!=', 1)
                       ->orderBy('numero_lot', 'asc')
                       ->get(['id_lot', 'numero_lot', 'numero_ilot', 'prix_comptant', 'prix_echelonne', 'id_lotissement', 'statut']);

            return response()->json($lots);
        } catch (\Exception $e) {
            Log::error('Erreur chargement lots disponibles: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des lots disponibles'
            ], 500);
        }
    }

    /**
     * Récupérer un lot spécifique avec son lotissement
     */
    public function show($id)
    {
        try {
            $lot = Lot::with('lotissement')
                      ->where('id_lot', $id)
                      ->where('valeursupprimee', '!=', 1)
                      ->firstOrFail();

            return response()->json($lot);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lot introuvable'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Erreur affichage lot: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du lot'
            ], 500);
        }
    }

    /**
     * Créer un nouveau lot
     */
    public function store(Request $request)
    {
        PermissionHelper::abort403('lots', 'create');

        try {
            $validated = $request->validate([
                'numero_lot'        => 'required|string|max:100|unique:lots,numero_lot',
                'numero_ilot'       => 'required|string|max:10',
                'type_lot'          => 'nullable|string|max:50|in:RESIDENTIEL,COMMERCIAL,INDUSTRIEL,MIXTE',
                'superficie'        => 'nullable|numeric|min:0',
                'prix_comptant'     => 'nullable|numeric|min:0',
                'prix_echelonne'    => 'nullable|numeric|min:0',
                'prix_achat'        => 'nullable|numeric|min:0',
                'statut'            => 'required|in:DISPONIBLE,RESERVE,VENDU',
                'id_lotissement'    => 'nullable|exists:lotissements,id_lotissement',
                'id_proprietaire'   => 'nullable|exists:proprietaires_terriens,id_proprietaire',
            ]);

            // Vérifier qu'au moins un prix est > 0
            $prixComptant = floatval($validated['prix_comptant'] ?? 0);
            $prixEchelonne = floatval($validated['prix_echelonne'] ?? 0);

            if ($prixComptant <= 0 && $prixEchelonne <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Au moins un prix (comptant ou échelonné) doit être supérieur à 0.'
                ], 422);
            }

            $validated['prix_comptant'] = $prixComptant;
            $validated['prix_echelonne'] = $prixEchelonne;
            $validated['valeursupprimee'] = 0;

            $lot = Lot::create($validated);
            $lot->load('lotissement');

            return response()->json([
                'success' => true,
                'message' => 'Lot créé avec succès',
                'lot' => $lot
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur creation lot: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Modifier un lot
     */
    public function update(Request $request, $id)
    {
        PermissionHelper::abort403('lots', 'update');

        try {
            $lot = Lot::where('id_lot', $id)
                      ->where('valeursupprimee', '!=', 1)
                      ->firstOrFail();

            // Vérifier si le lot est vendu
            if ($lot->statut === 'VENDU') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce lot est vendu et ne peut pas être modifié.'
                ], 403);
            }

            // Vérifier si le lot est réservé
            if ($lot->statut === 'RESERVE') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce lot est réservé et ne peut pas être modifié.'
                ], 403);
            }

            // Vérifier si le lot est sous contrat
            if (class_exists(Contrat::class)) {
                $sousContrat = Contrat::where('id_lot', $id)
                                      ->where('valeursupprimee', '!=', 1)
                                      ->exists();

                if ($sousContrat) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ce lot est sous contrat et ne peut pas être modifié.'
                    ], 403);
                }
            }

            $validated = $request->validate([
                'numero_lot'        => 'required|string|max:100|unique:lots,numero_lot,' . $id . ',id_lot',
                'numero_ilot'       => 'required|string|max:10',
                'type_lot'          => 'nullable|string|max:50|in:RESIDENTIEL,COMMERCIAL,INDUSTRIEL,MIXTE',
                'superficie'        => 'nullable|numeric|min:0',
                'prix_comptant'     => 'nullable|numeric|min:0',
                'prix_echelonne'    => 'nullable|numeric|min:0',
                'prix_achat'        => 'nullable|numeric|min:0',
                'statut'            => 'required|in:DISPONIBLE,RESERVE,VENDU',
                'id_lotissement'    => 'nullable|exists:lotissements,id_lotissement',
                'id_proprietaire'   => 'nullable|exists:proprietaires_terriens,id_proprietaire',
            ]);

            // Vérifier qu'au moins un prix est > 0
            $prixComptant = floatval($validated['prix_comptant'] ?? 0);
            $prixEchelonne = floatval($validated['prix_echelonne'] ?? 0);

            if ($prixComptant <= 0 && $prixEchelonne <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Au moins un prix (comptant ou échelonné) doit être supérieur à 0.'
                ], 422);
            }

            $validated['prix_comptant'] = $prixComptant;
            $validated['prix_echelonne'] = $prixEchelonne;

            $lot->update($validated);
            $lot->load('lotissement');

            return response()->json([
                'success' => true,
                'message' => 'Lot modifié avec succès',
                'lot' => $lot
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lot introuvable'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Erreur modification lot: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un lot (soft delete)
     */
    public function destroy($id)
    {
        PermissionHelper::abort403('lots', 'delete');

        try {
            $lot = Lot::findOrFail($id);

            // Vérifier si le lot est vendu
            if ($lot->statut === 'VENDU') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce lot est vendu et ne peut pas être supprimé.'
                ], 403);
            }

            // Vérifier si le lot est réservé
            if ($lot->statut === 'RESERVE') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce lot est réservé et ne peut pas être supprimé.'
                ], 403);
            }

            // Vérifier si le lot est sous contrat
            if (class_exists(Contrat::class)) {
                $sousContrat = Contrat::where('id_lot', $id)
                                      ->where('valeursupprimee', '!=', 1)
                                      ->exists();

                if ($sousContrat) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ce lot est sous contrat et ne peut pas être supprimé.'
                    ], 403);
                }
            }

            $lot->update(['valeursupprimee' => 1]);

            return response()->json([
                'success' => true,
                'message' => 'Lot supprimé avec succès'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lot introuvable'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Erreur suppression lot: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression'
            ], 500);
        }
    }

    /**
     * Récupérer les lots par statut
     */
    public function getByStatut($statut)
    {
        try {
            $validStatuts = ['DISPONIBLE', 'RESERVE', 'VENDU'];

            if (!in_array($statut, $validStatuts)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Statut invalide'
                ], 400);
            }

            $lots = Lot::with('lotissement')
                       ->where('statut', $statut)
                       ->where('valeursupprimee', '!=', 1)
                       ->orderBy('numero_lot', 'asc')
                       ->get();

            return response()->json($lots);
        } catch (\Exception $e) {
            Log::error('Erreur chargement lots par statut: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des lots'
            ], 500);
        }
    }

    /**
     * Récupérer les lots par lotissement
     */
    public function getByLotissement($idLotissement)
    {
        try {
            $lots = Lot::with('lotissement')
                       ->where('id_lotissement', $idLotissement)
                       ->where('valeursupprimee', '!=', 1)
                       ->orderBy('numero_lot', 'asc')
                       ->get();

            return response()->json($lots);
        } catch (\Exception $e) {
            Log::error('Erreur chargement lots par lotissement: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des lots'
            ], 500);
        }
    }

    /**
     * Récupérer tous les lotissements pour le select
     */
    public function getLotissements()
    {
        try {
            $lotissements = Lotissement::where('valeursupprimee', '!=', 1)
                                       ->orderBy('nom', 'asc')
                                       ->get(['id_lotissement', 'nom']);

            return response()->json($lotissements);
        } catch (\Exception $e) {
            Log::error('Erreur chargement lotissements: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des lotissements'
            ], 500);
        }
    }
}
