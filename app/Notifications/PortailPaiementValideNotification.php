<?php

namespace App\Notifications;

use App\Models\Paiement;
use Illuminate\Notifications\Notification;

class PortailPaiementValideNotification extends Notification
{
    public function __construct(public Paiement $paiement) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'paiement_valide',
            'titre' => 'Paiement enregistré',
            'message' => "Votre paiement de {$this->paiement->montant_formatted} pour le contrat {$this->paiement->contrat->reference_contrat} a été validé.",
            'contrat_id' => $this->paiement->contrat->id_contrat,
            'paiement_id' => $this->paiement->id_paiement,
        ];
    }
}
