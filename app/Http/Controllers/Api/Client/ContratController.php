<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContratResource;
use App\Models\Contrat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class ContratController extends Controller
{
    public function index(Request $request)
    {
        $client = $request->user();

        $contrats = $client->contrats()->with(['lot.lotissement'])->get();

        return ContratResource::collection($contrats);
    }

    public function show(Request $request, $id)
    {
        $contrat = Contrat::where('id_contrat', $id)
            ->where('valeursupprimee', 0)
            ->with(['lot.lotissement', 'paiements', 'echeances', 'contratDocuments.documentType'])
            ->firstOrFail();

        abort_unless($contrat->id_client === $request->user()->id, 403);

        return new ContratResource($contrat);
    }

    public function etat(Request $request, $id)
    {
        $contrat = Contrat::where('id_contrat', $id)
            ->where('valeursupprimee', 0)
            ->firstOrFail();

        abort_unless($contrat->id_client === $request->user()->id, 403);

        $url = URL::temporarySignedRoute('client.contrats.etat.signed', now()->addMinutes(10), ['id' => $id]);

        return response()->json(['signed_url' => $url]);
    }
}
