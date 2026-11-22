<?php

namespace App\Console\Commands;

use App\Jobs\SendClientSmsJob;
use App\Models\Echeance;
use App\Notifications\PortailEcheanceEnRetardNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RelancerEcheancesEnRetard extends Command
{
    /**
     * Nombre minimum de jours entre deux relances pour une même échéance,
     * pour ne pas harceler le client.
     */
    private const RELANCE_INTERVALLE_JOURS = 3;

    protected $signature = 'gsf:relancer-echeances';

    protected $description = "Relance (SMS + notification) les clients dont une échéance est en retard de paiement";

    public function handle(): int
    {
        $echeances = Echeance::where(function ($q) {
                $q->where('valeursupprimee', '!=', 1)->orWhereNull('valeursupprimee');
            })
            ->whereColumn('montant_paye', '<', 'montant_prevu')
            ->where('date_echeance', '<', now()->toDateString())
            ->with('contrat.client')
            ->get();

        $envoyees = 0;
        $ignorees = 0;

        foreach ($echeances as $echeance) {
            // Le statut stocké peut être resté "A_PAYER" si l'échéance n'a pas
            // été re-sauvegardée depuis qu'elle est passée en retard : on le
            // recalcule en mémoire à partir de la date/du montant réels.
            $echeance->updateStatutAutomatique();

            if ($echeance->statut !== 'EN_RETARD') {
                continue;
            }

            if (
                $echeance->derniere_relance_at
                && $echeance->derniere_relance_at->gt(now()->subDays(self::RELANCE_INTERVALLE_JOURS))
            ) {
                $ignorees++;
                continue;
            }

            $client = $echeance->contrat->client ?? null;

            if (!$client) {
                continue;
            }

            $message = "GSF : votre échéance du {$echeance->date_echeance_formatted} "
                . "d'un montant de {$echeance->montant_prevu_formatted} est en retard de paiement. "
                . "Merci de régulariser votre situation.";

            try {
                if ($client->contact) {
                    SendClientSmsJob::dispatch($client->contact, $message);
                }

                $client->notify(new PortailEcheanceEnRetardNotification($echeance));

                $echeance->derniere_relance_at = now();
                $echeance->save();

                $envoyees++;
            } catch (\Throwable $e) {
                Log::error('Échec envoi de la relance échéance', [
                    'echeance_id' => $echeance->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("{$envoyees} relance(s) envoyée(s), {$ignorees} déjà relancée(s) récemment.");

        return self::SUCCESS;
    }
}
