<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\ProprietaireTerrien;
use App\Models\VersementProprietaire;
use App\Models\Lot;
use App\Models\Paiement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProprietaireTerrienController extends Controller
{
    // ── Page web ─────────────────────────────────────────────────────────────

    public function index()
    {
        PermissionHelper::abort403('utilisateurs', 'view'); // ADMIN seulement
        return view('pages.proprietaires.proprietaire', ['title' => 'Gestion des Propriétaires']);
    }

    // ── API : liste ───────────────────────────────────────────────────────────

    public function apiIndex()
    {
        PermissionHelper::abort403('utilisateurs', 'view');

        try {
            $proprietaires = ProprietaireTerrien::notDeleted()
                ->orderBy('nom')
                ->get();

            return response()->json($proprietaires);
        } catch (\Exception $e) {
            Log::error('Erreur chargement propriétaires: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur chargement'], 500);
        }
    }

    // ── API : détail + stats ──────────────────────────────────────────────────

    public function apiShow($id)
    {
        PermissionHelper::abort403('utilisateurs', 'view');

        try {
            $p = ProprietaireTerrien::notDeleted()->findOrFail($id);

            $lots = Lot::with('lotissement')
                ->where('id_proprietaire', $id)
                ->where(function ($q) {
                    $q->where('valeursupprimee', '!=', 1)->orWhereNull('valeursupprimee');
                })
                ->orderBy('numero_lot')
                ->get();

            $versements = VersementProprietaire::where('id_proprietaire', $id)
                ->orderBy('date_versement', 'desc')
                ->get();

            $lotIds         = $lots->pluck('id_lot');
            $totalPrixAchat = $lots->sum('prix_achat');
            $totalVerse     = $versements->sum('montant');
            $parStatut      = $lots->groupBy('statut')->map->count();

            // Marge théorique = Σ(prix_vente_affiché − prix_achat) sur tous les lots
            $gainTheorique = $lots->sum(function ($lot) {
                $prixVente = max(floatval($lot->prix_comptant ?? 0), floatval($lot->prix_echelonne ?? 0));
                return $prixVente - floatval($lot->prix_achat ?? 0);
            });

            $totalPrixVenteTheorique = $lots->sum(function ($lot) {
                return max(floatval($lot->prix_comptant ?? 0), floatval($lot->prix_echelonne ?? 0));
            });

            // Encaissements clients = paiements VALIDÉS sur les contrats des lots de ce proprio
            $encaissementsClients = 0;
            if ($lotIds->isNotEmpty()) {
                $encaissementsClients = Paiement::whereHas('contrat', function ($q) use ($lotIds) {
                    $q->whereIn('id_lot', $lotIds);
                })->where('statut', 'VALIDE')->sum('montant');
            }

            // Position trésorerie = encaissements clients − versements faits au proprio
            // Positif : GSF a plus reçu des clients que versé au proprio (sain)
            // Négatif : GSF a avancé plus que reçu des clients (GSF finance en avance)
            $positionTresorerie = floatval($encaissementsClients) - floatval($totalVerse);

            return response()->json([
                'proprietaire' => $p->only(['id_proprietaire', 'nom', 'contact', 'email', 'adresse', 'note', 'date_creation_formatee']),
                'lots'         => $lots,
                'versements'   => $versements,
                'stats'        => [
                    'nb_lots'                    => $lots->count(),
                    'disponible'                 => $parStatut->get('DISPONIBLE', 0),
                    'reserve'                    => $parStatut->get('RESERVE', 0),
                    'vendu'                      => $parStatut->get('VENDU', 0),
                    'total_prix_achat'           => floatval($totalPrixAchat),
                    'total_prix_vente_theorique' => floatval($totalPrixVenteTheorique),
                    'gain_theorique'             => floatval($gainTheorique),
                    'encaissements_clients'      => floatval($encaissementsClients),
                    'total_verse'                => floatval($totalVerse),
                    'reste_a_verser'             => floatval($totalPrixAchat - $totalVerse),
                    'position_tresorerie'        => $positionTresorerie,
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Propriétaire introuvable'], 404);
        } catch (\Exception $e) {
            Log::error('Erreur détail propriétaire: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur'], 500);
        }
    }

    // ── API : créer ───────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        PermissionHelper::abort403('utilisateurs', 'create');

        try {
            $validated = $request->validate([
                'nom'     => 'required|string|max:200',
                'contact' => 'nullable|string|max:50',
                'email'   => 'nullable|email|max:150',
                'adresse' => 'nullable|string|max:300',
                'note'    => 'nullable|string',
            ]);

            $p = ProprietaireTerrien::create($validated);

            return response()->json([
                'success'      => true,
                'message'      => 'Propriétaire créé avec succès',
                'proprietaire' => $p,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Erreur de validation', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Erreur création propriétaire: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }

    // ── API : modifier ────────────────────────────────────────────────────────

    public function update(Request $request, $id)
    {
        PermissionHelper::abort403('utilisateurs', 'update');

        try {
            $p = ProprietaireTerrien::notDeleted()->findOrFail($id);

            $validated = $request->validate([
                'nom'     => 'required|string|max:200',
                'contact' => 'nullable|string|max:50',
                'email'   => 'nullable|email|max:150',
                'adresse' => 'nullable|string|max:300',
                'note'    => 'nullable|string',
            ]);

            $p->update($validated);

            return response()->json([
                'success'      => true,
                'message'      => 'Propriétaire modifié avec succès',
                'proprietaire' => $p,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Erreur de validation', 'errors' => $e->errors()], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Propriétaire introuvable'], 404);
        } catch (\Exception $e) {
            Log::error('Erreur modification propriétaire: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur'], 500);
        }
    }

    // ── API : supprimer ───────────────────────────────────────────────────────

    public function destroy($id)
    {
        PermissionHelper::abort403('utilisateurs', 'delete');

        try {
            $p = ProprietaireTerrien::notDeleted()->findOrFail($id);

            if ($p->lots()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer : ce propriétaire a des lots associés.',
                ], 422);
            }

            $p->delete();

            return response()->json(['success' => false, 'message' => 'Propriétaire supprimé']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Propriétaire introuvable'], 404);
        } catch (\Exception $e) {
            Log::error('Erreur suppression propriétaire: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur'], 500);
        }
    }

    // ── API : liste pour select (lots) ────────────────────────────────────────

    public function getListe()
    {
        try {
            $liste = ProprietaireTerrien::notDeleted()
                ->orderBy('nom')
                ->get(['id_proprietaire', 'nom', 'contact']);

            return response()->json($liste);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }

    // ── API : enregistrer un versement ────────────────────────────────────────

    public function storeVersement(Request $request, $id)
    {
        PermissionHelper::abort403('utilisateurs', 'create');

        try {
            $p = ProprietaireTerrien::notDeleted()->findOrFail($id);

            $validated = $request->validate([
                'montant'          => 'required|numeric|min:1',
                'date_versement'   => 'required|date',
                'mode_paiement'    => 'required|in:ESPECES,VIREMENT,CHEQUE,MOBILE_MONEY',
                'banque'           => 'nullable|string|max:100',
                'operateur'        => 'nullable|string|max:100',
                'reference_cheque' => 'nullable|string|max:100',
                'note'             => 'nullable|string',
            ]);

            $validated['id_proprietaire'] = $p->id_proprietaire;
            $validated['id_utilisateur']  = auth('utilisateurs')->id();

            $versement = VersementProprietaire::create($validated);

            return response()->json([
                'success'   => true,
                'message'   => 'Versement enregistré avec succès',
                'versement' => $versement,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Erreur de validation', 'errors' => $e->errors()], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Propriétaire introuvable'], 404);
        } catch (\Exception $e) {
            Log::error('Erreur versement: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }

    // ── API : supprimer un versement ──────────────────────────────────────────

    public function destroyVersement($idProprietaire, $idVersement)
    {
        PermissionHelper::abort403('utilisateurs', 'delete');

        try {
            $versement = VersementProprietaire::where('id_proprietaire', $idProprietaire)
                ->where('id_versement', $idVersement)
                ->firstOrFail();

            $versement->delete();

            return response()->json(['success' => true, 'message' => 'Versement supprimé']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Versement introuvable'], 404);
        } catch (\Exception $e) {
            Log::error('Erreur suppression versement: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur'], 500);
        }
    }
}
