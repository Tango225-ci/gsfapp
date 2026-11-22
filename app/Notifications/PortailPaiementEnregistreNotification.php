<?php

namespace App\Notifications;

use App\Models\Paiement;
use Illuminate\Notifications\Notification;

class PortailPaiementEnregistreNotification extends Notification
{
    public function __construct(public Paiement $paiement) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'paiement_enregistre',
            'titre' => 'Paiement enregistré',
            'message' => "Votre paiement de {$this->paiement->montant_formatted} pour le contrat {$this->paiement->contrat->reference_contrat} a été enregistré et est en attente de validation.",
            'contrat_id' => $this->paiement->contrat->id_contrat,
            'paiement_id' => $this->paiement->id_paiement,
        ];
    }
}
