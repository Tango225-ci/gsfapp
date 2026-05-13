<?php

namespace App\Http\Controllers;

use App\Models\Contrat;
use App\Models\Client;
use App\Models\Lot;
use App\Models\Paiement;
use App\Models\Echeance;
use App\Models\VilleDocumentFrais;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class ContratController extends Controller
{
    /**
     * Récupérer tous les contrats (non supprimés)
     */
    public function index()
    {
        $contrats = Contrat::with(['client', 'lot', 'lot.lotissement'])
                           ->withCount(['paiements as nombre_paiements' => function($query) {
                               $query->where('statut', 'VALIDE')
                                     ->where(function($q) {
                                         $q->where('valeursupprimee', '!=', 1)
                                           ->orWhereNull('valeursupprimee');
                                     });
                           }])
                           ->where('valeursupprimee', '!=', 1)
                           ->orWhereNull('valeursupprimee')
                           ->orderBy('date_contrat', 'desc')
                           ->get();

        $contrats->load(['paiements' => function($query) {
            $query->where('statut', 'VALIDE')
                  ->where(function($q) {
                      $q->where('valeursupprimee', '!=', 1)
                        ->orWhereNull('valeursupprimee');
                  });
        }]);

        $contrats->load(['echeances' => function($query) {
            $query->where('valeursupprimee', 0)
                  ->orderBy('date_echeance', 'asc');
        }]);

        $contrats->each(function($contrat) {
            $contrat->append([
                'montant_paye',
                'montant_paye_formatted',
                'reste_a_payer_calcule',
                'reste_a_payer_formatted',
                'progression_paiement',
                'est_solde',
                'est_totalement_solde', // NOUVEAU
                'peut_etre_modifie',
                'statut_label',
                'type_paiement_label',
                'date_contrat_formatted',
                'mode_paiement_label',
                'banque_paiement_label'
            ]);

            $contrat->nombre_paiements = (int)($contrat->nombre_paiements ?? 0);
        });

        return response()->json($contrats);
    }

    /**
     * Récupérer un contrat spécifique avec ses échéances
     */
    public function show($id)
    {
        $contrat = Contrat::with([
                                'client',
                                'lot',
                                'lot.lotissement',
                                'paiements' => function($query) {
                                    $query->where('valeursupprimee', '!=', 1)
                                          ->orWhereNull('valeursupprimee')
                                          ->orderBy('date_paiement', 'desc');
                                },
                                'echeances' => function($query) {
                                    $query->where('valeursupprimee', 0)
                                          ->orderBy('date_echeance', 'asc');
                                }
                            ])
                          ->where('id_contrat', $id)
                          ->where(function($query) {
                              $query->where('valeursupprimee', '!=', 1)
                                    ->orWhereNull('valeursupprimee');
                          })
                          ->firstOrFail();

        $contrat->verifierEtMettreAJourStatut();
        $contrat->refresh();
        $contrat->load('echeances');
        
        $contrat->append('est_totalement_solde'); // NOUVEAU

        return response()->json($contrat);
    }

    /**
     * Récupérer un contrat spécifique avec ses échéances et documents ACD
     */
    public function showWithDocuments($id)
    {
        try {
            $contrat = Contrat::with([
                'client',
                'lot',
                'lot.lotissement',
                'paiements' => function($query) {
                    $query->where('valeursupprimee', '!=', 1)
                          ->orWhereNull('valeursupprimee')
                          ->orderBy('date_paiement', 'desc');
                },
                'echeances' => function($query) {
                    $query->where('valeursupprimee', 0)
                          ->orderBy('date_echeance', 'asc');
                },
                'contratDocuments' => function($query) {
                    $query->with(['documentType', 'frais'])
                          ->where('valeursupprimee', 0)
                          ->orderBy('id_document_type', 'asc');
                }
            ])
            ->where('id_contrat', $id)
            ->where(function($query) {
                $query->where('valeursupprimee', '!=', 1)
                      ->orWhereNull('valeursupprimee');
            })
            ->firstOrFail();

            $contrat->verifierEtMettreAJourStatut();
            $contrat->refresh();
            $contrat->load('echeances');

            $contrat->append('tous_documents_acceptes', 'est_totalement_solde'); // MODIFIÉ

            return response()->json($contrat);

        } catch (\Exception $e) {
            Log::error('Erreur récupération contrat avec documents: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les frais ACD pour une ville donnée
     */
    public function getFraisAcdByVille($ville)
    {
        try {
            $frais = VilleDocumentFrais::with('documentType')
                ->where('ville', $ville)
                ->where('valeursupprimee', 0)
                ->get();

            $total = $frais->sum('montant');

            return response()->json([
                'success' => true,
                'ville' => $ville,
                'frais' => $frais->map(function($item) {
                    return [
                        'id_frais' => $item->id_frais,
                        'id_document_type' => $item->id_document_type,
                        'type_document' => $item->documentType ? $item->documentType->nom : 'Inconnu',
                        'code' => $item->documentType ? $item->documentType->code : '',
                        'montant' => $item->montant,
                        'montant_formatted' => number_format($item->montant, 0, ',', ' ') . ' FCFA',
                        'est_obligatoire' => $item->documentType ? $item->documentType->est_obligatoire : false
                    ];
                }),
                'total' => $total,
                'total_formatted' => number_format($total, 0, ',', ' ') . ' FCFA'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur récupération frais ACD par ville: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les frais ACD pour un lot spécifique (via son lotissement)
     */
    public function getFraisAcdByLot($idLot)
    {
        try {
            $lot = Lot::with('lotissement')->find($idLot);
            
            if (!$lot || !$lot->lotissement) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lot ou lotissement introuvable'
                ], 404);
            }

            $ville = $lot->lotissement->ville;
            
            if (!$ville) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune ville associée à ce lotissement'
                ], 404);
            }

            return $this->getFraisAcdByVille($ville);

        } catch (\Exception $e) {
            Log::error('Erreur récupération frais ACD par lot: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer la situation détaillée des paiements d'un contrat avec les échéances
     */
    public function getSituationPaiement($id)
    {
        try {
            Log::info('Chargement situation paiement pour contrat ID: ' . $id);

            $contrat = Contrat::with(['client', 'lot', 'lot.lotissement', 'echeances'])
                              ->where('id_contrat', $id)
                              ->where(function($query) {
                                  $query->where('valeursupprimee', '!=', 1)
                                        ->orWhereNull('valeursupprimee');
                              })
                              ->first();

            if (!$contrat) {
                Log::warning('Contrat introuvable: ' . $id);
                return response()->json([
                    'success' => false,
                    'message' => 'Contrat introuvable'
                ], 404);
            }

            $contrat->verifierEtMettreAJourStatut();
            $contrat->refresh();
            $contrat->load('echeances');

            $paiements = Paiement::where('id_contrat', $id)
                                 ->where('statut', 'VALIDE')
                                 ->where(function($query) {
                                     $query->where('valeursupprimee', '!=', 1)
                                           ->orWhereNull('valeursupprimee');
                                 })
                                 ->orderBy('date_paiement', 'desc')
                                 ->get();

            Log::info('Nombre de paiements trouvés: ' . $paiements->count());

            $montantTotal = floatval($contrat->montant_total ?? 0);
            $montantPaye = $paiements->sum('montant');

            $estSolde = $contrat->statut === 'SOLDE';
            $resteAPayer = $estSolde ? 0 : max(0, $montantTotal - $montantPaye);
            $progression = $estSolde ? 100 : ($montantTotal > 0 ? min(100, round(($montantPaye / $montantTotal) * 100)) : 0);

            if ($estSolde && $montantPaye == 0 && $contrat->type_paiement === 'COMPTANT') {
                $paiementAuto = Paiement::where('id_contrat', $id)
                                        ->where('statut', 'VALIDE')
                                        ->first();
                if ($paiementAuto) {
                    $montantPaye = floatval($paiementAuto->montant);
                    $resteAPayer = 0;
                    $progression = 100;
                    $paiements = collect([$paiementAuto]);
                    Log::info('Paiement automatique trouvé pour contrat COMPTANT', [
                        'montant' => $montantPaye,
                        'paiement_id' => $paiementAuto->id_paiement
                    ]);
                }
            }

            // Vérifier si des frais ACD restent à payer
            $fraisNonPayes = $contrat->contratDocuments()
                                     ->where('statut_paiement', '!=', 'paye')
                                     ->where('montant', '>', 0)
                                     ->count();
            
            $estTotalementSolde = $estSolde && $fraisNonPayes === 0;
            $peutRecevoirPaiement = $contrat->peutRecevoirPaiement() && !$estTotalementSolde;

            $prochaineEcheance = $contrat->prochaine_echeance;

            $echeances = $contrat->echeances->map(function($echeance) {
                return [
                    'id' => $echeance->id,
                    'numero' => $echeance->numero_echeance,
                    'date_echeance' => $echeance->date_echeance_formatted,
                    'date_echeance_raw' => $echeance->date_echeance,
                    'montant_prevu' => floatval($echeance->montant_prevu),
                    'montant_prevu_formatted' => $echeance->montant_prevu_formatted,
                    'montant_paye' => floatval($echeance->montant_paye),
                    'montant_paye_formatted' => $echeance->montant_paye_formatted,
                    'reste_a_payer' => $echeance->reste_a_payer,
                    'reste_a_payer_formatted' => $echeance->reste_a_payer_formatted,
                    'statut' => $echeance->statut,
                    'statut_label' => $echeance->statut_label,
                    'statut_class' => $echeance->statut_class,
                    'est_en_retard' => $echeance->est_en_retard,
                    'est_paye' => $echeance->est_paye,
                    'progression' => $echeance->progression_paiement
                ];
            });

            // Ajouter les frais ACD à la réponse
            $fraisAcd = [];
            $totalFraisAcd = 0;
            $ville = $contrat->lot && $contrat->lot->lotissement ? $contrat->lot->lotissement->ville : null;
            
            if ($ville) {
                $fraisAcdData = VilleDocumentFrais::with('documentType')
                    ->where('ville', $ville)
                    ->where('valeursupprimee', 0)
                    ->get();
                
                $fraisAcd = $fraisAcdData->map(function($item) {
                    return [
                        'id_frais' => $item->id_frais,
                        'id_document_type' => $item->id_document_type,
                        'type_document' => $item->documentType ? $item->documentType->nom : 'Inconnu',
                        'code' => $item->documentType ? $item->documentType->code : '',
                        'montant' => $item->montant,
                        'montant_formatted' => number_format($item->montant, 0, ',', ' ') . ' FCFA',
                        'est_obligatoire' => $item->documentType ? $item->documentType->est_obligatoire : false
                    ];
                });
                $totalFraisAcd = $fraisAcdData->sum('montant');
            }

            return response()->json([
                'success' => true,
                'contrat' => [
                    'id_contrat' => $contrat->id_contrat,
                    'reference_contrat' => $contrat->reference_contrat,
                    'client_nom' => $contrat->client->nom_prenoms ?? '-',
                    'lot_numero' => $contrat->lot->numero_lot ?? '-',
                    'date_contrat' => $contrat->date_contrat_formatted,
                    'date_contrat_raw' => $contrat->date_contrat,
                    'montant_total' => $montantTotal,
                    'montant_total_formatted' => number_format($montantTotal, 0, ',', ' ') . ' FCFA',
                    'type_paiement' => $contrat->type_paiement,
                    'type_paiement_label' => $contrat->type_paiement_label,
                    'duree_mois' => $contrat->duree_mois,
                    'statut' => $contrat->statut,
                    'statut_label' => $contrat->statut_label,
                    'mode_paiement' => $contrat->mode_paiement,
                    'mode_paiement_label' => $contrat->mode_paiement_label,
                    'banque_paiement' => $contrat->banque_paiement,
                    'banque_paiement_label' => $contrat->banque_paiement_label,
                    'est_solde' => $estSolde,
                    'est_totalement_solde' => $estTotalementSolde, // NOUVEAU
                    'peut_etre_modifie' => $contrat->peut_etre_modifie,
                    'nombre_echeances_total' => $contrat->nombre_echeances_total,
                    'nombre_echeances_payees' => $contrat->nombre_echeances_payees,
                    'nombre_echeances_en_retard' => $contrat->nombre_echeances_en_retard,
                    'ville' => $ville,
                    'frais_acd' => $fraisAcd,
                    'total_frais_acd' => $totalFraisAcd,
                    'total_frais_acd_formatted' => number_format($totalFraisAcd, 0, ',', ' ') . ' FCFA'
                ],
                'situation' => [
                    'montant_total' => number_format($montantTotal, 0, ',', ' ') . ' FCFA',
                    'montant_total_raw' => $montantTotal,
                    'montant_paye' => number_format($montantPaye, 0, ',', ' ') . ' FCFA',
                    'montant_paye_raw' => $montantPaye,
                    'reste_a_payer' => number_format($resteAPayer, 0, ',', ' ') . ' FCFA',
                    'reste_a_payer_raw' => $resteAPayer,
                    'progression' => $progression,
                    'peut_recevoir_paiement' => $peutRecevoirPaiement,
                    'est_completement_paye' => $resteAPayer <= 0
                ],
                'echeances' => $echeances,
                'prochaine_echeance' => $prochaineEcheance,
                'paiements' => $paiements->map(function($paiement) {
                    return [
                        'id_paiement' => $paiement->id_paiement,
                        'date_paiement' => $paiement->date_paiement_formatted,
                        'date_paiement_raw' => $paiement->date_paiement,
                        'reference' => $paiement->reference,
                        'montant' => floatval($paiement->montant),
                        'montant_formatted' => $paiement->montant_formatted,
                        'mode_paiement' => $paiement->mode_paiement,
                        'mode_paiement_label' => $paiement->mode_paiement_label,
                        'statut' => $paiement->statut,
                        'statut_label' => $paiement->statut_label,
                        'banque_paiement' => $paiement->banque_paiement,
                        'banque_label' => $paiement->banque_label
                    ];
                })->values()->toArray()
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur situation paiement: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les échéances d'un contrat
     */
    public function getEcheances($id)
    {
        try {
            $contrat = Contrat::where('id_contrat', $id)
                              ->where('valeursupprimee', '!=', 1)
                              ->first();

            if (!$contrat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contrat introuvable'
                ], 404);
            }

            $contrat->verifierEtMettreAJourStatut();

            $echeances = Echeance::where('id_contrat', $id)
                                 ->where('valeursupprimee', 0)
                                 ->orderBy('date_echeance', 'asc')
                                 ->get();

            return response()->json([
                'success' => true,
                'echeances' => $echeances->map(function($echeance) {
                    return [
                        'id' => $echeance->id,
                        'numero' => $echeance->numero_echeance,
                        'date_echeance' => $echeance->date_echeance_formatted,
                        'date_echeance_raw' => $echeance->date_echeance,
                        'montant_prevu' => floatval($echeance->montant_prevu),
                        'montant_prevu_formatted' => $echeance->montant_prevu_formatted,
                        'montant_paye' => floatval($echeance->montant_paye),
                        'montant_paye_formatted' => $echeance->montant_paye_formatted,
                        'reste_a_payer' => $echeance->reste_a_payer,
                        'reste_a_payer_formatted' => $echeance->reste_a_payer_formatted,
                        'statut' => $echeance->statut,
                        'statut_label' => $echeance->statut_label,
                        'statut_class' => $echeance->statut_class,
                        'est_en_retard' => $echeance->est_en_retard,
                        'est_paye' => $echeance->est_paye,
                        'progression' => $echeance->progression_paiement
                    ];
                }),
                'resume' => [
                    'total_echeances' => $contrat->nombre_echeances_total,
                    'echeances_payees' => $contrat->nombre_echeances_payees,
                    'echeances_en_retard' => $contrat->nombre_echeances_en_retard,
                    'prochaine_echeance' => $contrat->prochaine_echeance
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur récupération échéances: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Régénérer les échéances d'un contrat
     */
    public function regenererEcheances($id)
    {
        try {
            $contrat = Contrat::where('id_contrat', $id)
                              ->where('valeursupprimee', '!=', 1)
                              ->first();

            if (!$contrat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contrat introuvable'
                ], 404);
            }

            if ($contrat->type_paiement !== 'ECHELONNE') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce contrat n\'est pas un contrat échelonné'
                ], 422);
            }

            $totalPaye = $contrat->montant_paye;
            if ($totalPaye > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de régénérer les échéances car des paiements ont déjà été effectués'
                ], 422);
            }

            $contrat->genererEcheances();

            return response()->json([
                'success' => true,
                'message' => 'Échéances régénérées avec succès',
                'nombre_echeances' => $contrat->duree_mois
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur régénération échéances: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer un nouveau contrat
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_client'         => 'required|exists:clients,id',
                'id_lot'            => 'required|exists:lots,id_lot',
                'date_contrat'      => 'required|date',
                'date_debut'        => 'nullable|date',
                'montant_total'     => 'required|numeric|min:0',
                'type_paiement'     => 'required|in:COMPTANT,ECHELONNE',
                'duree_mois'        => 'nullable|integer|min:1|max:360',
                'frequence_paiement'=> 'nullable|in:MENSUEL,TRIMESTRIEL,SEMESTRIEL,ANNUEL',
                'statut'            => 'required|in:EN_COURS,SOLDE,RESILIE,EN_RETARD',
                'mode_paiement'     => 'nullable|in:ESPECES,VIREMENT,CHEQUE,MOBILE_MONEY',
                'banque_paiement'   => 'nullable|string|max:100'
            ]);

            $lot = Lot::find($validated['id_lot']);
            if (!$lot || $lot->statut !== 'DISPONIBLE') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce lot n\'est pas disponible.'
                ], 422);
            }

            $existingContrat = Contrat::where('id_lot', $validated['id_lot'])
                                      ->where(function($query) {
                                          $query->where('valeursupprimee', '!=', 1)
                                                ->orWhereNull('valeursupprimee');
                                      })
                                      ->first();

            if ($existingContrat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce lot est déjà sous contrat.'
                ], 422);
            }

            if ($validated['type_paiement'] === 'ECHELONNE') {
                if (empty($validated['duree_mois']) || $validated['duree_mois'] < 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'La durée en mois est requise pour un contrat échelonné.'
                    ], 422);
                }
                $validated['mode_paiement'] = null;
                $validated['banque_paiement'] = null;
            }

            if ($validated['type_paiement'] === 'COMPTANT') {
                if (empty($validated['mode_paiement'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Le mode de paiement est requis.'
                    ], 422);
                }
                if (in_array($validated['mode_paiement'], ['VIREMENT', 'CHEQUE']) && empty($validated['banque_paiement'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'La banque est requise.'
                    ], 422);
                }
                $validated['duree_mois'] = 1;
                $validated['statut'] = 'SOLDE';
            }

            if (empty($validated['date_debut'])) {
                $validated['date_debut'] = $validated['date_contrat'];
            }

            $validated['valeursupprimee'] = 0;
            $validated['montant_verse'] = 0;
            $validated['reste_a_payer'] = $validated['montant_total'];

            DB::beginTransaction();

            $contrat = Contrat::create($validated);

            if ($validated['type_paiement'] === 'COMPTANT') {
                $paiement = Paiement::create([
                    'id_contrat' => $contrat->id_contrat,
                    'date_paiement' => $validated['date_contrat'],
                    'montant' => $validated['montant_total'],
                    'mode_paiement' => $validated['mode_paiement'],
                    'banque_paiement' => $validated['banque_paiement'] ?? null,
                    'statut' => 'VALIDE',
                    'reference' => $this->genererReferencePaiement(),
                    'valeursupprimee' => 0,
                    'notes' => 'Paiement automatique - contrat comptant'
                ]);

                $contrat->montant_verse = $validated['montant_total'];
                $contrat->reste_a_payer = 0;
                $contrat->save();

                Log::info('Paiement automatique créé pour contrat COMPTANT', [
                    'contrat_id' => $contrat->id_contrat,
                    'reference' => $contrat->reference_contrat,
                    'montant' => $validated['montant_total']
                ]);
            }

            DB::commit();

            $contrat = Contrat::with(['client', 'lot', 'paiements', 'echeances'])
                              ->find($contrat->id_contrat);

            return response()->json([
                'success' => true,
                'message' => 'Contrat créé avec succès',
                'contrat' => $contrat
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création contrat: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Générer une référence de paiement
     */
    private function genererReferencePaiement()
    {
        $yearMonth = date('Ym');

        $lastPaiement = Paiement::where('reference', 'LIKE', 'PAI-' . $yearMonth . '-%')
                                ->orderBy('id_paiement', 'desc')
                                ->first();

        if ($lastPaiement && $lastPaiement->reference) {
            $parts = explode('-', $lastPaiement->reference);
            $lastNumber = intval(end($parts));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return 'PAI-' . $yearMonth . '-' . $newNumber;
    }

    /**
     * Modifier un contrat
     */
    public function update(Request $request, $id)
    {
        try {
            $contrat = Contrat::where('id_contrat', $id)
                              ->where('valeursupprimee', '!=', 1)
                              ->firstOrFail();

            if ($contrat->est_solde) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce contrat est soldé et ne peut plus être modifié.'
                ], 403);
            }

            $validated = $request->validate([
                'id_client'         => 'required|exists:clients,id',
                'id_lot'            => 'required|exists:lots,id_lot',
                'date_contrat'      => 'required|date',
                'date_debut'        => 'nullable|date',
                'montant_total'     => 'required|numeric|min:0',
                'type_paiement'     => 'required|in:COMPTANT,ECHELONNE',
                'duree_mois'        => 'nullable|integer|min:1|max:360',
                'frequence_paiement'=> 'nullable|in:MENSUEL,TRIMESTRIEL,SEMESTRIEL,ANNUEL',
                'statut'            => 'required|in:EN_COURS,SOLDE,RESILIE,EN_RETARD',
                'mode_paiement'     => 'nullable|in:ESPECES,VIREMENT,CHEQUE,MOBILE_MONEY',
                'banque_paiement'   => 'nullable|string|max:100'
            ]);

            $changementLot = ($contrat->id_lot != $validated['id_lot']);

            if ($changementLot) {
                $lot = Lot::find($validated['id_lot']);
                if (!$lot || $lot->statut !== 'DISPONIBLE') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Le lot sélectionné n\'est pas disponible.'
                    ], 422);
                }

                $existingContrat = Contrat::where('id_lot', $validated['id_lot'])
                                          ->where('id_contrat', '!=', $id)
                                          ->where(function($query) {
                                              $query->where('valeursupprimee', '!=', 1)
                                                    ->orWhereNull('valeursupprimee');
                                          })
                                          ->first();

                if ($existingContrat) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ce lot est déjà attribué à un autre contrat.'
                    ], 422);
                }
            }

            if ($validated['type_paiement'] === 'ECHELONNE') {
                if (empty($validated['duree_mois']) || $validated['duree_mois'] < 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'La durée en mois est requise pour un contrat échelonné.'
                    ], 422);
                }
                $validated['mode_paiement'] = null;
                $validated['banque_paiement'] = null;
            }

            if ($validated['type_paiement'] === 'COMPTANT') {
                if (empty($validated['mode_paiement'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Le mode de paiement est requis.'
                    ], 422);
                }
                if (in_array($validated['mode_paiement'], ['VIREMENT', 'CHEQUE']) && empty($validated['banque_paiement'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'La banque est requise.'
                    ], 422);
                }
                $validated['duree_mois'] = 1;
            }

            DB::beginTransaction();

            $ancienIdLot = $contrat->id_lot;
            $ancienTypePaiement = $contrat->type_paiement;
            $ancienneDuree = $contrat->duree_mois;
            $ancienMontant = $contrat->montant_total;

            $contrat->update($validated);

            // Mettre à jour les montants des documents ACD si le lot a changé
            if ($changementLot) {
                $nouveauLot = Lot::find($validated['id_lot']);
                if ($nouveauLot && $nouveauLot->lotissement) {
                    $nouvelleVille = $nouveauLot->lotissement->ville;
                    if ($nouvelleVille) {
                        $contrat->mettreAJourMontantsDocuments($nouvelleVille);
                        Log::info('Montants des documents ACD mis à jour après changement de lot', [
                            'contrat_id' => $contrat->id_contrat,
                            'nouvelle_ville' => $nouvelleVille
                        ]);
                    }
                }
            }

            if ($validated['type_paiement'] === 'ECHELONNE') {
                $totalPaye = $contrat->montant_paye;
                $doitRegenerer = false;

                if ($ancienTypePaiement !== 'ECHELONNE') {
                    $doitRegenerer = true;
                } elseif ($ancienneDuree != $validated['duree_mois']) {
                    $doitRegenerer = true;
                } elseif ($ancienMontant != $validated['montant_total'] && $totalPaye == 0) {
                    $doitRegenerer = true;
                }

                if ($doitRegenerer && $totalPaye == 0) {
                    $contrat->genererEcheances();
                }
            }

            if ($changementLot) {
                $autreContratAncienLot = Contrat::where('id_lot', $ancienIdLot)
                                                ->where('id_contrat', '!=', $id)
                                                ->where(function($query) {
                                                    $query->where('valeursupprimee', '!=', 1)
                                                          ->orWhereNull('valeursupprimee');
                                                })
                                                ->exists();

                if (!$autreContratAncienLot) {
                    $ancienLot = Lot::find($ancienIdLot);
                    if ($ancienLot && $ancienLot->statut !== 'VENDU') {
                        $ancienLot->update(['statut' => 'DISPONIBLE']);
                        Log::info('Lot remis à DISPONIBLE après changement de contrat', [
                            'lot_id' => $ancienIdLot,
                            'ancien_contrat_id' => $id
                        ]);
                    }
                }

                $nouveauLot = Lot::find($validated['id_lot']);
                if ($nouveauLot && $nouveauLot->statut === 'DISPONIBLE') {
                    $nouveauLot->update(['statut' => 'RESERVE']);
                    Log::info('Lot marqué comme RÉSERVÉ après changement de contrat', [
                        'lot_id' => $validated['id_lot'],
                        'nouveau_contrat_id' => $id
                    ]);
                }
            }

            DB::commit();

            $contrat->load(['client', 'lot', 'paiements', 'echeances']);

            return response()->json([
                'success' => true,
                'message' => 'Contrat modifié avec succès',
                'contrat' => $contrat
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur modification contrat: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un contrat avec authentification administrateur
     */
    public function adminDelete(Request $request, $id)
    {
        try {
            $request->validate([
                'admin_password' => 'required|string',
            ]);

            $admin = \App\Models\User::where('role', 'admin')
                                     ->orWhere('is_admin', true)
                                     ->first();

            if (!$admin || !Hash::check($request->admin_password, $admin->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mot de passe administrateur incorrect.'
                ], 403);
            }

            $contrat = Contrat::where('id_contrat', $id)->firstOrFail();

            DB::beginTransaction();

            $idLot = $contrat->id_lot;

            // Soft delete du contrat
            $contrat->update(['valeursupprimee' => 1]);

            // Soft delete de TOUS les paiements associés
            Paiement::where('id_contrat', $id)
                    ->update(['valeursupprimee' => 1]);

            // Soft delete de TOUTES les échéances associées
            Echeance::where('id_contrat', $id)
                    ->update(['valeursupprimee' => 1]);

            // Soft delete des documents ACD associés
            \App\Models\ContratDocument::where('id_contrat', $id)
                    ->update(['valeursupprimee' => 1]);

            // Mettre à jour le statut du lot après suppression
            $this->updateLotStatutAfterContratDeletion($idLot, $id);

            DB::commit();

            $nomSupprime = $contrat->reference_contrat;

            return response()->json([
                'success' => true,
                'message' => 'Contrat "' . $nomSupprime . '" et toutes ses données associées supprimés avec succès.'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Contrat introuvable'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Mot de passe administrateur requis.'
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur suppression admin contrat: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un contrat (soft delete) - UNIQUEMENT si pas de paiements ET non soldé
     */
    public function destroy($id)
    {
        try {
            $contrat = Contrat::where('id_contrat', $id)->firstOrFail();

            $nombrePaiements = Paiement::where('id_contrat', $id)
                                       ->where('statut', 'VALIDE')
                                       ->where(function($query) {
                                           $query->where('valeursupprimee', '!=', 1)
                                                 ->orWhereNull('valeursupprimee');
                                       })
                                       ->count();

            if ($contrat->est_solde || $contrat->statut === 'SOLDE') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce contrat est soldé. Utilisez la suppression administrateur.',
                    'requires_admin' => true,
                    'raison' => 'contrat_solde'
                ], 403);
            }

            if ($nombrePaiements > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce contrat a ' . $nombrePaiements . ' paiement(s) associé(s). Utilisez la suppression administrateur.',
                    'requires_admin' => true,
                    'raison' => 'a_des_paiements',
                    'nombre_paiements' => $nombrePaiements
                ], 403);
            }

            DB::beginTransaction();

            $idLot = $contrat->id_lot;

            // Soft delete du contrat
            $contrat->update(['valeursupprimee' => 1]);

            // Soft delete des échéances associées
            Echeance::where('id_contrat', $id)->update(['valeursupprimee' => 1]);

            // Soft delete des documents ACD associés
            \App\Models\ContratDocument::where('id_contrat', $id)->update(['valeursupprimee' => 1]);

            // Mettre à jour le statut du lot après suppression
            $this->updateLotStatutAfterContratDeletion($idLot, $id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contrat supprimé avec succès. Le lot associé a été remis à disponible.'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Contrat introuvable'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur suppression contrat: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour le statut d'un lot après suppression d'un contrat
     */
    private function updateLotStatutAfterContratDeletion($lotId, $excludedContratId = null)
    {
        $lot = Lot::find($lotId);
        if (!$lot) {
            Log::warning('Lot non trouvé pour mise à jour', ['lot_id' => $lotId]);
            return;
        }

        // Vérifier s'il existe d'autres contrats actifs sur ce lot
        $query = Contrat::where('id_lot', $lotId)
                        ->where(function($query) {
                            $query->where('valeursupprimee', '!=', 1)
                                  ->orWhereNull('valeursupprimee');
                        });

        if ($excludedContratId) {
            $query->where('id_contrat', '!=', $excludedContratId);
        }

        $autreContratActif = $query->exists();

        if (!$autreContratActif) {
            // Aucun autre contrat actif sur ce lot → remettre à DISPONIBLE
            $ancienStatut = $lot->statut;

            // Vérifier que le lot n'est pas marqué comme vendu définitivement
            if ($lot->statut !== 'VENDU') {
                $lot->statut = 'DISPONIBLE';
                $lot->save();

                Log::info('Lot remis à DISPONIBLE après suppression de contrat', [
                    'lot_id' => $lotId,
                    'lot_numero' => $lot->numero_lot,
                    'ancien_statut' => $ancienStatut,
                    'contrat_supprime_id' => $excludedContratId
                ]);
            } else {
                Log::info('Lot VENDU non modifié après suppression de contrat', [
                    'lot_id' => $lotId,
                    'contrat_supprime_id' => $excludedContratId
                ]);
            }
        } else {
            Log::info('Lot non modifié car un autre contrat actif existe', [
                'lot_id' => $lotId,
                'contrat_supprime_id' => $excludedContratId
            ]);
        }
    }

    /**
     * Récupérer tous les clients pour le select
     */
    public function getClients()
    {
        $clients = Client::where('valeursupprimee', '!=', 1)
                         ->orWhereNull('valeursupprimee')
                         ->orderBy('nom_prenoms', 'asc')
                         ->get(['id', 'nom_prenoms']);

        return response()->json($clients);
    }

    /**
     * Récupérer tous les lots disponibles pour le select
     */
    public function getLotsDisponibles()
    {
        $lots = Lot::with('lotissement')
                   ->where('statut', 'DISPONIBLE')
                   ->where('valeursupprimee', '!=', 1)
                   ->orWhereNull('valeursupprimee')
                   ->orderBy('numero_lot', 'asc')
                   ->get(['id_lot', 'numero_lot', 'numero_ilot', 'prix_comptant', 'prix_echelonne', 'id_lotissement']);

        return response()->json($lots);
    }

    /**
     * Récupérer le prix d'un lot
     */
    public function getPrixLot($idLot)
    {
        $lot = Lot::where('id_lot', $idLot)
                  ->where(function($query) {
                      $query->where('valeursupprimee', '!=', 1)
                            ->orWhereNull('valeursupprimee');
                  })
                  ->first(['prix_comptant', 'prix_echelonne']);

        return response()->json([
            'prix_comptant' => $lot ? floatval($lot->prix_comptant) : 0,
            'prix_echelonne' => $lot ? floatval($lot->prix_echelonne) : 0,
            'prix_total' => $lot ? floatval($lot->prix_comptant) : 0
        ]);
    }

    /**
     * Générer un PDF pour l'impression du récapitulatif du contrat
     */
    public function imprimerRecap($id)
    {
        try {
            $contrat = Contrat::with(['client', 'lot', 'lot.lotissement', 'echeances'])
                              ->where('id_contrat', $id)
                              ->where(function($query) {
                                  $query->where('valeursupprimee', '!=', 1)
                                        ->orWhereNull('valeursupprimee');
                              })
                              ->first();

            if (!$contrat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contrat introuvable'
                ], 404);
            }

            $contrat->verifierEtMettreAJourStatut();
            $contrat->refresh();
            $contrat->load('echeances');

            $paiements = Paiement::where('id_contrat', $id)
                                 ->where(function($query) {
                                     $query->where('valeursupprimee', '!=', 1)
                                           ->orWhereNull('valeursupprimee');
                                 })
                                 ->orderBy('date_paiement', 'desc')
                                 ->get();

            $montantTotal = floatval($contrat->montant_total ?? 0);
            $montantPaye = $paiements->where('statut', 'VALIDE')->sum('montant');
            $resteAPayer = max(0, $montantTotal - $montantPaye);
            $progression = $montantTotal > 0 ? min(100, round(($montantPaye / $montantTotal) * 100)) : 0;

            $echeances = $contrat->echeances->map(function($echeance) {
                return [
                    'numero' => $echeance->numero_echeance,
                    'date_echeance' => $echeance->date_echeance_formatted,
                    'montant_prevu' => floatval($echeance->montant_prevu),
                    'montant_prevu_formatted' => $echeance->montant_prevu_formatted,
                    'montant_paye' => floatval($echeance->montant_paye),
                    'montant_paye_formatted' => $echeance->montant_paye_formatted,
                    'reste_a_payer' => $echeance->reste_a_payer,
                    'reste_a_payer_formatted' => $echeance->reste_a_payer_formatted,
                    'statut' => $echeance->statut,
                    'statut_label' => $echeance->statut_label
                ];
            });

            // Ajouter les frais ACD à la réponse
            $fraisAcd = [];
            $totalFraisAcd = 0;
            $ville = $contrat->lot && $contrat->lot->lotissement ? $contrat->lot->lotissement->ville : null;
            
            if ($ville) {
                $fraisAcdData = VilleDocumentFrais::with('documentType')
                    ->where('ville', $ville)
                    ->where('valeursupprimee', 0)
                    ->get();
                
                $fraisAcd = $fraisAcdData->map(function($item) {
                    return [
                        'type_document' => $item->documentType ? $item->documentType->nom : 'Inconnu',
                        'code' => $item->documentType ? $item->documentType->code : '',
                        'montant' => $item->montant,
                        'montant_formatted' => number_format($item->montant, 0, ',', ' ') . ' FCFA',
                        'est_obligatoire' => $item->documentType ? $item->documentType->est_obligatoire : false
                    ];
                });
                $totalFraisAcd = $fraisAcdData->sum('montant');
            }

            return response()->json([
                'success' => true,
                'contrat' => [
                    'id_contrat' => $contrat->id_contrat,
                    'reference_contrat' => $contrat->reference_contrat,
                    'client' => $contrat->client,
                    'lot' => $contrat->lot,
                    'date_contrat' => $contrat->date_contrat,
                    'montant_total' => $montantTotal,
                    'type_paiement' => $contrat->type_paiement,
                    'statut' => $contrat->statut,
                    'mode_paiement' => $contrat->mode_paiement,
                    'banque_paiement' => $contrat->banque_paiement,
                    'reste_a_payer' => $resteAPayer,
                    'duree_mois' => $contrat->duree_mois,
                    'ville' => $ville,
                    'frais_acd' => $fraisAcd,
                    'total_frais_acd' => $totalFraisAcd,
                    'total_frais_acd_formatted' => number_format($totalFraisAcd, 0, ',', ' ') . ' FCFA'
                ],
                'situation' => [
                    'montant_total' => number_format($montantTotal, 0, ',', ' ') . ' FCFA',
                    'montant_paye' => number_format($montantPaye, 0, ',', ' ') . ' FCFA',
                    'reste_a_payer' => number_format($resteAPayer, 0, ',', ' ') . ' FCFA',
                    'progression' => $progression
                ],
                'echeances' => $echeances,
                'paiements' => $paiements
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur impression récap: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }
}