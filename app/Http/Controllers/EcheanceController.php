<?php

namespace App\Http\Controllers;

use App\Models\Echeance;
use App\Models\Contrat;
use App\Models\Paiement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class EcheanceController extends Controller
{
    /**
     * Récupérer TOUTES les échéances groupées par contrat (pour la vue globale avec accordéon)
     */
    public function getAll()
    {
        try {
            $echeances = Echeance::with(['contrat.client', 'contrat.lot'])
                                 ->where('valeursupprimee', 0)
                                 ->orderBy('date_echeance', 'asc')
                                 ->get();

            // Grouper les échéances par contrat
            $grouped = [];
            foreach ($echeances as $echeance) {
                $contrat = $echeance->contrat;
                $idContrat = $contrat ? $contrat->id_contrat : 'sans_contrat';

                if (!isset($grouped[$idContrat])) {
                    $client = $contrat ? $contrat->client : null;
                    $lot = $contrat ? $contrat->lot : null;

                    $echeancesContrat = $echeances->where('id_contrat', $idContrat);
                    $totalEcheances = $echeancesContrat->count();
                    $totalPayees = $echeancesContrat->where('statut', 'PAYE')->count();
                    $totalEnRetard = $echeancesContrat->where('statut', 'EN_RETARD')->count();
                    $montantTotal = $echeancesContrat->sum('montant_prevu');
                    $montantPaye = $echeancesContrat->sum('montant_paye');
                    $progression = $montantTotal > 0 ? round(($montantPaye / $montantTotal) * 100) : 0;

                    $grouped[$idContrat] = [
                        'id_contrat' => $idContrat,
                        'contrat_reference' => $contrat ? $contrat->reference_contrat : '-',
                        'client_nom' => $client ? $client->nom_prenoms : '-',
                        'lot_numero' => $lot ? $lot->numero_lot : '-',
                        'montant_total' => floatval($montantTotal),
                        'montant_total_formatted' => number_format($montantTotal, 0, ',', ' ') . ' FCFA',
                        'montant_paye' => floatval($montantPaye),
                        'montant_paye_formatted' => number_format($montantPaye, 0, ',', ' ') . ' FCFA',
                        'reste_a_payer' => max(0, $montantTotal - $montantPaye),
                        'reste_a_payer_formatted' => number_format(max(0, $montantTotal - $montantPaye), 0, ',', ' ') . ' FCFA',
                        'total_echeances' => $totalEcheances,
                        'echeances_payees' => $totalPayees,
                        'echeances_en_retard' => $totalEnRetard,
                        'progression' => $progression,
                        'contrat_statut' => $contrat ? $contrat->statut : 'EN_COURS',
                        'contrat_statut_label' => $contrat ? $contrat->statut_label : 'En cours',
                        'est_solde' => $contrat ? $contrat->est_solde : false,
                        'echeances' => []
                    ];
                }

                $grouped[$idContrat]['echeances'][] = [
                    'id' => $echeance->id,
                    'numero' => $echeance->numero_echeance,
                    'id_contrat' => $contrat ? $contrat->id_contrat : null,
                    'contrat_reference' => $contrat ? $contrat->reference_contrat : '-',
                    'client_nom' => isset($client) ? $client->nom_prenoms : '-',
                    'lot_numero' => isset($lot) ? $lot->numero_lot : '-',
                    'date_echeance' => $echeance->date_echeance_formatted,
                    'date_echeance_raw' => $echeance->date_echeance,
                    'montant_prevu' => floatval($echeance->montant_prevu),
                    'montant_prevu_formatted' => number_format($echeance->montant_prevu, 0, ',', ' ') . ' FCFA',
                    'montant_paye' => floatval($echeance->montant_paye),
                    'montant_paye_formatted' => number_format($echeance->montant_paye, 0, ',', ' ') . ' FCFA',
                    'reste_a_payer' => max(0, $echeance->montant_prevu - $echeance->montant_paye),
                    'reste_a_payer_formatted' => number_format(max(0, $echeance->montant_prevu - $echeance->montant_paye), 0, ',', ' ') . ' FCFA',
                    'statut' => $echeance->statut,
                    'statut_label' => $this->getStatutLabel($echeance->statut),
                    'statut_class' => $this->getStatutClass($echeance->statut),
                    'est_en_retard' => $echeance->statut === 'EN_RETARD',
                    'est_paye' => $echeance->statut === 'PAYE',
                    'progression' => $echeance->montant_prevu > 0 ? round(($echeance->montant_paye / $echeance->montant_prevu) * 100) : 0
                ];
            }

            // Convertir en tableau indexé
            $contrats = array_values($grouped);

            // Calculer les statistiques globales
            $totalEcheances = $echeances->count();
            $montantTotalAttendu = $echeances->sum('montant_prevu');
            $montantPaye = $echeances->sum('montant_paye');
            $montantReste = $montantTotalAttendu - $montantPaye;
            $enRetard = $echeances->where('statut', 'EN_RETARD')->count();
            $totalContrats = count($contrats);

            return response()->json([
                'success' => true,
                'contrats' => $contrats,
                'statistiques' => [
                    'total_echeances' => $totalEcheances,
                    'total_contrats' => $totalContrats,
                    'montant_total_attendu' => number_format($montantTotalAttendu, 0, ',', ' ') . ' FCFA',
                    'montant_total_attendu_raw' => $montantTotalAttendu,
                    'montant_paye' => number_format($montantPaye, 0, ',', ' ') . ' FCFA',
                    'montant_paye_raw' => $montantPaye,
                    'montant_reste' => number_format($montantReste, 0, ',', ' ') . ' FCFA',
                    'montant_reste_raw' => $montantReste,
                    'en_retard' => $enRetard
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur récupération toutes les échéances: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer toutes les échéances d'un contrat
     */
    public function index($idContrat)
    {
        try {
            $contrat = Contrat::where('id_contrat', $idContrat)
                              ->where('valeursupprimee', '!=', 1)
                              ->first();

            if (!$contrat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contrat introuvable'
                ], 404);
            }

            $contrat->verifierEtMettreAJourStatut();

            $echeances = Echeance::where('id_contrat', $idContrat)
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
     * Récupérer une échéance spécifique
     */
    public function show($id)
    {
        try {
            $echeance = Echeance::with('contrat')
                                ->where('id', $id)
                                ->where('valeursupprimee', 0)
                                ->firstOrFail();

            return response()->json([
                'success' => true,
                'echeance' => [
                    'id' => $echeance->id,
                    'numero' => $echeance->numero_echeance,
                    'id_contrat' => $echeance->id_contrat,
                    'date_echeance' => $echeance->date_echeance,
                    'montant_prevu' => $echeance->montant_prevu,
                    'montant_paye' => $echeance->montant_paye,
                    'statut' => $echeance->statut,
                    'statut_label' => $echeance->statut_label,
                    'reste_a_payer' => $echeance->reste_a_payer,
                    'progression' => $echeance->progression_paiement
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur affichage échéance: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Échéance introuvable'
            ], 404);
        }
    }

    /**
     * Marquer une échéance comme payée manuellement
     * Crée automatiquement un paiement pour tracer l'opération,
     * met à jour le contrat (montant_verse, reste_a_payer, statut)
     */
    public function marquerPayee($id)
    {
        try {
            DB::beginTransaction();

            $echeance = Echeance::where('id', $id)
                                ->where('valeursupprimee', 0)
                                ->firstOrFail();

            // Vérifier si l'échéance n'est pas déjà payée
            if ($echeance->statut === 'PAYE') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette échéance est déjà payée'
                ], 400);
            }

            $montantAPayer = $echeance->reste_a_payer;
            $contrat = $echeance->contrat;

            if (!$contrat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contrat introuvable pour cette échéance'
                ], 404);
            }

            // Créer le paiement (la référence est générée automatiquement par le boot du modèle)
            $paiement = Paiement::create([
                'id_contrat' => $contrat->id_contrat,
                'date_paiement' => now()->format('Y-m-d'),
                'montant' => $montantAPayer,
                'mode_paiement' => 'ESPECES',
                'statut' => 'VALIDE',
                'notes' => "Paiement automatique - Échéance n°{$echeance->numero_echeance} marquée comme payée"
            ]);

            Log::info('Paiement créé automatiquement via marquerPayee', [
                'paiement_id' => $paiement->id_paiement,
                'reference' => $paiement->reference,
                'echeance_id' => $echeance->id,
                'montant' => $montantAPayer
            ]);

            // Marquer l'échéance comme payée
            $echeance->montant_paye = $echeance->montant_prevu;
            $echeance->statut = 'PAYE';
            $echeance->save();

            Log::info('Échéance marquée comme payée', [
                'echeance_id' => $echeance->id,
                'numero' => $echeance->numero_echeance,
                'montant_paye' => $echeance->montant_paye
            ]);

            // Mettre à jour le montant total versé du contrat
            $totalPaye = Echeance::where('id_contrat', $contrat->id_contrat)
                                 ->where('valeursupprimee', 0)
                                 ->sum('montant_paye');

            $contrat->montant_verse = $totalPaye;

            // Mettre à jour le reste à payer
            $contrat->reste_a_payer = max(0, $contrat->montant_total - $totalPaye);
            $contrat->save();

            // Mettre à jour le statut du contrat
            $contrat->verifierEtMettreAJourStatut();

            DB::commit();

            // Recharger le contrat pour avoir le statut à jour
            $contrat->refresh();
            $contratSolde = $contrat->statut === 'SOLDE';

            // Récupérer les échéances mises à jour pour le contrat
            $echeancesAffectees = Echeance::where('id_contrat', $contrat->id_contrat)
                                          ->where('valeursupprimee', 0)
                                          ->orderBy('date_echeance', 'asc')
                                          ->get()
                                          ->map(function($e) {
                                              return [
                                                  'echeance_id' => $e->id,
                                                  'numero' => $e->numero_echeance,
                                                  'date_echeance' => $e->date_echeance_formatted,
                                                  'montant_applique' => $e->montant_paye,
                                                  'statut' => $e->statut
                                              ];
                                          });

            return response()->json([
                'success' => true,
                'message' => 'Échéance marquée comme payée. Un paiement de ' . number_format($montantAPayer, 0, ',', ' ') . ' FCFA a été créé automatiquement.',
                'paiement' => [
                    'id' => $paiement->id_paiement,
                    'reference' => $paiement->reference,
                    'montant' => $paiement->montant,
                    'montant_formatted' => $paiement->montant_formatted
                ],
                'echeance' => [
                    'id' => $echeance->id,
                    'numero' => $echeance->numero_echeance,
                    'statut' => $echeance->statut,
                    'montant_paye' => $echeance->montant_paye
                ],
                'echeances_affectees' => $echeancesAffectees,
                'details' => [
                    'contrat_solde' => $contratSolde,
                    'reste_a_payer' => number_format($contrat->reste_a_payer, 0, ',', ' ') . ' FCFA'
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur marquage échéance payée: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les échéances en retard
     */
    public function getEnRetard()
    {
        try {
            $echeances = Echeance::with(['contrat.client', 'contrat.lot'])
                                 ->where('statut', 'EN_RETARD')
                                 ->where('valeursupprimee', 0)
                                 ->orderBy('date_echeance', 'asc')
                                 ->get();

            $totalMontant = $echeances->sum('montant_prevu');
            $totalPaye = $echeances->sum('montant_paye');
            $totalReste = $totalMontant - $totalPaye;

            return response()->json([
                'success' => true,
                'echeances' => $echeances->map(function($echeance) {
                    $contrat = $echeance->contrat;
                    $client = $contrat ? $contrat->client : null;
                    $lot = $contrat ? $contrat->lot : null;

                    return [
                        'id' => $echeance->id,
                        'numero' => $echeance->numero_echeance,
                        'contrat_reference' => $contrat ? $contrat->reference_contrat : '-',
                        'client_nom' => $client ? $client->nom_prenoms : '-',
                        'lot_numero' => $lot ? $lot->numero_lot : '-',
                        'date_echeance' => $echeance->date_echeance_formatted,
                        'montant_prevu' => floatval($echeance->montant_prevu),
                        'montant_prevu_formatted' => number_format($echeance->montant_prevu, 0, ',', ' ') . ' FCFA',
                        'montant_paye' => floatval($echeance->montant_paye),
                        'montant_paye_formatted' => number_format($echeance->montant_paye, 0, ',', ' ') . ' FCFA',
                        'reste_a_payer' => max(0, $echeance->montant_prevu - $echeance->montant_paye),
                        'reste_a_payer_formatted' => number_format(max(0, $echeance->montant_prevu - $echeance->montant_paye), 0, ',', ' ') . ' FCFA',
                        'statut' => $echeance->statut,
                        'statut_label' => $this->getStatutLabel($echeance->statut),
                        'progression' => $echeance->montant_prevu > 0 ? round(($echeance->montant_paye / $echeance->montant_prevu) * 100) : 0
                    ];
                }),
                'total' => $echeances->count(),
                'statistiques' => [
                    'montant_total' => number_format($totalMontant, 0, ',', ' ') . ' FCFA',
                    'montant_paye' => number_format($totalPaye, 0, ',', ' ') . ' FCFA',
                    'montant_reste' => number_format($totalReste, 0, ',', ' ') . ' FCFA'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur récupération échéances en retard: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les échéances du mois en cours
     */
    public function getMoisEnCours()
    {
        try {
            $debutMois = now()->startOfMonth();
            $finMois = now()->endOfMonth();

            $echeances = Echeance::with(['contrat.client', 'contrat.lot'])
                                 ->whereBetween('date_echeance', [$debutMois, $finMois])
                                 ->where('valeursupprimee', 0)
                                 ->orderBy('date_echeance', 'asc')
                                 ->get();

            $totalMontant = $echeances->sum('montant_prevu');
            $totalPaye = $echeances->sum('montant_paye');
            $totalReste = $totalMontant - $totalPaye;

            return response()->json([
                'success' => true,
                'echeances' => $echeances->map(function($echeance) {
                    $contrat = $echeance->contrat;
                    $client = $contrat ? $contrat->client : null;
                    $lot = $contrat ? $contrat->lot : null;

                    return [
                        'id' => $echeance->id,
                        'numero' => $echeance->numero_echeance,
                        'contrat_reference' => $contrat ? $contrat->reference_contrat : '-',
                        'client_nom' => $client ? $client->nom_prenoms : '-',
                        'lot_numero' => $lot ? $lot->numero_lot : '-',
                        'date_echeance' => $echeance->date_echeance_formatted,
                        'montant_prevu' => floatval($echeance->montant_prevu),
                        'montant_prevu_formatted' => number_format($echeance->montant_prevu, 0, ',', ' ') . ' FCFA',
                        'montant_paye' => floatval($echeance->montant_paye),
                        'montant_paye_formatted' => number_format($echeance->montant_paye, 0, ',', ' ') . ' FCFA',
                        'reste_a_payer' => max(0, $echeance->montant_prevu - $echeance->montant_paye),
                        'reste_a_payer_formatted' => number_format(max(0, $echeance->montant_prevu - $echeance->montant_paye), 0, ',', ' ') . ' FCFA',
                        'statut' => $echeance->statut,
                        'statut_label' => $this->getStatutLabel($echeance->statut),
                        'progression' => $echeance->montant_prevu > 0 ? round(($echeance->montant_paye / $echeance->montant_prevu) * 100) : 0
                    ];
                }),
                'total' => $echeances->count(),
                'periode' => [
                    'debut' => $debutMois->format('d/m/Y'),
                    'fin' => $finMois->format('d/m/Y')
                ],
                'statistiques' => [
                    'montant_total' => number_format($totalMontant, 0, ',', ' ') . ' FCFA',
                    'montant_paye' => number_format($totalPaye, 0, ',', ' ') . ' FCFA',
                    'montant_reste' => number_format($totalReste, 0, ',', ' ') . ' FCFA'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur récupération échéances du mois: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statut label
     */
    private function getStatutLabel($statut)
    {
        $labels = [
            'A_PAYER' => 'À payer',
            'PAYE' => 'Payée',
            'EN_RETARD' => 'En retard'
        ];
        return $labels[$statut] ?? $statut;
    }

    /**
     * Get statut CSS class
     */
    private function getStatutClass($statut)
    {
        $classes = [
            'A_PAYER' => 'bg-yellow-100 text-yellow-800',
            'PAYE' => 'bg-green-100 text-green-800',
            'EN_RETARD' => 'bg-red-100 text-red-800'
        ];
        return $classes[$statut] ?? 'bg-gray-100 text-gray-800';
    }
}
