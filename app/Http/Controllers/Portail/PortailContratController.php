<?php

namespace App\Http\Controllers\Portail;

use App\Http\Controllers\Controller;
use App\Models\Contrat;

class PortailContratController extends Controller
{
    public function index()
    {
        $client = auth('clients')->user();

        $contrats = $client->contrats()->with(['lot.lotissement'])->get();

        return view('portail.contrats.index', compact('contrats'));
    }

    public function show($id)
    {
        $contrat = Contrat::where('id_contrat', $id)
            ->where('valeursupprimee', 0)
            ->with(['lot.lotissement', 'paiements', 'echeances', 'contratDocuments.documentType'])
            ->firstOrFail();

        abort_unless($contrat->id_client === auth('clients')->id(), 403);

        return view('portail.contrats.show', compact('contrat'));
    }

    public function etat($id)
    {
        $contrat = Contrat::where('id_contrat', $id)
            ->where('valeursupprimee', 0)
            ->with(['client', 'lot.lotissement', 'paiements' => function ($q) {
                $q->where('statut', 'VALIDE')->orderBy('date_paiement');
            }, 'echeances', 'contratDocuments.documentType'])
            ->firstOrFail();

        abort_unless($contrat->id_client === auth('clients')->id(), 403);

        return view('portail.contrats.etat', compact('contrat'));
    }
}
