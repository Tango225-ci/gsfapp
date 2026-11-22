<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EcheanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numero' => $this->numero_echeance,
            'date_echeance_formatted' => $this->date_echeance_formatted,
            'montant_prevu_formatted' => $this->montant_prevu_formatted,
            'montant_paye_formatted' => $this->montant_paye_formatted,
            'reste_a_payer' => (float) $this->reste_a_payer,
            'reste_a_payer_formatted' => $this->reste_a_payer_formatted,
            'statut' => $this->statut,
            'statut_label' => $this->statut_label,
            'progression_paiement' => $this->progression_paiement,
        ];
    }
}
