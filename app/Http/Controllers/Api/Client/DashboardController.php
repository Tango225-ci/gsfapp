<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContratResource;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $client = $request->user();

        $contrats = $client->contrats()->with(['lot.lotissement'])->get();

        $totalDu = $contrats->sum('montant_total');
        $totalPaye = $contrats->sum('montant_paye');
        $totalReste = $contrats->sum('reste_a_payer_calcule');
        $progressionGlobale = $totalDu > 0 ? min(100, round($totalPaye / $totalDu * 100)) : 0;

        return response()->json([
            'resume' => [
                'total_du' => (float) $totalDu,
                'total_paye' => (float) $totalPaye,
                'total_reste' => (float) $totalReste,
                'progression' => $progressionGlobale,
                'solde' => $progressionGlobale >= 100,
            ],
            'contrats' => ContratResource::collection($contrats),
        ]);
    }
}
