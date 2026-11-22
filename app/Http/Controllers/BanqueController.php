<?php

namespace App\Http\Controllers;

use App\Helpers\NotifHelper;
use App\Helpers\PermissionHelper;
use App\Models\CompteBancaire;
use App\Models\OperationBancaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Suivi du (des) compte(s) bancaire(s) de l'entreprise GSF :
 * entrées / sorties saisies en EN_ATTENTE, validées ou rejetées
 * par ADMIN / DIRECTEUR. Seules les opérations validées comptent dans le solde.
 */
class BanqueController extends Controller
{
    // ── Page web ─────────────────────────────────────────────────────────────

    public function index()
    {
        PermissionHelper::abort403('banque', 'view');
        return view('pages.banque.banque', ['title' => 'Banque GSF']);
    }

    // ── Comptes bancaires ────────────────────────────────────────────────────

    public function comptes()
    {
        PermissionHelper::abort403('banque', 'view');

        $comptes = CompteBancaire::notDeleted()->orderByDesc('actif')->orderBy('banque')->get()
            ->map(function (CompteBancaire $c) {
                $enAttente = $c->operations()->where('statut', 'EN_ATTENTE');
                return array_merge($c->toArray(), [
                    'solde'               => $c->soldeActuel(),
                    'disponible'          => $c->disponible(),
                    'nb_en_attente'       => (clone $enAttente)->count(),
                    'entrees_en_attente'  => (float) (clone $enAttente)->where('type', 'CREDIT')->sum('montant'),
                    'sorties_en_attente'  => (float) (clone $enAttente)->where('type', 'DEBIT')->sum('montant'),
                ]);
            });

        return response()->json([
            'comptes'    => $comptes,
            'categories' => OperationBancaire::CATEGORIES,
        ]);
    }

    public function storeCompte(Request $request)
    {
        PermissionHelper::abort403('banque', 'comptes');

        try {
            $compte = CompteBancaire::create($this->validateCompte($request));
            return response()->json(['success' => true, 'message' => 'Compte bancaire créé', 'compte' => $compte], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Erreur de validation', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Erreur création compte bancaire: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur lors de la création du compte'], 500);
        }
    }

    public function updateCompte(Request $request, $id)
    {
        PermissionHelper::abort403('banque', 'comptes');

        try {
            $compte = CompteBancaire::notDeleted()->findOrFail($id);
            $compte->update($this->validateCompte($request));
            return response()->json(['success' => true, 'message' => 'Compte bancaire mis à jour', 'compte' => $compte]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Erreur de validation', 'errors' => $e->errors()], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Compte introuvable'], 404);
        } catch (\Exception $e) {
            Log::error('Erreur mise à jour compte bancaire: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur lors de la mise à jour'], 500);
        }
    }

    public function destroyCompte($id)
    {
        PermissionHelper::abort403('banque', 'comptes');

        try {
            $compte = CompteBancaire::notDeleted()->findOrFail($id);

            if ($compte->operations()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce compte a des opérations : désactivez-le plutôt que de le supprimer.',
                ], 422);
            }

            $compte->update(['valeursupprimee' => 1]);
            return response()->json(['success' => true, 'message' => 'Compte supprimé']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Compte introuvable'], 404);
        }
    }

    private function validateCompte(Request $request): array
    {
        return $request->validate([
            'banque'             => 'required|string|max:100',
            'numero_compte'      => 'required|string|max:100',
            'intitule'           => 'nullable|string|max:150',
            'solde_initial'      => 'required|numeric',
            'date_solde_initial' => 'nullable|date',
            'actif'              => 'boolean',
        ], [
            'banque.required'        => 'La banque est obligatoire.',
            'numero_compte.required' => 'Le numéro de compte est obligatoire.',
            'solde_initial.required' => 'Le solde initial est obligatoire (0 si nouveau compte).',
        ]);
    }

    // ── Opérations ───────────────────────────────────────────────────────────

    public function operations(Request $request)
    {
        PermissionHelper::abort403('banque', 'view');

        $query = OperationBancaire::with(['compte:id,banque,numero_compte', 'auteur:id_utilisateur,nom', 'validateur:id_utilisateur,nom'])
            ->orderByDesc('date_operation')
            ->orderByDesc('id');

        if ($request->filled('compte')) $query->where('id_compte', $request->compte);
        if ($request->filled('statut')) $query->where('statut', $request->statut);
        if ($request->filled('type'))   $query->where('type', $request->type);
        if ($request->filled('du'))     $query->whereDate('date_operation', '>=', $request->du);
        if ($request->filled('au'))     $query->whereDate('date_operation', '<=', $request->au);

        return response()->json($query->limit(1000)->get());
    }

    public function storeOperation(Request $request)
    {
        PermissionHelper::abort403('banque', 'create');

        try {
            $data = $this->validateOperation($request);
            $data['statut']         = 'EN_ATTENTE';
            $data['id_utilisateur'] = auth('utilisateurs')->id();

            $operation = DB::transaction(function () use ($data) {
                $this->verifierProvision($data);
                return OperationBancaire::create($data);
            });
            $operation->load('compte');

            NotifHelper::notifyAdmins('Opération bancaire à valider', [
                ($operation->type === 'CREDIT' ? 'Entrée' : 'Sortie') . ' : ' . $operation->categorie_label,
                'Libellé : ' . $operation->libelle,
                'Montant : ' . number_format((float) $operation->montant, 0, ',', ' ') . ' FCFA',
                'Compte : ' . $operation->compte->banque . ' — ' . $operation->compte->numero_compte,
                'Date : ' . $operation->date_operation_formatee,
            ]);

            return response()->json(['success' => true, 'message' => 'Opération enregistrée, en attente de validation', 'operation' => $operation], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Erreur de validation', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Erreur création opération bancaire: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur lors de l\'enregistrement'], 500);
        }
    }

    public function updateOperation(Request $request, $id)
    {
        PermissionHelper::abort403('banque', 'update');

        try {
            $operation = OperationBancaire::findOrFail($id);

            if ($operation->statut === 'VALIDE') {
                return response()->json(['success' => false, 'message' => 'Une opération validée ne peut plus être modifiée.'], 422);
            }

            $data = $this->validateOperation($request);
            // Une opération rejetée puis corrigée repart en validation
            $data['statut']      = 'EN_ATTENTE';
            $data['motif_rejet'] = null;

            DB::transaction(function () use ($operation, $data) {
                $this->verifierProvision($data, $operation->id);
                $operation->update($data);
            });

            return response()->json(['success' => true, 'message' => 'Opération mise à jour', 'operation' => $operation]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Erreur de validation', 'errors' => $e->errors()], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Opération introuvable'], 404);
        } catch (\Exception $e) {
            Log::error('Erreur mise à jour opération bancaire: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur lors de la mise à jour'], 500);
        }
    }

    public function destroyOperation($id)
    {
        PermissionHelper::abort403('banque', 'delete');

        try {
            $operation = OperationBancaire::findOrFail($id);

            if ($operation->statut === 'VALIDE') {
                return response()->json([
                    'success' => false,
                    'message' => 'Une opération validée ne peut pas être supprimée. Saisissez une opération inverse pour la corriger.',
                ], 422);
            }

            $operation->delete();
            return response()->json(['success' => true, 'message' => 'Opération supprimée']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Opération introuvable'], 404);
        }
    }

    public function validerOperation($id)
    {
        PermissionHelper::abort403('banque', 'valider');

        try {
            $operation = OperationBancaire::findOrFail($id);

            if ($operation->statut !== 'EN_ATTENTE') {
                return response()->json(['success' => false, 'message' => 'Seule une opération en attente peut être validée.'], 422);
            }

            DB::transaction(function () use ($operation) {
                if ($operation->type === 'DEBIT') {
                    $compte = CompteBancaire::lockForUpdate()->findOrFail($operation->id_compte);
                    $solde  = $compte->soldeActuel();
                    if ((float) $operation->montant > $solde) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'montant' => 'Solde insuffisant : la sortie de ' . $this->fcfa($operation->montant)
                                . ' dépasse le solde du compte (' . $this->fcfa($solde) . ').',
                        ]);
                    }
                }

                $operation->update([
                    'statut'      => 'VALIDE',
                    'motif_rejet' => null,
                    'valide_par'  => auth('utilisateurs')->id(),
                    'valide_le'   => now(),
                ]);
            });

            return response()->json(['success' => true, 'message' => 'Opération validée', 'operation' => $operation]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => collect($e->errors())->flatten()->first(), 'errors' => $e->errors()], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Opération introuvable'], 404);
        }
    }

    public function rejeterOperation(Request $request, $id)
    {
        PermissionHelper::abort403('banque', 'rejeter');

        try {
            $validated = $request->validate([
                'motif' => 'required|string|max:255',
            ], ['motif.required' => 'Le motif du rejet est obligatoire.']);

            $operation = OperationBancaire::findOrFail($id);

            if ($operation->statut !== 'EN_ATTENTE') {
                return response()->json(['success' => false, 'message' => 'Seule une opération en attente peut être rejetée.'], 422);
            }

            $operation->update([
                'statut'      => 'REJETE',
                'motif_rejet' => $validated['motif'],
                'valide_par'  => auth('utilisateurs')->id(),
                'valide_le'   => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Opération rejetée', 'operation' => $operation]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Erreur de validation', 'errors' => $e->errors()], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Opération introuvable'], 404);
        }
    }

    /**
     * Une sortie ne peut pas dépasser le montant disponible sur le compte
     * (solde validé − sorties déjà en attente). Le compte est verrouillé
     * pour que deux saisies simultanées ne passent pas toutes les deux.
     */
    private function verifierProvision(array $data, ?int $exceptOperationId = null): void
    {
        if ($data['type'] !== 'DEBIT') {
            return;
        }

        $compte     = CompteBancaire::lockForUpdate()->findOrFail($data['id_compte']);
        $disponible = $compte->disponible($exceptOperationId);

        if ((float) $data['montant'] > $disponible) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'montant' => 'Solde insuffisant : montant disponible sur ce compte ' . $this->fcfa(max(0, $disponible))
                    . ($disponible < $compte->soldeActuel() ? ' (sorties en attente déduites).' : '.'),
            ]);
        }
    }

    private function fcfa($montant): string
    {
        return number_format((float) $montant, 0, ',', ' ') . ' FCFA';
    }

    private function validateOperation(Request $request): array
    {
        $data = $request->validate([
            'id_compte'      => ['required', Rule::exists('comptes_bancaires', 'id')->where(fn ($q) => $q->where('actif', 1)->where(fn ($w) => $w->where('valeursupprimee', '!=', 1)->orWhereNull('valeursupprimee')))],
            'type'           => 'required|in:CREDIT,DEBIT',
            'categorie'      => ['required', Rule::in(OperationBancaire::categoriesKeys())],
            'libelle'        => 'required|string|max:255',
            'montant'        => 'required|numeric|min:1',
            'date_operation' => 'required|date|before_or_equal:today',
            'reference'      => 'nullable|string|max:100',
            'note'           => 'nullable|string',
        ], [
            'id_compte.required'             => 'Choisissez un compte bancaire.',
            'id_compte.exists'               => 'Ce compte bancaire est inactif ou introuvable.',
            'libelle.required'               => 'Le libellé est obligatoire.',
            'montant.min'                    => 'Le montant doit être supérieur à 0.',
            'date_operation.before_or_equal' => 'La date ne peut pas être dans le futur.',
        ]);

        if (!array_key_exists($data['categorie'], OperationBancaire::CATEGORIES[$data['type']])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'categorie' => 'Cette catégorie ne correspond pas au type d\'opération.',
            ]);
        }

        return $data;
    }
}
