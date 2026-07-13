<?php

namespace App\Http\Controllers\Portail;

use App\Http\Controllers\Controller;
use App\Models\ContratDocument;
use Illuminate\Support\Facades\Storage;

class PortailDocumentController extends Controller
{
    public function download($id)
    {
        $document = ContratDocument::where('id_contrat_document', $id)
            ->where('valeursupprimee', 0)
            ->with('contrat')
            ->firstOrFail();

        abort_unless($document->contrat && $document->contrat->id_client === auth('clients')->id(), 403);
        abort_unless($document->fichier_path, 404);

        return Storage::disk('public')->response($document->fichier_path);
    }
}
