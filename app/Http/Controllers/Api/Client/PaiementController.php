<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Paiement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class PaiementController extends Controller
{
    public function recu(Request $request, $id)
    {
        $paiement = Paiement::where('id_paiement', $id)
            ->where(function ($q) {
                $q->where('valeursupprimee', '!=', 1)->orWhereNull('valeursupprimee');
            })
            ->with('contrat')
            ->firstOrFail();

        abort_unless($paiement->contrat && $paiement->contrat->id_client === $request->user()->id, 403);

        $url = URL::temporarySignedRoute('client.paiements.recu.signed', now()->addMinutes(10), ['id' => $id]);

        return response()->json(['signed_url' => $url]);
    }
}
