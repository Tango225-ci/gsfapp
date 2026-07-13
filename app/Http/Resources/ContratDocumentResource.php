<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContratDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_contrat_document,
            'nom' => $this->document_type_nom,
            'montant_formatted' => $this->montant_formatted,
            'statut_paiement' => $this->statut_paiement,
            'statut_paiement_label' => $this->statut_paiement_label,
            'statut_document' => $this->statut_document,
            'statut_document_label' => $this->statut_document_label,
            'telechargeable' => filled($this->fichier_path),
        ];
    }
}
