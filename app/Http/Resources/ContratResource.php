<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContratResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_contrat,
            'reference' => $this->reference_contrat,
            'statut' => $this->statut,
            'statut_label' => $this->statut_label,
            'type_paiement' => $this->type_paiement,
            'type_paiement_label' => $this->type_paiement_label,
            'date_contrat_formatted' => $this->date_contrat_formatted,
            'montant_total' => (float) $this->montant_total,
            'montant_total_formatted' => $this->montant_total_formatted,
            'montant_paye' => (float) $this->montant_paye,
            'montant_paye_formatted' => $this->montant_paye_formatted,
            'reste_a_payer' => (float) $this->reste_a_payer_calcule,
            'reste_a_payer_formatted' => $this->reste_a_payer_formatted,
            'progression_paiement' => $this->progression_paiement,
            'lot' => $this->whenLoaded('lot', function () {
                return [
                    'numero_lot' => $this->lot->numero_lot,
                    'numero_ilot' => $this->lot->numero_ilot,
                    'lotissement_nom' => $this->lot->lotissement_nom,
                    'ville_nom' => $this->lot->ville_nom,
                ];
            }),
            'paiements' => PaiementResource::collection($this->whenLoaded('paiements', function () {
                return $this->paiements->where('statut', 'VALIDE')->sortByDesc('date_paiement')->values();
            })),
            'documents' => ContratDocumentResource::collection($this->whenLoaded('contratDocuments')),
            'echeances' => EcheanceResource::collection($this->whenLoaded('echeances', function () {
                return $this->echeances->sortBy('date_echeance')->values();
            })),
        ];
    }
}
