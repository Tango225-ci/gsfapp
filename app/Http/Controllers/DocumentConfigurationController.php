<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use App\Models\VilleDocumentFrais;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DocumentConfigurationController extends Controller
{
    /**
     * Récupérer tous les types de documents
     */
    public function getDocumentTypes()
    {
        try {
            $documentTypes = DocumentType::with('fraisParVille')
                                         ->where('valeursupprimee', 0)
                                         ->orderBy('ordre_affichage', 'asc')
                                         ->get();

            return response()->json([
                'success' => true,
                'document_types' => $documentTypes
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur récupération types documents: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer un nouveau type de document
     */
    public function storeDocumentType(Request $request)
    {
        try {
            $validated = $request->validate([
                'nom' => 'required|string|max:100|unique:document_types,nom',
                'code' => 'required|string|max:50|unique:document_types,code',
                'description' => 'nullable|string',
                'ordre_affichage' => 'nullable|integer|min:0',
                'est_obligatoire' => 'boolean',
                'actif' => 'boolean'
            ]);

            $validated['ordre_affichage'] = $validated['ordre_affichage'] ?? 0;
            $validated['est_obligatoire'] = $validated['est_obligatoire'] ?? true;
            $validated['actif'] = $validated['actif'] ?? true;

            $documentType = DocumentType::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Type de document créé avec succès',
                'document_type' => $documentType
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erreur création type document: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Modifier un type de document
     */
    public function updateDocumentType(Request $request, $id)
    {
        try {
            $documentType = DocumentType::where('id_document_type', $id)
                                        ->where('valeursupprimee', 0)
                                        ->firstOrFail();

            $validated = $request->validate([
                'nom' => 'required|string|max:100|unique:document_types,nom,' . $id . ',id_document_type',
                'code' => 'required|string|max:50|unique:document_types,code,' . $id . ',id_document_type',
                'description' => 'nullable|string',
                'ordre_affichage' => 'nullable|integer|min:0',
                'est_obligatoire' => 'boolean',
                'actif' => 'boolean'
            ]);

            $documentType->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Type de document modifié avec succès',
                'document_type' => $documentType
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur modification type document: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un type de document (soft delete)
     */
    public function deleteDocumentType($id)
    {
        try {
            DB::beginTransaction();

            $documentType = DocumentType::where('id_document_type', $id)
                                        ->where('valeursupprimee', 0)
                                        ->firstOrFail();

            $contratDocumentsCount = $documentType->contratsDocuments()->count();
            if ($contratDocumentsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce type de document est déjà utilisé dans des contrats et ne peut pas être supprimé.'
                ], 403);
            }

            $documentType->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Type de document supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur suppression type document: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer tous les frais par ville
     */
    public function getFraisParVille()
    {
        try {
            $frais = VilleDocumentFrais::with('documentType')
                                       ->where('valeursupprimee', 0)
                                       ->orderBy('ville', 'asc')
                                       ->orderBy('id_document_type', 'asc')
                                       ->get();

            return response()->json([
                'success' => true,
                'frais' => $frais
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur récupération frais: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer ou mettre à jour un tarif pour une ville et un type de document
     */
    public function storeFrais(Request $request)
    {
        try {
            $validated = $request->validate([
                'ville' => 'required|string|max:150',
                'id_document_type' => 'required|exists:document_types,id_document_type',
                'montant' => 'required|numeric|min:0'
            ]);

            $existing = VilleDocumentFrais::where('ville', $validated['ville'])
                                          ->where('id_document_type', $validated['id_document_type'])
                                          ->where('valeursupprimee', 0)
                                          ->first();

            if ($existing) {
                $existing->update(['montant' => $validated['montant']]);
                $message = 'Tarif mis à jour avec succès';
                $frais = $existing;
            } else {
                $frais = VilleDocumentFrais::create($validated);
                $message = 'Tarif créé avec succès';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'frais' => $frais
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erreur création tarif: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un tarif (soft delete)
     */
    public function deleteFrais($id)
    {
        try {
            DB::beginTransaction();

            $frais = VilleDocumentFrais::where('id_frais', $id)
                                       ->where('valeursupprimee', 0)
                                       ->firstOrFail();

            $documentsCount = $frais->contratsDocuments()->count();
            if ($documentsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce tarif est déjà utilisé dans des contrats et ne peut pas être supprimé.'
                ], 403);
            }

            $frais->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tarif supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur suppression tarif: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les villes distinctes pour la configuration
     */
    public function getVilles()
    {
        try {
            $villes = VilleDocumentFrais::where('valeursupprimee', 0)
                                        ->select('ville')
                                        ->distinct()
                                        ->orderBy('ville', 'asc')
                                        ->pluck('ville');

            return response()->json([
                'success' => true,
                'villes' => $villes
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur récupération villes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les frais pour un type de document spécifique
     */
    public function getFraisByDocumentType($idDocumentType)
    {
        try {
            $frais = VilleDocumentFrais::with('documentType')
                                       ->where('id_document_type', $idDocumentType)
                                       ->where('valeursupprimee', 0)
                                       ->orderBy('ville', 'asc')
                                       ->get();

            return response()->json([
                'success' => true,
                'frais' => $frais
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur récupération frais par type: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }
}
