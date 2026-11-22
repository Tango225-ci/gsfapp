<?php

namespace App\Http\Controllers\Api\Client;

use App\Helpers\NotifHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaiementResource;
use App\Models\Contrat;
use App\Models\ContratDocument;
use App\Models\Echeance;
use App\Models\Paiement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class PaiementController extends Controller
{
    /**
     * Tous les paiements du client, tous contrats confondus.
     */
    public function index(Request $request)
    {
        $client = $request->user();

        $paiements = Paiement::whereHas('contrat', function ($q) use ($client) {
                $q->where('id_client', $client->id)->where('valeursupprimee', 0);
            })
            ->where(function ($q) {
                $q->where('valeursupprimee', '!=', 1)->orWhereNull('valeursupprimee');
            })
            ->with('contrat.lot')
            ->orderByDesc('date_paiement')
            ->get();

        return PaiementResource::collection($paiements);
    }

    /**
     * Le client effectue un paiement (une ou plusieurs échéances de contrat, ou un ou
     * plusieurs frais de document) en attendant l'intégration d'un agrégateur de paiement
     * en ligne (CinetPay ou autre). Toujours créé en EN_ATTENTE : l'équipe GSF valide le
     * paiement une fois les fonds effectivement reçus, il n'y a pas d'encaissement
     * automatique depuis l'application. Le montant n'est jamais celui envoyé par le
     * client : il est toujours recalculé côté serveur à partir des éléments sélectionnés,
     * pour éviter tout écart entre l'affichage et ce qui est réellement dû.
     */
    public function store(Request $request)
    {
        $client = $request->user();

        $validated = $request->validate([
            'id_contrat'             => 'required|integer',
            'montant'                => 'nullable|numeric|min:1',
            'mode_paiement'          => 'required|in:ESPECES,VIREMENT,CHEQUE,MOBILE_MONEY',
            'date_paiement'          => 'required|date',
            'reference'              => 'nullable|string|max:100',
            'notes'                  => 'nullable|string|max:500',
            'id_contrat_document'    => 'nullable|integer',
            'id_contrat_documents'   => 'nullable|array',
            'id_contrat_documents.*' => 'integer',
            'id_echeances'           => 'nullable|array',
            'id_echeances.*'         => 'integer',
            'banque_paiement'        => 'nullable|string|max:50',
        ], [
            'mode_paiement.required' => 'Le mode de paiement est obligatoire.',
            'date_paiement.required' => 'La date du paiement est obligatoire.',
        ]);

        $contrat = Contrat::where('id_contrat', $validated['id_contrat'])
            ->where('valeursupprimee', 0)
            ->first();

        abort_unless($contrat, 404);
        abort_unless($contrat->id_client === $client->id, 403);

        if (empty($validated['banque_paiement'])) {
            if ($validated['mode_paiement'] === 'VIREMENT') {
                return response()->json(['message' => 'Veuillez indiquer la banque utilisée pour le virement.'], 422);
            }
            if ($validated['mode_paiement'] === 'MOBILE_MONEY') {
                return response()->json(['message' => 'Veuillez indiquer l\'opérateur Mobile Money utilisé.'], 422);
            }
        }

        $notes = $validated['notes'] ?? null;
        $noteOrigine = "Paiement effectué par le client depuis l'application mobile, en attente de vérification par l'équipe GSF."
            . ($notes ? " {$notes}" : '');

        $documentIds = $validated['id_contrat_documents'] ?? [];
        if (empty($documentIds) && !empty($validated['id_contrat_document'])) {
            $documentIds = [$validated['id_contrat_document']];
        }
        $echeanceIds = $validated['id_echeances'] ?? [];

        if (!empty($documentIds)) {
            $documentIds = array_values(array_unique($documentIds));

            $documents = ContratDocument::whereIn('id_contrat_document', $documentIds)
                ->where('id_contrat', $contrat->id_contrat)
                ->where('valeursupprimee', 0)
                ->get();

            if ($documents->count() !== count($documentIds)) {
                return response()->json(['message' => 'Un ou plusieurs documents sont introuvables pour ce contrat.'], 404);
            }
            if ($documents->contains(fn ($d) => $d->statut_paiement === 'paye')) {
                return response()->json(['message' => 'Un des documents sélectionnés est déjà entièrement payé.'], 422);
            }
            if ($documents->contains(fn ($d) => $d->reste_a_payer <= 0)) {
                return response()->json(['message' => 'Un des documents sélectionnés n\'a plus de montant restant à payer.'], 422);
            }

            $paiements = DB::transaction(function () use ($documents, $contrat, $validated, $noteOrigine) {
                return $documents->map(fn ($document) => Paiement::create([
                    'id_contrat'          => $contrat->id_contrat,
                    'id_contrat_document' => $document->id_contrat_document,
                    'date_paiement'       => $validated['date_paiement'],
                    'montant'             => $document->reste_a_payer,
                    'mode_paiement'       => $validated['mode_paiement'],
                    'reference'           => $validated['reference'] ?? null,
                    'banque_paiement'     => $validated['banque_paiement'] ?? null,
                    'notes'               => $noteOrigine,
                    'statut'              => 'EN_ATTENTE',
                    'type_paiement'       => 'FRAIS_DOCUMENT',
                    'valeursupprimee'     => 0,
                ]));
            });

            $total = $documents->sum('reste_a_payer');

            NotifHelper::notifyAdmins('Nouveau paiement effectué par un client', [
                '**Un client a effectué un paiement depuis l\'application mobile — à vérifier et valider dès réception des fonds.**',
                'Client : ' . $client->nom_prenoms,
                'Contrat : ' . $contrat->reference_contrat,
                'Documents : ' . $documents->pluck('document_type_nom')->filter()->implode(', '),
                'Montant total : ' . number_format($total, 0, ',', ' ') . ' FCFA',
                'Mode de paiement : ' . $validated['mode_paiement'] . ($paiements->first()->banque_paiement ? ' (' . $paiements->first()->banque_label . ')' : ''),
                'Référence : ' . ($validated['reference'] ?? '—'),
            ]);

            return response()->json([
                'success'   => true,
                'message'   => 'Paiement enregistré avec succès. Il sera vérifié et validé par notre équipe dès réception des fonds.',
                'paiements' => PaiementResource::collection($paiements->each->load('contrat.lot')),
            ], 201);
        }

        if (!empty($echeanceIds)) {
            if (!$contrat->peutRecevoirPaiement()) {
                $message = $contrat->statut === 'SOLDE'
                    ? 'Ce contrat est déjà soldé.'
                    : 'Ce contrat ne peut plus recevoir de paiement.';

                return response()->json(['message' => $message], 422);
            }

            $echeanceIds = array_values(array_unique($echeanceIds));

            $echeances = Echeance::whereIn('id', $echeanceIds)
                ->where('id_contrat', $contrat->id_contrat)
                ->get();

            if ($echeances->count() !== count($echeanceIds)) {
                return response()->json(['message' => 'Une ou plusieurs échéances sont introuvables pour ce contrat.'], 404);
            }
            if ($echeances->contains(fn ($e) => $e->statut === 'PAYE')) {
                return response()->json(['message' => 'Une des échéances sélectionnées est déjà payée.'], 422);
            }

            $montant = $echeances->sum(fn ($e) => $e->reste_a_payer);

            if ($montant <= 0) {
                return response()->json(['message' => 'Aucun montant restant sur les échéances sélectionnées.'], 422);
            }

            $paiement = Paiement::create([
                'id_contrat'      => $contrat->id_contrat,
                'date_paiement'   => $validated['date_paiement'],
                'montant'         => $montant,
                'mode_paiement'   => $validated['mode_paiement'],
                'reference'       => $validated['reference'] ?? null,
                'banque_paiement' => $validated['banque_paiement'] ?? null,
                'notes'           => $noteOrigine . ' Échéances concernées : '
                    . $echeances->sortBy('date_echeance')->pluck('date_echeance_formatted')->implode(', ') . '.',
                'statut'          => 'EN_ATTENTE',
                'type_paiement'   => $contrat->type_paiement,
                'valeursupprimee' => 0,
            ]);

            NotifHelper::notifyAdmins('Nouveau paiement effectué par un client', [
                '**Un client a effectué un paiement depuis l\'application mobile — à vérifier et valider dès réception des fonds.**',
                'Client : ' . $client->nom_prenoms,
                'Contrat : ' . $contrat->reference_contrat,
                'Échéances : ' . $echeances->sortBy('date_echeance')->pluck('date_echeance_formatted')->implode(', '),
                'Montant : ' . number_format($montant, 0, ',', ' ') . ' FCFA',
                'Mode de paiement : ' . $validated['mode_paiement'] . ($paiement->banque_paiement ? ' (' . $paiement->banque_label . ')' : ''),
                'Référence : ' . ($paiement->reference ?? '—'),
            ]);

            return response()->json([
                'success'  => true,
                'message'  => 'Paiement enregistré avec succès. Il sera vérifié et validé par notre équipe dès réception des fonds.',
                'paiement' => new PaiementResource($paiement->load('contrat.lot')),
            ], 201);
        }

        if (empty($validated['montant'])) {
            return response()->json(['message' => 'Le montant est obligatoire.'], 422);
        }

        if (!$contrat->peutRecevoirPaiement()) {
            $message = $contrat->statut === 'SOLDE'
                ? 'Ce contrat est déjà soldé.'
                : 'Ce contrat ne peut plus recevoir de paiement.';

            return response()->json(['message' => $message], 422);
        }

        $resteAPayer = $contrat->reste_a_payer_calcule;

        if ($validated['montant'] > $resteAPayer) {
            return response()->json([
                'message' => 'Le montant dépasse le reste à payer (' . number_format($resteAPayer, 0, ',', ' ') . ' FCFA).',
            ], 422);
        }

        $paiement = Paiement::create([
            'id_contrat'      => $contrat->id_contrat,
            'date_paiement'   => $validated['date_paiement'],
            'montant'         => $validated['montant'],
            'mode_paiement'   => $validated['mode_paiement'],
            'reference'       => $validated['reference'] ?? null,
            'banque_paiement' => $validated['banque_paiement'] ?? null,
            'notes'           => $noteOrigine,
            'statut'          => 'EN_ATTENTE',
            'type_paiement'   => $contrat->type_paiement,
            'valeursupprimee' => 0,
        ]);

        NotifHelper::notifyAdmins('Nouveau paiement effectué par un client', [
            '**Un client a effectué un paiement depuis l\'application mobile — à vérifier et valider dès réception des fonds.**',
            'Client : ' . $client->nom_prenoms,
            'Contrat : ' . $contrat->reference_contrat,
            'Montant : ' . number_format($validated['montant'], 0, ',', ' ') . ' FCFA',
            'Mode de paiement : ' . $validated['mode_paiement'] . ($paiement->banque_paiement ? ' (' . $paiement->banque_label . ')' : ''),
            'Référence : ' . ($paiement->reference ?? '—'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Paiement enregistré avec succès. Il sera vérifié et validé par notre équipe dès réception des fonds.',
            'paiement' => new PaiementResource($paiement->load('contrat.lot')),
        ], 201);
    }

    /**
     * Détail d'un paiement du client (lecture seule).
     */
    public function show(Request $request, $id)
    {
        $paiement = Paiement::where('id_paiement', $id)
            ->where(function ($q) {
                $q->where('valeursupprimee', '!=', 1)->orWhereNull('valeursupprimee');
            })
            ->with(['contrat.lot', 'contratDocument'])
            ->firstOrFail();

        abort_unless($paiement->contrat && $paiement->contrat->id_client === $request->user()->id, 403);

        return new PaiementResource($paiement);
    }

    public function recu(Request $request, $id)
    {
        $paiement = Paiement::where('id_paiement', $id)
            ->where(function ($q) {
                $q->where('valeursupprimee', '!=', 1)->orWhereNull('valeursupprimee');
            })
            ->with('contrat')
            ->firstOrFail();

        abort_unless($paiement->contrat && $paiement->contrat->id_client === $request->user()->id, 403);

        $url = URL::temporarySignedRoute('client.paiements.recu.signed', now()->addMinutes(10), ['id' => $id]);

        return response()->json(['signed_url' => $url]);
    }
}
