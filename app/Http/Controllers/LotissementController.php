<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\Lotissement;
use App\Models\Lot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class LotissementController extends Controller
{
    /**
     * Récupérer tous les lotissements (non supprimés)
     * pour le tableau récapitulatif
     */
    public function index()
    {
        $lotissements = Lotissement::withCount('lots')
                                   ->where(function($query) {
                                       $query->where('valeursupprimee', '!=', 1)
                                             ->orWhereNull('valeursupprimee');
                                   })
                                   ->orderBy('nom', 'asc')
                                   ->get();

        return response()->json($lotissements);
    }

    /**
     * Récupérer la liste des villes distinctes
     */
    public function getVilles()
    {
        $villes = Lotissement::where(function($query) {
                                 $query->where('valeursupprimee', '!=', 1)
                                       ->orWhereNull('valeursupprimee');
                             })
                             ->select('ville')
                             ->distinct()
                             ->whereNotNull('ville')
                             ->where('ville', '!=', '')
                             ->orderBy('ville', 'asc')
                             ->pluck('ville');

        return response()->json($villes);
    }

    /**
     * Récupérer un lotissement spécifique
     */
    public function show($id)
    {
        $lotissement = Lotissement::withCount('lots')
                                  ->where('id_lotissement', $id)
                                  ->where(function($query) {
                                      $query->where('valeursupprimee', '!=', 1)
                                            ->orWhereNull('valeursupprimee');
                                  })
                                  ->first();

        if (!$lotissement) {
            return response()->json([
                'success' => false,
                'message' => 'Lotissement introuvable'
            ], 404);
        }

        return response()->json($lotissement);
    }

    /**
     * Créer un nouveau lotissement
     */
    public function store(Request $request)
    {
        PermissionHelper::abort403('lotissements', 'create');

        try {
            $validated = $request->validate([
                'nom'                 => ['required', 'string', 'max:150', Rule::unique('lotissements', 'nom')->where(fn ($q) => $q->where('valeursupprimee', '!=', 1))],
                'localisation'        => 'nullable|string',
                'ville'               => 'required|string|max:255',
                'statut_lotissement'  => 'nullable|in:NON_APPROUVE,APPROUVE,APPROUVE_DIFFUSE,ACD_INDIVIDUEL,ACD_GLOBAL',
                'numero_approbation'  => 'nullable|string|max:20',
                'date_approbation'    => 'nullable|date'
            ]);

            $validated['valeursupprimee'] = 0;
            $validated['statut_lotissement'] = $validated['statut_lotissement'] ?? 'NON_APPROUVE';

            $lotissement = Lotissement::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Lotissement créé avec succès',
                'lotissement' => $lotissement
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur création lotissement: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Modifier un lotissement
     */
    public function update(Request $request, $id)
    {
        PermissionHelper::abort403('lotissements', 'update');

        try {
            $lotissement = Lotissement::where('id_lotissement', $id)
                                      ->where(function($query) {
                                          $query->where('valeursupprimee', '!=', 1)
                                                ->orWhereNull('valeursupprimee');
                                      })
                                      ->first();

            if (!$lotissement) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lotissement introuvable'
                ], 404);
            }

            $validated = $request->validate([
                'nom'                 => ['required', 'string', 'max:150', Rule::unique('lotissements', 'nom')->ignore($id, 'id_lotissement')->where(fn ($q) => $q->where('valeursupprimee', '!=', 1))],
                'localisation'        => 'nullable|string',
                'ville'               => 'required|string|max:255',
                'statut_lotissement'  => 'nullable|in:NON_APPROUVE,APPROUVE,APPROUVE_DIFFUSE,ACD_INDIVIDUEL,ACD_GLOBAL',
                'numero_approbation'  => 'nullable|string|max:20',
                'date_approbation'    => 'nullable|date'
            ]);

            $lotissement->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Lotissement modifié avec succès',
                'lotissement' => $lotissement
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur modification lotissement: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un lotissement (soft delete)
     */
    public function destroy($id)
    {
        PermissionHelper::abort403('lotissements', 'delete');

        try {
            $lotissement = Lotissement::where('id_lotissement', $id)->first();

            if (!$lotissement) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lotissement introuvable'
                ], 404);
            }

            // Vérifier s'il y a des lots associés
            if (class_exists(Lot::class)) {
                $lotsCount = Lot::where('id_lotissement', $id)
                                ->where(function($query) {
                                    $query->where('valeursupprimee', '!=', 1)
                                          ->orWhereNull('valeursupprimee');
                                })
                                ->count();

                if ($lotsCount > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ce lotissement contient des lots et ne peut pas être supprimé.'
                    ], 403);
                }
            }

            $lotissement->update(['valeursupprimee' => 1]);

            return response()->json([
                'success' => true,
                'message' => 'Lotissement supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur suppression lotissement: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les lotissements par ville
     */
    public function getByVille($ville)
    {
        $lotissements = Lotissement::withCount('lots')
                                   ->where('ville', 'LIKE', '%' . $ville . '%')
                                   ->where(function($query) {
                                       $query->where('valeursupprimee', '!=', 1)
                                             ->orWhereNull('valeursupprimee');
                                   })
                                   ->orderBy('nom', 'asc')
                                   ->get();

        return response()->json($lotissements);
    }

    /**
     * Récupérer les lotissements par statut
     */
    public function getByStatut($statut)
    {
        $validStatuts = ['NON_APPROUVE', 'APPROUVE', 'APPROUVE_DIFFUSE', 'ACD_INDIVIDUEL', 'ACD_GLOBAL'];

        if (!in_array($statut, $validStatuts)) {
            return response()->json([
                'success' => false,
                'message' => 'Statut invalide'
            ], 400);
        }

        $lotissements = Lotissement::withCount('lots')
                                   ->where('statut_lotissement', $statut)
                                   ->where(function($query) {
                                       $query->where('valeursupprimee', '!=', 1)
                                             ->orWhereNull('valeursupprimee');
                                   })
                                   ->orderBy('nom', 'asc')
                                   ->get();

        return response()->json($lotissements);
    }

    /**
     * Récupérer les statistiques des lotissements
     */
    public function getStatistics()
    {
        $baseQuery = Lotissement::where(function($query) {
            $query->where('valeursupprimee', '!=', 1)
                  ->orWhereNull('valeursupprimee');
        });

        $total = $baseQuery->count();
        $nonApprouve = (clone $baseQuery)->where('statut_lotissement', 'NON_APPROUVE')->count();
        $approuve = (clone $baseQuery)->where('statut_lotissement', 'APPROUVE')->count();
        $approuveDiffuse = (clone $baseQuery)->where('statut_lotissement', 'APPROUVE_DIFFUSE')->count();
        $acdIndividuel = (clone $baseQuery)->where('statut_lotissement', 'ACD_INDIVIDUEL')->count();
        $acdGlobal = (clone $baseQuery)->where('statut_lotissement', 'ACD_GLOBAL')->count();

        return response()->json([
            'success' => true,
            'total' => $total,
            'non_approuve' => $nonApprouve,
            'approuve' => $approuve,
            'approuve_diffuse' => $approuveDiffuse,
            'acd_individuel' => $acdIndividuel,
            'acd_global' => $acdGlobal
        ]);
    }

    /**
     * Recherche de lotissements
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');

        $lotissements = Lotissement::withCount('lots')
                                   ->where(function($q) use ($query) {
                                       $q->where('nom', 'LIKE', '%' . $query . '%')
                                         ->orWhere('ville', 'LIKE', '%' . $query . '%')
                                         ->orWhere('localisation', 'LIKE', '%' . $query . '%')
                                         ->orWhere('numero_approbation', 'LIKE', '%' . $query . '%');
                                   })
                                   ->where(function($q) {
                                       $q->where('valeursupprimee', '!=', 1)
                                         ->orWhereNull('valeursupprimee');
                                   })
                                   ->orderBy('nom', 'asc')
                                   ->get();

        return response()->json($lotissements);
    }

    /**
     * Mettre à jour le statut d'un lotissement
     */
    public function updateStatut(Request $request, $id)
    {
        PermissionHelper::abort403('lotissements', 'update');

        try {
            $lotissement = Lotissement::where('id_lotissement', $id)
                                      ->where(function($query) {
                                          $query->where('valeursupprimee', '!=', 1)
                                                ->orWhereNull('valeursupprimee');
                                      })
                                      ->first();

            if (!$lotissement) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lotissement introuvable'
                ], 404);
            }

            $validated = $request->validate([
                'statut_lotissement' => 'required|in:NON_APPROUVE,APPROUVE,APPROUVE_DIFFUSE,ACD_INDIVIDUEL,ACD_GLOBAL'
            ]);

            $lotissement->update(['statut_lotissement' => $validated['statut_lotissement']]);

            return response()->json([
                'success' => true,
                'message' => 'Statut mis à jour avec succès',
                'lotissement' => $lotissement
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur mise à jour statut: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les lots d'un lotissement
     */
    public function getLots($id)
    {
        if (!class_exists(Lot::class)) {
            return response()->json([]);
        }

        $lots = Lot::where('id_lotissement', $id)
                   ->where(function($query) {
                       $query->where('valeursupprimee', '!=', 1)
                             ->orWhereNull('valeursupprimee');
                   })
                   ->orderBy('numero_lot', 'asc')
                   ->get();

        return response()->json($lots);
    }
}
