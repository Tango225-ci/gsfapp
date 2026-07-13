<?php

namespace App\Http\Controllers\Portail;

use App\Http\Controllers\Controller;
use App\Models\Paiement;

class PortailPaiementController extends Controller
{
    public function recu($id)
    {
        $paiement = Paiement::where('id_paiement', $id)
            ->where(function ($q) {
                $q->where('valeursupprimee', '!=', 1)->orWhereNull('valeursupprimee');
            })
            ->with(['contrat.client', 'contrat.lot'])
            ->firstOrFail();

        abort_unless($paiement->contrat && $paiement->contrat->id_client === auth('clients')->id(), 403);

        return view('portail.paiements.recu', compact('paiement'));
    }
}
