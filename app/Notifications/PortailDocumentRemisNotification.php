<?php

namespace App\Notifications;

use App\Models\ContratDocument;
use Illuminate\Notifications\Notification;

class PortailDocumentRemisNotification extends Notification
{
    public function __construct(public ContratDocument $document) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $nomDocument = $this->document->document_type_nom ?? 'Document';

        return [
            'type' => 'document_remis',
            'titre' => 'Document remis',
            'message' => "Votre document « {$nomDocument} » vous a été remis pour le contrat {$this->document->contrat->reference_contrat}.",
            'contrat_id' => $this->document->contrat->id_contrat,
            'document_id' => $this->document->id_contrat_document,
        ];
    }
}
