<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->data;

        return [
            'id' => $this->id,
            'type' => $data['type'] ?? null,
            'titre' => $data['titre'] ?? 'Notification',
            'message' => $data['message'] ?? '',
            'contrat_id' => $data['contrat_id'] ?? null,
            'lu' => ! is_null($this->read_at),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
