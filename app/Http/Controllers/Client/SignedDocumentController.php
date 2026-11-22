<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Contrat;
use App\Models\ContratDocument;
use App\Models\Paiement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

/**
 * Sert les documents PDF-imprimables (état de contrat, reçu, pièce jointe)
 * pour l'app mobile, via lien signé Laravel plutôt que session — l'app
 * mobile s'authentifie par jeton Sanctum et n'a pas de cookie de session.
 * La propriété du document a déjà été vérifiée côté API au moment de la
 * génération du lien ; la sécurité de ces routes repose sur la signature
 * + son expiration courte (10 minutes).
 */
class SignedDocumentController extends Controller
{
    public function etatContrat($id)
    {
        $contrat = Contrat::where('id_contrat', $id)
            ->where('valeursupprimee', 0)
            ->with(['client', 'lot.lotissement', 'paiements' => function ($q) {
                $q->where('statut', 'VALIDE')->orderBy('date_paiement');
            }, 'echeances', 'contratDocuments.documentType'])
            ->firstOrFail();

        return Pdf::loadView('pdf.contrat-etat', compact('contrat'))
            ->download('etat-contrat-' . str_replace(['/', '\\'], '-', $contrat->reference_contrat) . '.pdf');
    }

    public function recuPaiement($id)
    {
        $paiement = Paiement::where('id_paiement', $id)
            ->where(function ($q) {
                $q->where('valeursupprimee', '!=', 1)->orWhereNull('valeursupprimee');
            })
            ->with(['contrat.client', 'contrat.lot'])
            ->firstOrFail();

        return Pdf::loadView('pdf.paiement-recu', compact('paiement'))
            ->download('recu-' . str_replace(['/', '\\'], '-', $paiement->reference) . '.pdf');
    }

    public function telechargerDocument($id)
    {
        $document = ContratDocument::where('id_contrat_document', $id)
            ->where('valeursupprimee', 0)
            ->firstOrFail();

        abort_unless($document->fichier_path, 404);

        return Storage::disk('public')->response($document->fichier_path);
    }
}
