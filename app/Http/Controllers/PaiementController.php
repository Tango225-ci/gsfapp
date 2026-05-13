<?php

namespace App\Http\Controllers;

use App\Models\Paiement;
use App\Models\Contrat;
use App\Models\Lot;
use App\Models\Echeance;
use App\Models\ContratDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PaiementController extends Controller
{
    /**
     * Récupérer tous les paiements (non supprimés)
     */
    public function index()
    {
        $paiements = Paiement::with(['contrat', 'contrat.client', 'contrat.lot'])
                             ->where('valeursupprimee', '!=', 1)
                             ->orWhereNull('valeursupprimee')
                             ->orderBy('date_paiement', 'desc')
                             ->get();

        return response()->json($paiements);
    }

    /**
     * Récupérer un paiement spécifique
     */
    public function show($id)
    {
        $paiement = Paiement::with(['contrat', 'contrat.client', 'contrat.lot'])
                            ->where('id_paiement', $id)
                            ->where(function($query) {
                                $query->where('valeursupprimee', '!=', 1)
                                      ->orWhereNull('valeursupprimee');
                            })
                            ->firstOrFail();

        return response()->json($paiement);
    }

    /**
     * Obtenir le montant suggéré pour un paiement
     */
    public function getMontantSuggere($idContrat)
    {
        try {
            $contrat = Contrat::with('echeances')
                              ->where('id_contrat', $idContrat)
                              ->where('valeursupprimee', '!=', 1)
                              ->first();

            if (!$contrat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contrat introuvable'
                ], 404);
            }

            // Mettre à jour les échéances en retard
            $contrat->verifierEtMettreAJourStatut();
            $contrat->refresh();
            $contrat->load('echeances');

            $resteAPayer = $contrat->reste_a_payer_calcule;

            // Pour les contrats échelonnés, suggérer le montant de la prochaine échéance
            $montantSuggere = $resteAPayer;
            $prochaineEcheance = null;
            $message = "Reste à payer total";

            if ($contrat->type_paiement === 'ECHELONNE') {
                $prochaineEcheance = $contrat->prochaine_echeance;
                if ($prochaineEcheance) {
                    $montantSuggere = min($prochaineEcheance['reste_a_payer'], $resteAPayer);
                    $message = "Montant de la prochaine échéance (n°" . $prochaineEcheance['numero'] . ")";
                }
            }

            return response()->json([
                'success' => true,
                'montant_suggere' => $montantSuggere,
                'montant_suggere_formatted' => number_format($montantSuggere, 0, ',', ' ') . ' FCFA',
                'reste_a_payer_total' => $resteAPayer,
                'reste_a_payer_total_formatted' => number_format($resteAPayer, 0, ',', ' ') . ' FCFA',
                'message' => $message,
                'prochaine_echeance' => $prochaineEcheance,
                'type_paiement' => $contrat->type_paiement
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur suggestion montant: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage(),
                'montant_suggere' => 0
            ], 500);
        }
    }

    /**
     * Créer un nouveau paiement avec affectation aux échéances
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_contrat'      => 'required|exists:contrats,id_contrat',
                'date_paiement'   => 'required|date',
                'montant'         => 'required|numeric|min:0',
                'mode_paiement'   => 'required|in:ESPECES,VIREMENT,CHEQUE,MOBILE_MONEY',
                'reference'       => 'nullable|string|max:100',
                'banque_paiement' => 'nullable|string|max:100',
                'statut'          => 'required|in:EN_ATTENTE,VALIDE,ANNULE,REJETE',
                'notes'           => 'nullable|string'
            ]);

            $contrat = Contrat::with('echeances')
                              ->where('id_contrat', $validated['id_contrat'])
                              ->where(function($query) {
                                  $query->where('valeursupprimee', '!=', 1)
                                        ->orWhereNull('valeursupprimee');
                              })
                              ->first();

            if (!$contrat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contrat introuvable ou supprimé.'
                ], 404);
            }

            if (!$contrat->peutRecevoirPaiement()) {
                $message = $contrat->statut === 'SOLDE'
                    ? 'Ce contrat est déjà soldé.'
                    : 'Ce contrat ne peut plus recevoir de paiement.';
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            if ($validated['statut'] === 'VALIDE') {
                $resteAPayer = $contrat->reste_a_payer_calcule;

                if ($validated['montant'] > $resteAPayer) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Le montant dépasse le reste à payer (' . number_format($resteAPayer, 0, ',', ' ') . ' FCFA).'
                    ], 422);
                }
            }

            DB::beginTransaction();

            try {
                $validated['valeursupprimee'] = 0;
                $validated['type_paiement'] = 'ECHELONNE';
                $paiement = Paiement::create($validated);

                $resultatEcheances = null;
                $echeancesAffectees = [];

                // Si le paiement est validé et que c'est un contrat échelonné
                if ($validated['statut'] === 'VALIDE' && $contrat->type_paiement === 'ECHELONNE') {
                    $resultatEcheances = $contrat->traiterPaiementEcheances($validated['montant']);
                    $echeancesAffectees = $resultatEcheances['echeances_affectees'];
                }

                $contrat->updateContratStatut();

                // Mettre à jour le statut du lot si le contrat devient soldé
                $contrat->refresh();
                if ($contrat->statut === 'SOLDE') {
                    $lot = Lot::find($contrat->id_lot);
                    if ($lot && $lot->statut !== 'VENDU') {
                        $lot->update(['statut' => 'VENDU']);
                        Log::info('Lot marqué comme VENDU après paiement', [
                            'lot_id' => $lot->id_lot,
                            'contrat_id' => $contrat->id_contrat
                        ]);
                    }
                }

                DB::commit();

                // Recharger le contrat avec toutes ses relations
                $contrat->refresh();
                $contrat->load(['echeances', 'paiements']);

                $response = [
                    'success' => true,
                    'message' => 'Paiement enregistré avec succès.',
                    'details' => [
                        'reste_a_payer' => $contrat->reste_a_payer_formatted,
                        'reste_a_payer_raw' => $contrat->reste_a_payer_calcule,
                        'contrat_solde' => $contrat->statut === 'SOLDE',
                        'progression' => $contrat->progression_paiement
                    ],
                    'paiement' => $paiement->load('contrat')
                ];

                // Ajouter les informations sur les échéances affectées
                if (!empty($echeancesAffectees)) {
                    $response['echeances_affectees'] = $echeancesAffectees;
                    $response['montant_restant_non_affecte'] = $resultatEcheances['montant_restant'] ?? 0;
                    $response['message'] = count($echeancesAffectees) . ' échéance(s) mise(s) à jour.';
                }

                return response()->json($response, 201);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Erreur création paiement: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Modifier un paiement
     */
    public function update(Request $request, $id)
    {
        try {
            $paiement = Paiement::where('id_paiement', $id)
                                ->where('valeursupprimee', '!=', 1)
                                ->firstOrFail();

            $validated = $request->validate([
                'date_paiement'   => 'required|date',
                'montant'         => 'required|numeric|min:0',
                'mode_paiement'   => 'required|in:ESPECES,VIREMENT,CHEQUE,MOBILE_MONEY',
                'reference'       => 'nullable|string|max:100',
                'banque_paiement' => 'nullable|string|max:100',
                'statut'          => 'required|in:EN_ATTENTE,VALIDE,ANNULE,REJETE',
                'notes'           => 'nullable|string'
            ]);

            $contrat = Contrat::with('echeances')->find($paiement->id_contrat);

            if (!$contrat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contrat introuvable.'
                ], 404);
            }

            if ($validated['statut'] === 'VALIDE') {
                $totalPayeSansCePaiement = Paiement::where('id_contrat', $contrat->id_contrat)
                                                   ->where('statut', 'VALIDE')
                                                   ->where('id_paiement', '!=', $paiement->id_paiement)
                                                   ->where(function($query) {
                                                       $query->where('valeursupprimee', '!=', 1)
                                                             ->orWhereNull('valeursupprimee');
                                                   })
                                                   ->sum('montant');

                $nouveauTotal = $totalPayeSansCePaiement + $validated['montant'];

                if ($nouveauTotal > $contrat->montant_total) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Le montant total dépasse le montant du contrat.'
                    ], 422);
                }
            }

            DB::beginTransaction();

            try {
                $ancienStatut = $paiement->statut;
                $paiement->update($validated);

                // Mettre à jour les échéances si le statut change
                if ($contrat->type_paiement === 'ECHELONNE') {
                    // Recalculer toutes les échéances en fonction des paiements validés
                    $this->recalculerEcheancesContrat($contrat);
                }

                $contrat->updateContratStatut();

                // Mettre à jour le statut du lot
                $contrat->refresh();
                if ($contrat->statut === 'SOLDE') {
                    $lot = Lot::find($contrat->id_lot);
                    if ($lot && $lot->statut !== 'VENDU') {
                        $lot->update(['statut' => 'VENDU']);
                    }
                } elseif ($contrat->statut === 'EN_COURS' || $contrat->statut === 'EN_RETARD') {
                    $lot = Lot::find($contrat->id_lot);
                    if ($lot && $lot->statut === 'VENDU') {
                        $autreContratSolde = Contrat::where('id_lot', $contrat->id_lot)
                                                    ->where('id_contrat', '!=', $contrat->id_contrat)
                                                    ->where('statut', 'SOLDE')
                                                    ->where(function($query) {
                                                        $query->where('valeursupprimee', '!=', 1)
                                                              ->orWhereNull('valeursupprimee');
                                                    })
                                                    ->exists();
                        if (!$autreContratSolde) {
                            $lot->update(['statut' => 'RESERVE']);
                        }
                    }
                }

                DB::commit();

                $contrat->refresh();
                $contrat->load('echeances');

                return response()->json([
                    'success' => true,
                    'message' => 'Paiement modifié avec succès',
                    'details' => [
                        'reste_a_payer' => $contrat->reste_a_payer_formatted,
                        'contrat_solde' => $contrat->statut === 'SOLDE',
                        'progression' => $contrat->progression_paiement
                    ],
                    'paiement' => $paiement->load('contrat')
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Erreur modification paiement: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un paiement (soft delete)
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $paiement = Paiement::where('id_paiement', $id)->firstOrFail();
            $idContrat = $paiement->id_contrat;
            $paiement->update(['valeursupprimee' => 1]);

            $contrat = Contrat::with('echeances')->find($idContrat);
            if ($contrat) {
                // Recalculer les échéances pour les contrats échelonnés
                if ($contrat->type_paiement === 'ECHELONNE') {
                    $this->recalculerEcheancesContrat($contrat);
                }

                $contrat->updateContratStatut();

                // Mettre à jour le statut du lot
                $contrat->refresh();
                if ($contrat->statut !== 'SOLDE') {
                    $lot = Lot::find($contrat->id_lot);
                    if ($lot && $lot->statut === 'VENDU') {
                        $autreContratSolde = Contrat::where('id_lot', $contrat->id_lot)
                                                    ->where('statut', 'SOLDE')
                                                    ->where(function($query) {
                                                        $query->where('valeursupprimee', '!=', 1)
                                                              ->orWhereNull('valeursupprimee');
                                                    })
                                                    ->exists();
                        if (!$autreContratSolde) {
                            $lot->update(['statut' => 'RESERVE']);
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Paiement supprimé avec succès',
                'details' => [
                    'reste_a_payer' => $contrat ? $contrat->reste_a_payer_formatted : '0 FCFA'
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur suppression paiement: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Valider un paiement
     */
    public function valider($id)
    {
        try {
            DB::beginTransaction();

            $paiement = Paiement::where('id_paiement', $id)
                                ->where('valeursupprimee', '!=', 1)
                                ->firstOrFail();

            $contrat = Contrat::with('echeances')->find($paiement->id_contrat);

            if ($contrat) {
                $totalPayeSansCePaiement = Paiement::where('id_contrat', $contrat->id_contrat)
                                                   ->where('statut', 'VALIDE')
                                                   ->where('id_paiement', '!=', $paiement->id_paiement)
                                                   ->where(function($query) {
                                                       $query->where('valeursupprimee', '!=', 1)
                                                             ->orWhereNull('valeursupprimee');
                                                   })
                                                   ->sum('montant');

                $nouveauTotal = $totalPayeSansCePaiement + $paiement->montant;

                if ($nouveauTotal > $contrat->montant_total) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Impossible de valider ce paiement car il dépasserait le montant du contrat.'
                    ], 422);
                }
            }

            $paiement->valider();

            // Affecter le paiement aux échéances si contrat échelonné
            $echeancesAffectees = [];
            if ($contrat && $contrat->type_paiement === 'ECHELONNE') {
                $resultat = $contrat->traiterPaiementEcheances($paiement->montant);
                $echeancesAffectees = $resultat['echeances_affectees'];
            }

            $contrat->updateContratStatut();

            // Mettre à jour le statut du lot
            $contrat->refresh();
            if ($contrat->statut === 'SOLDE') {
                $lot = Lot::find($contrat->id_lot);
                if ($lot && $lot->statut !== 'VENDU') {
                    $lot->update(['statut' => 'VENDU']);
                    Log::info('Lot marqué comme VENDU après validation du paiement', [
                        'lot_id' => $lot->id_lot,
                        'contrat_id' => $contrat->id_contrat
                    ]);
                }
            }

            DB::commit();

            $contrat->refresh();

            $response = [
                'success' => true,
                'message' => 'Paiement validé avec succès',
                'details' => [
                    'reste_a_payer' => $contrat->reste_a_payer_formatted,
                    'contrat_solde' => $contrat->statut === 'SOLDE',
                    'progression' => $contrat->progression_paiement
                ]
            ];

            if (!empty($echeancesAffectees)) {
                $response['echeances_affectees'] = $echeancesAffectees;
                $response['message'] .= ' - ' . count($echeancesAffectees) . ' échéance(s) mise(s) à jour.';
            }

            return response()->json($response);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur validation paiement: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rejeter un paiement
     */
    public function rejeter($id)
    {
        try {
            DB::beginTransaction();

            $paiement = Paiement::where('id_paiement', $id)
                                ->where('valeursupprimee', '!=', 1)
                                ->firstOrFail();

            $paiement->rejeter();

            $contrat = Contrat::with('echeances')->find($paiement->id_contrat);
            if ($contrat) {
                // Recalculer les échéances si nécessaire
                if ($contrat->type_paiement === 'ECHELONNE') {
                    $this->recalculerEcheancesContrat($contrat);
                }
                $contrat->updateContratStatut();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Paiement rejeté avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur rejet paiement: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les paiements par contrat
     */
    public function getByContrat($idContrat)
    {
        $paiements = Paiement::with('contrat')
                             ->where('id_contrat', $idContrat)
                             ->where(function($query) {
                                 $query->where('valeursupprimee', '!=', 1)
                                       ->orWhereNull('valeursupprimee');
                             })
                             ->orderBy('date_paiement', 'desc')
                             ->get();

        return response()->json($paiements);
    }

    /**
     * Récupérer le prochain numéro de séquence
     */
    public function getNextSequence($yearMonth)
    {
        try {
            $date = \Carbon\Carbon::createFromFormat('Y-m', $yearMonth);
            $formattedYearMonth = $date->format('Ym');

            $count = Paiement::where('reference', 'LIKE', 'PAI-' . $formattedYearMonth . '-%')
                             ->count();

            return response()->json([
                'nextSequence' => $count + 1,
                'formatted' => str_pad($count + 1, 4, '0', STR_PAD_LEFT)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'nextSequence' => 1,
                'formatted' => '0001'
            ]);
        }
    }

    /**
     * Recalculer toutes les échéances d'un contrat en fonction des paiements validés
     */
    private function recalculerEcheancesContrat($contrat)
    {
        try {
            // Réinitialiser toutes les échéances
            $echeances = Echeance::where('id_contrat', $contrat->id_contrat)
                                 ->where('valeursupprimee', 0)
                                 ->orderBy('date_echeance', 'asc')
                                 ->get();

            foreach ($echeances as $echeance) {
                $echeance->montant_paye = 0;
                $echeance->statut = 'A_PAYER';
                $echeance->save();
            }

            // Récupérer tous les paiements validés
            $paiements = Paiement::where('id_contrat', $contrat->id_contrat)
                                 ->where('statut', 'VALIDE')
                                 ->where('valeursupprimee', '!=', 1)
                                 ->orderBy('date_paiement', 'asc')
                                 ->get();

            // Réappliquer chaque paiement aux échéances
            foreach ($paiements as $paiement) {
                $contrat->traiterPaiementEcheances($paiement->montant);
            }

            // Mettre à jour les statuts des échéances
            $contrat->verifierEtMettreAJourStatut();

            Log::info('Échéances recalculées pour le contrat', [
                'contrat_id' => $contrat->id_contrat,
                'nombre_paiements' => $paiements->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur recalcul échéances: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Créer un paiement pour des frais de document
     */
    public function storeFraisDocument(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_contrat'           => 'required|exists:contrats,id_contrat',
                'id_contrat_document'  => 'required|exists:contrat_documents,id_contrat_document',
                'date_paiement'        => 'required|date',
                'montant'              => 'required|numeric|min:0',
                'mode_paiement'        => 'required|in:ESPECES,VIREMENT,CHEQUE,MOBILE_MONEY',
                'banque_paiement'      => 'nullable|string|max:100',
                'statut'               => 'required|in:EN_ATTENTE,VALIDE,ANNULE,REJETE',
                'notes'                => 'nullable|string'
            ]);

            $contratDocument = ContratDocument::where('id_contrat_document', $validated['id_contrat_document'])
                                              ->where('id_contrat', $validated['id_contrat'])
                                              ->where('valeursupprimee', 0)
                                              ->first();

            if (!$contratDocument) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document introuvable ou non lié à ce contrat.'
                ], 404);
            }

            // Vérifier si le document a un montant > 0
            if ($contratDocument->montant <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce document n\'a pas de frais à payer (montant = 0 FCFA).'
                ], 422);
            }

            if ($contratDocument->statut_paiement === 'paye') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce document est déjà entièrement payé.'
                ], 422);
            }

            // Calculer le reste à payer
            $dejaPaye = $contratDocument->paiements()->where('statut', 'VALIDE')->sum('montant');
            $resteAPayer = $contratDocument->montant - $dejaPaye;

            if ($validated['montant'] > $resteAPayer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le montant dépasse le reste à payer (' . number_format($resteAPayer, 0, ',', ' ') . ' FCFA).'
                ], 422);
            }

            DB::beginTransaction();

            // Générer la référence
            $reference = $this->genererReferencePaiement();

            $paiementData = [
                'id_contrat' => $validated['id_contrat'],
                'id_contrat_document' => $validated['id_contrat_document'],
                'date_paiement' => $validated['date_paiement'],
                'montant' => $validated['montant'],
                'mode_paiement' => $validated['mode_paiement'],
                'banque_paiement' => $validated['banque_paiement'] ?? null,
                'statut' => $validated['statut'],
                'notes' => $validated['notes'] ?? null,
                'type_paiement' => 'FRAIS_DOCUMENT',
                'reference' => $reference,
                'valeursupprimee' => 0
            ];

            $paiement = Paiement::create($paiementData);

            // Si le paiement est validé immédiatement
            if ($validated['statut'] === 'VALIDE') {
                $nouveauTotalPaye = $dejaPaye + $validated['montant'];
                if ($nouveauTotalPaye >= $contratDocument->montant) {
                    $contratDocument->marquerPaye($reference);
                } elseif ($nouveauTotalPaye > 0) {
                    $contratDocument->marquerPartiel();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Paiement des frais document enregistré avec succès',
                'paiement' => $paiement->load('contrat'),
                'document' => $contratDocument->refresh()
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur paiement frais document: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Générer une référence de paiement unique
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
     * Récupérer les paiements d'un document spécifique
     */
    public function getPaiementsByDocument($idContratDocument)
    {
        try {
            $paiements = Paiement::where('id_contrat_document', $idContratDocument)
                                 ->where('type_paiement', 'FRAIS_DOCUMENT')
                                 ->where('valeursupprimee', '!=', 1)
                                 ->orderBy('date_paiement', 'desc')
                                 ->get();

            $totalPaye = $paiements->where('statut', 'VALIDE')->sum('montant');

            return response()->json([
                'success' => true,
                'paiements' => $paiements,
                'total_paye' => $totalPaye,
                'total_paye_formatted' => number_format($totalPaye, 0, ',', ' ') . ' FCFA'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur récupération paiements document: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }
}