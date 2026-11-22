<?php

namespace App\Notifications;

use App\Models\Echeance;
use Illuminate\Notifications\Notification;

class PortailEcheanceEnRetardNotification extends Notification
{
    public function __construct(public Echeance $echeance) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'echeance_en_retard',
            'titre' => 'Échéance en retard',
            'message' => "Votre échéance du {$this->echeance->date_echeance_formatted} d'un montant de {$this->echeance->montant_prevu_formatted} est en retard de paiement.",
            'contrat_id' => $this->echeance->contrat->id_contrat,
            'echeance_id' => $this->echeance->id,
        ];
    }
}
