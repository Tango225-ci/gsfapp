<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\DocumentType;
use App\Models\VilleDocumentFrais;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DocumentConfigurationController extends Controller
{
    /**
     * Afficher la page de configuration des documents ACD
     */
    public function index()
    {
        // Correction : utiliser le bon chemin de vue
        // Essaye plusieurs chemins possibles
        if (view()->exists('pages.document.document')) {
            return view('pages.document.document');
        } elseif (view()->exists('pages.documents.document')) {
            return view('pages.documents.document');
        } elseif (view()->exists('documents.index')) {
            return view('documents.index');
        } elseif (view()->exists('pages.documents-config')) {
            return view('pages.documents-config');
        }
        
        // Vue par défaut si aucune n'existe
        return view('documents.index');
    }

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
        PermissionHelper::abort403('documents', 'manage');

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
            $validated['valeursupprimee'] = 0;

            $documentType = DocumentType::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Type de document créé avec succès',
                'document_type' => $documentType
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
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
        PermissionHelper::abort403('documents', 'manage');

        try {
            // Log pour debuguer
            Log::info('Tentative de mise à jour du type ID: ' . $id);
            Log::info('Données reçues: ', $request->all());
            
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
                'message' => 'Type de document modifié avec succès',  // Message corrigé
                'document_type' => $documentType
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Type de document introuvable'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
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

            // Vérifier si des documents de contrat existent pour ce type
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

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Type de document introuvable'
            ], 404);
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
    public function getFraisParVille(Request $request)
    {
        try {
            $query = VilleDocumentFrais::with('documentType')
                                       ->where('valeursupprimee', 0);
            
            // Filtrer par ville si spécifiée
            if ($request->has('ville') && !empty($request->ville)) {
                $query->where('ville', $request->ville);
            }
            
            $frais = $query->orderBy('ville', 'asc')
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
        PermissionHelper::abort403('documents', 'manage');

        try {
            $validated = $request->validate([
                'ville' => 'required|string|max:150',
                'id_document_type' => 'required|exists:document_types,id_document_type',
                'montant' => 'required|numeric|min:0'
            ]);

            // Vérifier si le tarif existe déjà
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
                'frais' => $frais->load('documentType')
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
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

            // Vérifier si des documents de contrat utilisent ce tarif
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

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Tarif introuvable'
            ], 404);
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
            // Récupérer les villes depuis les lotissements existants
            $lotissements = \App\Models\Lotissement::where('valeursupprimee', '!=', 1)
                                                   ->orWhereNull('valeursupprimee')
                                                   ->select('ville')
                                                   ->distinct()
                                                   ->whereNotNull('ville')
                                                   ->where('ville', '!=', '')
                                                   ->orderBy('ville', 'asc')
                                                   ->get();
            
            $villes = $lotissements->pluck('ville')->toArray();
            
            // Ajouter aussi les villes qui ont déjà des tarifs configurés
            $fraisVilles = VilleDocumentFrais::where('valeursupprimee', 0)
                                             ->select('ville')
                                             ->distinct()
                                             ->pluck('ville')
                                             ->toArray();
            
            $allVilles = array_unique(array_merge($villes, $fraisVilles));
            sort($allVilles);

            return response()->json([
                'success' => true,
                'villes' => $allVilles
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

    /**
     * Récupérer toutes les villes avec leurs tarifs (pour tableau croisé)
     */
    public function getMatriceFrais()
    {
        try {
            // Récupérer tous les types de documents actifs
            $documentTypes = DocumentType::where('actif', 1)
                                         ->where('valeursupprimee', 0)
                                         ->orderBy('ordre_affichage', 'asc')
                                         ->get();
            
            // Récupérer toutes les villes
            $villes = $this->getVilles()->getData(true);
            $villesListe = $villes['villes'] ?? [];
            
            // Récupérer tous les frais
            $frais = VilleDocumentFrais::where('valeursupprimee', 0)
                                       ->get()
                                       ->keyBy(function($item) {
                                           return $item->ville . '_' . $item->id_document_type;
                                       });
            
            // Construire la matrice
            $matrice = [];
            foreach ($villesListe as $ville) {
                $matrice[$ville] = [];
                foreach ($documentTypes as $docType) {
                    $key = $ville . '_' . $docType->id_document_type;
                    $matrice[$ville][$docType->id_document_type] = [
                        'id_frais' => $frais[$key]->id_frais ?? null,
                        'montant' => $frais[$key]->montant ?? 0,
                        'existe' => isset($frais[$key])
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'document_types' => $documentTypes,
                'villes' => $villesListe,
                'matrice' => $matrice
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur récupération matrice frais: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }
}