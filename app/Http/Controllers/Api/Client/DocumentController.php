<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\ContratDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class DocumentController extends Controller
{
    public function downloadUrl(Request $request, $id)
    {
        $document = ContratDocument::where('id_contrat_document', $id)
            ->where('valeursupprimee', 0)
            ->with('contrat')
            ->firstOrFail();

        abort_unless($document->contrat && $document->contrat->id_client === $request->user()->id, 403);
        abort_unless($document->fichier_path, 404);

        $url = URL::temporarySignedRoute('client.documents.telecharger.signed', now()->addMinutes(10), ['id' => $id]);

        return response()->json(['signed_url' => $url]);
    }
}
