<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaiementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_paiement,
            'reference' => $this->reference,
            'date_paiement_formatted' => $this->date_paiement_formatted,
            'montant' => (float) $this->montant,
            'montant_formatted' => $this->montant_formatted,
            'mode_paiement_label' => $this->mode_paiement_label,
            'banque_label' => $this->banque_label,
            'type_paiement_label' => $this->type_paiement_label,
            'statut' => $this->statut,
            'statut_label' => $this->statut_label,
        ];
    }
}
