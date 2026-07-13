<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference_display,
            'nom_prenoms' => $this->nom_prenoms,
            'contact' => $this->contact,
            'email' => $this->email,
            'adresse' => $this->adresse,
            'statut' => $this->statut,
            'must_change_password' => (bool) $this->must_change_password,
        ];
    }
}
