<?php

namespace App\Http\Controllers;

use App\Models\ContratDocument;
use App\Models\Contrat;
use App\Models\DocumentType;
use App\Models\VilleDocumentFrais;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContratDocumentController extends Controller
{
    /**
     * Récupérer tous les documents d'un contrat spécifique
     */
    public function index($idContrat)
    {
        try {
            $contrat = Contrat::where('id_contrat', $idContrat)
                              ->where('valeursupprimee', '!=', 1)
                              ->first();

            if (!$contrat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contrat introuvable'
                ], 404);
            }

            $documents = ContratDocument::with(['documentType', 'frais'])
                                        ->where('id_contrat', $idContrat)
                                        ->where('valeursupprimee', 0)
                                        ->orderBy('id_document_type', 'asc')
                                        ->get();

            return response()->json([
                'success' => true,
                'documents' => $documents,
                'resume' => [
                    'total_documents' => $documents->count(),
                    'documents_payes' => $documents->where('statut_paiement', 'paye')->count(),
                    'documents_disponibles' => $documents->where('statut_document', 'disponible')->count(),
                    'documents_remis' => $documents->where('statut_document', 'remis')->count(),
                    'montant_total_frais' => $documents->sum('montant'),
                    'montant_total_paye' => $documents->where('statut_paiement', 'paye')->sum('montant'),
                    'tous_obligatoires_acceptes' => $contrat->tous_documents_acceptes
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur récupération documents contrat: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer un document spécifique
     */
    public function show($id)
    {
        try {
            $document = ContratDocument::with(['contrat', 'documentType', 'frais'])
                                       ->where('id_contrat_document', $id)
                                       ->where('valeursupprimee', 0)
                                       ->firstOrFail();

            return response()->json([
                'success' => true,
                'document' => $document
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur récupération document: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Document introuvable'
            ], 404);
        }
    }

    /**
     * Marquer un document comme disponible
     */
    public function marquerDisponible($id)
    {
        try {
            DB::beginTransaction();

            $document = ContratDocument::where('id_contrat_document', $id)
                                       ->where('valeursupprimee', 0)
                                       ->firstOrFail();

            if ($document->statut_document === 'remis') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce document a déjà été remis au client.'
                ], 422);
            }

            $document->marquerDisponible();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Document marqué comme disponible',
                'document' => $document
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur marquage disponible: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marquer un document comme remis au client
     */
    public function marquerRemis($id)
    {
        try {
            DB::beginTransaction();

            $document = ContratDocument::where('id_contrat_document', $id)
                                       ->where('valeursupprimee', 0)
                                       ->firstOrFail();

            if ($document->statut_document === 'remis') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce document a déjà été remis au client.'
                ], 422);
            }

            // Si le document n'était pas encore disponible, le marquer d'abord comme disponible
            if ($document->statut_document !== 'disponible') {
                $document->marquerDisponible();
                $document->refresh();
            }

            $document->marquerRemis();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Document marqué comme remis au client',
                'document' => $document
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur marquage remis: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload d'un fichier pour le document
     */
    public function uploadFichier(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'fichier' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120'
            ]);

            $document = ContratDocument::where('id_contrat_document', $id)
                                       ->where('valeursupprimee', 0)
                                       ->firstOrFail();

            if ($request->hasFile('fichier')) {
                $fichier = $request->file('fichier');
                $fileName = time() . '_' . $document->id_contrat . '_' . $document->id_document_type . '.' . $fichier->getClientOriginalExtension();
                $filePath = $fichier->storeAs('contrat_documents', $fileName, 'public');

                $document->fichier_path = $filePath;
                $document->save();

                Log::info('Fichier uploadé pour le document', [
                    'document_id' => $document->id_contrat_document,
                    'fichier_path' => $filePath
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Fichier uploadé avec succès',
                'fichier_path' => $document->fichier_path
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur upload fichier: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un document (soft delete)
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $document = ContratDocument::where('id_contrat_document', $id)
                                       ->where('valeursupprimee', 0)
                                       ->firstOrFail();

            $hasPaiements = $document->paiements()->count() > 0;

            if ($hasPaiements) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce document a des paiements associés. Utilisez la suppression administrateur.'
                ], 403);
            }

            $document->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Document supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur suppression document: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Générer les documents manquants pour un contrat
     */
    public function genererManquants($idContrat)
    {
        try {
            DB::beginTransaction();

            $contrat = Contrat::where('id_contrat', $idContrat)
                              ->where('valeursupprimee', '!=', 1)
                              ->firstOrFail();

            $existingDocTypes = ContratDocument::where('id_contrat', $idContrat)
                                               ->where('valeursupprimee', 0)
                                               ->pluck('id_document_type')
                                               ->toArray();

            $allDocTypes = DocumentType::where('actif', 1)
                                       ->where('valeursupprimee', 0)
                                       ->pluck('id_document_type')
                                       ->toArray();

            $missingDocTypes = array_diff($allDocTypes, $existingDocTypes);

            if (empty($missingDocTypes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tous les documents sont déjà générés pour ce contrat.'
                ], 422);
            }

            $lot = $contrat->lot;
            if (!$lot || !$lot->lotissement) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de récupérer la ville du lot.'
                ], 422);
            }

            $ville = $lot->lotissement->ville;
            $compteur = 0;

            foreach ($missingDocTypes as $docTypeId) {
                $docType = DocumentType::find($docTypeId);
                if (!$docType) continue;

                $frais = VilleDocumentFrais::where('ville', $ville)
                                           ->where('id_document_type', $docTypeId)
                                           ->where('valeursupprimee', 0)
                                           ->first();

                $montant = $frais ? $frais->montant : 0;
                $idFrais = $frais ? $frais->id_frais : null;

                ContratDocument::create([
                    'id_contrat' => $idContrat,
                    'id_lot' => $contrat->id_lot,
                    'id_document_type' => $docTypeId,
                    'id_frais' => $idFrais,
                    'montant' => $montant,
                    'statut_paiement' => 'non_paye',
                    'statut_document' => 'en_attente',
                    'valeursupprimee' => 0
                ]);
                $compteur++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $compteur . ' document(s) manquant(s) généré(s) avec succès',
                'nombre_generes' => $compteur
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur génération documents manquants: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }
}
