<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class Contrat extends Model
{
    /**
     * Désactiver les timestamps automatiques
     */
    public $timestamps = false;

    /**
     * Le nom de la table associée au modèle.
     */
    protected $table = 'contrats';

    /**
     * La clé primaire de la table.
     */
    protected $primaryKey = 'id_contrat';

    /**
     * Les attributs qui sont assignables en masse.
     */
    protected $fillable = [
        'reference_contrat',
        'id_client',
        'id_lot',
        'date_contrat',
        'date_debut',
        'date_fin',
        'montant_total',
        'montant_verse',
        'reste_a_payer',
        'type_paiement',
        'duree_mois',
        'frequence_paiement',
        'statut',
        'created_at',
        'valeursupprimee',
        'mode_paiement',
        'banque_paiement',
        'documents_acceptes',
        'date_acceptation_documents'
    ];

    /**
     * Les attributs qui doivent être castés.
     */
    protected $casts = [
        'date_contrat' => 'date',
        'date_debut' => 'date',
        'date_fin' => 'date',
        'montant_total' => 'decimal:2',
        'montant_verse' => 'decimal:2',
        'reste_a_payer' => 'decimal:2',
        'duree_mois' => 'integer',
        'created_at' => 'datetime',
        'valeursupprimee' => 'boolean',
        'documents_acceptes' => 'boolean',
        'date_acceptation_documents' => 'date'
    ];

    /**
     * Les attributs avec leurs valeurs par défaut.
     */
    protected $attributes = [
        'statut' => 'EN_COURS',
        'valeursupprimee' => 0,
        'montant_verse' => 0.00,
        'reste_a_payer' => 0.00,
        'frequence_paiement' => 'MENSUEL',
        'documents_acceptes' => 0
    ];

    /**
     * Les attributs à ajouter automatiquement aux réponses JSON.
     */
    protected $appends = [
        'statut_label',
        'type_paiement_label',
        'montant_total_formatted',
        'mode_paiement_label',
        'banque_paiement_label',
        'date_contrat_formatted',
        'created_at_formatted',
        'montant_paye',
        'montant_paye_formatted',
        'reste_a_payer_calcule',
        'reste_a_payer_formatted',
        'progression_paiement',
        'est_solde',
        'peut_etre_modifie',
        'prochaine_echeance',
        'nombre_echeances_en_retard',
        'nombre_echeances_total',
        'nombre_echeances_payees',
        'tous_documents_acceptes'
    ];

    /**
     * Boot method pour définir des événements du modèle.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = now();
            $model->reference_contrat = self::generateReference();

            // Initialiser les valeurs par défaut
            if (!isset($model->montant_verse)) {
                $model->montant_verse = 0.00;
            }
            if (!isset($model->reste_a_payer)) {
                $model->reste_a_payer = $model->montant_total ?? 0.00;
            }
            if (!isset($model->date_debut)) {
                $model->date_debut = $model->date_contrat;
            }
        });

        static::created(function ($contrat) {
            // Générer les échéances pour les contrats échelonnés
            if ($contrat->type_paiement === 'ECHELONNE') {
                $contrat->genererEcheances();
            }

            // Générer les documents ACD
            $contrat->genererDocumentsAcD();

            $lot = Lot::find($contrat->id_lot);

            if ($lot && $lot->statut === 'DISPONIBLE') {
                if ($contrat->type_paiement === 'COMPTANT' || $contrat->statut === 'SOLDE') {
                    $lot->update(['statut' => 'VENDU']);
                    Log::info('Lot marqué comme VENDU (création contrat COMPTANT/SOLDE)', [
                        'lot_id' => $lot->id_lot,
                        'contrat_id' => $contrat->id_contrat,
                        'type_paiement' => $contrat->type_paiement
                    ]);
                } else {
                    $lot->update(['statut' => 'RESERVE']);
                    Log::info('Lot marqué comme RÉSERVÉ (création contrat ECHELONNE)', [
                        'lot_id' => $lot->id_lot,
                        'contrat_id' => $contrat->id_contrat
                    ]);
                }
            }
        });
    }

    /**
     * Générer une référence unique pour le contrat
     */
    public static function generateReference()
    {
        $year = date('Y');

        $lastContrat = self::where('reference_contrat', 'LIKE', $year . '/%')
                           ->orderBy('reference_contrat', 'desc')
                           ->first();

        if ($lastContrat && $lastContrat->reference_contrat) {
            $lastNumber = intval(substr($lastContrat->reference_contrat, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $year . '/' . $newNumber;
    }

    /**
     * Relation avec le client
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'id_client', 'id');
    }

    /**
     * Relation avec le lot
     */
    public function lot()
    {
        return $this->belongsTo(Lot::class, 'id_lot', 'id_lot');
    }

    /**
     * Relation avec les paiements
     */
    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'id_contrat', 'id_contrat');
    }

    /**
     * Relation avec les échéances
     */
    public function echeances()
    {
        return $this->hasMany(Echeance::class, 'id_contrat', 'id_contrat')
                    ->where('valeursupprimee', 0)
                    ->orderBy('date_echeance', 'asc');
    }

    /**
     * Relation avec les documents du contrat
     */
    public function contratDocuments()
    {
        return $this->hasMany(ContratDocument::class, 'id_contrat', 'id_contrat')
                    ->where('valeursupprimee', 0);
    }

    /**
     * Relation avec les documents du contrat (tous, y compris soft deleted)
     */
    public function allContratDocuments()
    {
        return $this->hasMany(ContratDocument::class, 'id_contrat', 'id_contrat');
    }

    /**
     * Vérifier si tous les documents obligatoires sont payés/acceptés
     */
    public function getTousDocumentsAcceptesAttribute()
    {
        // Récupérer tous les types de documents obligatoires
        $docTypesObligatoires = DocumentType::where('est_obligatoire', 1)
                                            ->where('actif', 1)
                                            ->where('valeursupprimee', 0)
                                            ->pluck('id_document_type')
                                            ->toArray();

        if (empty($docTypesObligatoires)) {
            return true;
        }

        // Récupérer les documents du contrat qui sont payés ou disponibles
        $documentsOk = $this->contratDocuments()
                            ->whereIn('id_document_type', $docTypesObligatoires)
                            ->whereIn('statut_document', ['paye', 'disponible', 'remis'])
                            ->count();

        return $documentsOk === count($docTypesObligatoires);
    }

    /**
     * Générer automatiquement les documents ACD pour ce contrat
     */
    public function genererDocumentsAcD()
    {
        // Récupérer le lotissement et sa ville
        $lot = $this->lot;
        if (!$lot || !$lot->lotissement) {
            Log::warning('Impossible de générer les documents ACD : lot ou lotissement introuvable', [
                'contrat_id' => $this->id_contrat,
                'lot_id' => $this->id_lot
            ]);
            return false;
        }

        $ville = $lot->lotissement->ville;
        if (!$ville) {
            Log::warning('Impossible de générer les documents ACD : ville du lotissement introuvable', [
                'contrat_id' => $this->id_contrat,
                'lotissement_id' => $lot->id_lotissement
            ]);
            return false;
        }

        // Récupérer tous les types de documents actifs
        $documentTypes = DocumentType::where('actif', 1)
                                     ->where('valeursupprimee', 0)
                                     ->orderBy('ordre_affichage')
                                     ->get();

        if ($documentTypes->isEmpty()) {
            Log::warning('Aucun type de document ACD configuré', [
                'contrat_id' => $this->id_contrat
            ]);
            return false;
        }

        $compteur = 0;
        foreach ($documentTypes as $docType) {
            // Chercher le tarif pour cette ville
            $frais = VilleDocumentFrais::where('ville', $ville)
                                       ->where('id_document_type', $docType->id_document_type)
                                       ->where('valeursupprimee', 0)
                                       ->first();

            $montant = $frais ? $frais->montant : 0;
            $idFrais = $frais ? $frais->id_frais : null;

            // Créer le document lié au contrat
            ContratDocument::create([
                'id_contrat' => $this->id_contrat,
                'id_lot' => $this->id_lot,
                'id_document_type' => $docType->id_document_type,
                'id_frais' => $idFrais,
                'montant' => $montant,
                'statut_paiement' => 'non_paye',
                'statut_document' => 'en_attente',
                'valeursupprimee' => 0
            ]);
            $compteur++;
        }

        Log::info('Documents ACD générés pour le contrat', [
            'contrat_id' => $this->id_contrat,
            'ville' => $ville,
            'nombre_documents' => $compteur
        ]);

        return true;
    }

    /**
     * Générer les échéances pour un contrat échelonné (MONTANTS ENTIERS)
     */
    public function genererEcheances()
    {
        if ($this->type_paiement !== 'ECHELONNE') {
            return false;
        }

        $dureeMois = $this->duree_mois ?? 1;
        if ($dureeMois <= 0) {
            $dureeMois = 1;
        }

        // Supprimer les anciennes échéances (soft delete)
        Echeance::where('id_contrat', $this->id_contrat)->update(['valeursupprimee' => 1]);

        $montantTotal = floatval($this->montant_total ?? 0);

        // Calculer un montant ENTIER par échéance
        $montantParEcheance = floor($montantTotal / $dureeMois);
        $reste = $montantTotal - ($montantParEcheance * $dureeMois);

        $dateDebut = $this->date_debut ?? $this->date_contrat ?? now();
        $dateEcheance = Carbon::parse($dateDebut);

        $echeances = [];
        for ($i = 1; $i <= $dureeMois; $i++) {
            $montant = $montantParEcheance;

            // Ajouter 1 FCFA aux premières échéances si nécessaire
            if ($reste > 0) {
                $montant++;
                $reste--;
            }

            $echeances[] = [
                'id_contrat' => $this->id_contrat,
                'numero_echeance' => $i,
                'date_echeance' => $dateEcheance->copy(),
                'montant_prevu' => $montant,
                'montant_paye' => 0,
                'statut' => 'A_PAYER',
                'valeursupprimee' => 0
            ];

            $dateEcheance->addMonth();
        }

        $result = Echeance::insert($echeances);

        $this->update(['date_fin' => $dateEcheance->copy()->subMonth()]);

        Log::info('Échéances générées pour le contrat', [
            'contrat_id' => $this->id_contrat,
            'reference' => $this->reference_contrat,
            'nombre_echeances' => $dureeMois,
            'montant_par_echeance' => $montantParEcheance,
            'reste_reparti' => $montantTotal - ($montantParEcheance * $dureeMois)
        ]);

        return $result;
    }

    /**
     * Récupérer le montant total des paiements validés (non supprimés)
     */
    public function getMontantPayeAttribute()
    {
        $total = $this->paiements()
                      ->where('statut', 'VALIDE')
                      ->where(function($query) {
                          $query->where('valeursupprimee', '!=', 1)
                                ->orWhereNull('valeursupprimee');
                      })
                      ->sum('montant');

        return floatval($total ?? 0);
    }

    /**
     * Récupérer le montant payé formaté
     */
    public function getMontantPayeFormattedAttribute()
    {
        $montant = $this->montant_paye ?? 0;
        if ($montant <= 0) {
            return '0 FCFA';
        }
        return number_format($montant, 0, ',', ' ') . ' FCFA';
    }

    /**
     * Récupérer le reste à payer (calculé)
     */
    public function getResteAPayerCalculeAttribute()
    {
        $montantTotal = floatval($this->montant_total ?? 0);
        $montantPaye = floatval($this->montant_paye ?? 0);
        $reste = max(0, $montantTotal - $montantPaye);

        // Si le statut est SOLDE, forcer à 0
        if ($this->statut === 'SOLDE') {
            return 0;
        }

        return $reste;
    }

    /**
     * Récupérer le reste à payer formaté
     */
    public function getResteAPayerFormattedAttribute()
    {
        $reste = $this->reste_a_payer_calcule ?? 0;
        if ($reste <= 0) {
            return '0 FCFA';
        }
        return number_format($reste, 0, ',', ' ') . ' FCFA';
    }

    /**
     * Récupérer le pourcentage de progression du paiement
     */
    public function getProgressionPaiementAttribute()
    {
        $montantTotal = floatval($this->montant_total ?? 0);
        $montantPaye = floatval($this->montant_paye ?? 0);

        if ($montantTotal <= 0) {
            return 0;
        }
        return min(100, round(($montantPaye / $montantTotal) * 100));
    }

    /**
     * Vérifier si le contrat est soldé
     */
    public function getEstSoldeAttribute()
    {
        return $this->statut === 'SOLDE' || $this->reste_a_payer_calcule <= 0;
    }

    /**
     * Vérifier si le contrat peut être modifié
     */
    public function getPeutEtreModifieAttribute()
    {
        return $this->statut !== 'SOLDE' && $this->valeursupprimee != 1;
    }

    /**
     * Récupérer la prochaine échéance à payer
     */
    public function getProchaineEcheanceAttribute()
    {
        $echeance = $this->echeances()
                         ->whereIn('statut', ['A_PAYER', 'EN_RETARD'])
                         ->orderBy('date_echeance', 'asc')
                         ->first();

        if (!$echeance) {
            return null;
        }

        return [
            'id' => $echeance->id,
            'numero' => $echeance->numero_echeance,
            'date_echeance' => $echeance->date_echeance_formatted,
            'montant_prevu' => $echeance->montant_prevu,
            'montant_prevu_formatted' => $echeance->montant_prevu_formatted,
            'reste_a_payer' => $echeance->reste_a_payer,
            'reste_a_payer_formatted' => $echeance->reste_a_payer_formatted,
            'statut' => $echeance->statut,
            'statut_label' => $echeance->statut_label,
            'est_en_retard' => $echeance->est_en_retard
        ];
    }

    /**
     * Récupérer le nombre d'échéances en retard
     */
    public function getNombreEcheancesEnRetardAttribute()
    {
        return $this->echeances()->where('statut', 'EN_RETARD')->count();
    }

    /**
     * Récupérer le nombre total d'échéances
     */
    public function getNombreEcheancesTotalAttribute()
    {
        return $this->echeances()->count();
    }

    /**
     * Récupérer le nombre d'échéances payées
     */
    public function getNombreEcheancesPayeesAttribute()
    {
        return $this->echeances()->where('statut', 'PAYE')->count();
    }

    /**
     * Vérifier si le contrat peut recevoir un nouveau paiement
     */
    public function peutRecevoirPaiement()
    {
        if ($this->statut === 'SOLDE') {
            return false;
        }

        if ($this->reste_a_payer_calcule <= 0) {
            return false;
        }

        return true;
    }

    /**
     * Mettre à jour le statut du contrat en fonction des paiements
     */
    public function updateContratStatut()
    {
        $totalPaye = $this->paiements()
                          ->where('statut', 'VALIDE')
                          ->where(function($query) {
                              $query->where('valeursupprimee', '!=', 1)
                                    ->orWhereNull('valeursupprimee');
                          })
                          ->sum('montant');

        $montantTotal = $this->montant_total;

        // Mettre à jour les montants
        $this->montant_verse = $totalPaye;
        $this->reste_a_payer = max(0, $montantTotal - $totalPaye);

        if ($totalPaye >= $montantTotal && $this->statut !== 'SOLDE') {
            $this->statut = 'SOLDE';
            Log::info('Contrat marqué comme SOLDE', [
                'contrat_id' => $this->id_contrat,
                'reference' => $this->reference_contrat,
                'total_paye' => $totalPaye,
                'montant_total' => $montantTotal
            ]);

            $this->marquerLotVendu();

        } elseif ($totalPaye < $montantTotal && $this->statut === 'SOLDE') {
            $this->statut = 'EN_COURS';
            Log::info('Contrat repassé en EN_COURS', [
                'contrat_id' => $this->id_contrat,
                'reference' => $this->reference_contrat,
                'total_paye' => $totalPaye,
                'montant_total' => $montantTotal
            ]);

            $this->marquerLotReserve();
        }

        $this->save();

        return $this;
    }

    /**
     * Vérifier et mettre à jour le statut du contrat en fonction des échéances
     */
    public function verifierEtMettreAJourStatut()
    {
        $this->load('echeances');

        foreach ($this->echeances as $echeance) {
            $echeance->updateStatutAutomatique();
            $echeance->save();
        }

        $this->load('echeances');

        $toutesEcheancesPayees = $this->echeances->every(function($echeance) {
            return $echeance->est_paye;
        });

        if ($toutesEcheancesPayees && $this->statut !== 'SOLDE') {
            $this->update(['statut' => 'SOLDE']);
            $this->marquerLotVendu();

            Log::info('Contrat marqué comme SOLDE (toutes échéances payées)', [
                'contrat_id' => $this->id_contrat,
                'reference' => $this->reference_contrat
            ]);
        }

        $aEcheancesEnRetard = $this->echeances->contains(function($echeance) {
            return $echeance->est_en_retard;
        });

        if ($aEcheancesEnRetard && $this->statut === 'EN_COURS') {
            $this->update(['statut' => 'EN_RETARD']);

            Log::info('Contrat marqué comme EN_RETARD', [
                'contrat_id' => $this->id_contrat,
                'reference' => $this->reference_contrat
            ]);
        }

        if (!$aEcheancesEnRetard && $this->statut === 'EN_RETARD' && !$toutesEcheancesPayees) {
            $this->update(['statut' => 'EN_COURS']);

            Log::info('Contrat repassé en EN_COURS', [
                'contrat_id' => $this->id_contrat,
                'reference' => $this->reference_contrat
            ]);
        }

        return $this;
    }

    /**
     * Traiter un nouveau paiement et l'affecter aux échéances
     */
    public function traiterPaiementEcheances($montant)
    {
        $montantRestant = $montant;
        $echeancesAffectees = [];

        $echeancesNonPayees = $this->echeances()
                                   ->whereIn('statut', ['A_PAYER', 'EN_RETARD'])
                                   ->orderBy('date_echeance', 'asc')
                                   ->get();

        foreach ($echeancesNonPayees as $echeance) {
            if ($montantRestant <= 0) {
                break;
            }

            $resteAvant = $echeance->reste_a_payer;
            $montantRestant = $echeance->appliquerPaiement($montantRestant);
            $montantApplique = $resteAvant - $echeance->reste_a_payer;

            if ($montantApplique > 0) {
                $echeancesAffectees[] = [
                    'echeance_id' => $echeance->id,
                    'numero' => $echeance->numero_echeance,
                    'montant_applique' => $montantApplique,
                    'date_echeance' => $echeance->date_echeance_formatted
                ];
            }
        }

        $this->montant_verse = $this->montant_paye;
        $this->reste_a_payer = $this->reste_a_payer_calcule;
        $this->save();

        $this->verifierEtMettreAJourStatut();

        return [
            'success' => true,
            'montant_total' => $montant,
            'montant_restant' => $montantRestant,
            'echeances_affectees' => $echeancesAffectees
        ];
    }

    /**
     * Marquer le lot comme VENDU
     */
    private function marquerLotVendu()
    {
        $lot = Lot::find($this->id_lot);
        if ($lot && $lot->statut !== 'VENDU') {
            $lot->update(['statut' => 'VENDU']);
            Log::info('Lot marqué comme VENDU (contrat soldé)', [
                'lot_id' => $lot->id_lot,
                'contrat_id' => $this->id_contrat,
                'reference' => $this->reference_contrat
            ]);
        }
    }

    /**
     * Marquer le lot comme RÉSERVÉ
     */
    private function marquerLotReserve()
    {
        $lot = Lot::find($this->id_lot);
        if ($lot && $lot->statut === 'VENDU') {
            $autreContratActif = Contrat::where('id_lot', $this->id_lot)
                                        ->where('id_contrat', '!=', $this->id_contrat)
                                        ->where(function($query) {
                                            $query->where('valeursupprimee', '!=', 1)
                                                  ->orWhereNull('valeursupprimee');
                                        })
                                        ->where('statut', '!=', 'SOLDE')
                                        ->exists();

            if (!$autreContratActif) {
                $lot->update(['statut' => 'RESERVE']);
                Log::info('Lot repassé en RÉSERVÉ', [
                    'lot_id' => $lot->id_lot,
                    'contrat_id' => $this->id_contrat
                ]);
            }
        }
    }

    /**
     * Accesseur pour le libellé du statut
     */
    public function getStatutLabelAttribute()
    {
        $statuts = [
            'EN_COURS' => 'En cours',
            'SOLDE' => 'Soldé',
            'RESILIE' => 'Résilié',
            'EN_RETARD' => 'En retard'
        ];

        return $statuts[$this->statut] ?? $this->statut;
    }

    /**
     * Accesseur pour le libellé du type de paiement
     */
    public function getTypePaiementLabelAttribute()
    {
        $types = [
            'COMPTANT' => 'Comptant',
            'ECHELONNE' => 'Échelonné'
        ];

        return $types[$this->type_paiement] ?? $this->type_paiement;
    }

    /**
     * Accesseur pour le libellé du mode de paiement
     */
    public function getModePaiementLabelAttribute()
    {
        $modes = [
            'ESPECES' => 'Espèces',
            'VIREMENT' => 'Virement bancaire',
            'CHEQUE' => 'Chèque',
            'MOBILE_MONEY' => 'Mobile Money'
        ];

        return $modes[$this->mode_paiement] ?? $this->mode_paiement ?? '-';
    }

    /**
     * Accesseur pour le libellé de la banque
     */
    public function getBanquePaiementLabelAttribute()
    {
        $banques = [
            'SGBCI' => 'SGBCI (Société Générale)',
            'ECOBANK' => 'Ecobank CI',
            'NSIA' => 'NSIA Banque',
            'ORABANK' => 'Orabank',
            'BICICI' => 'BICICI',
            'BRIDGE' => 'Bridge Bank Group',
            'CORIS' => 'Coris Bank International',
            'BANK_OF_AFRICA' => 'Bank of Africa'
        ];

        return $banques[$this->banque_paiement] ?? $this->banque_paiement ?? '-';
    }

    /**
     * Accesseur pour le montant total formaté
     */
    public function getMontantTotalFormattedAttribute()
    {
        $montant = floatval($this->montant_total ?? 0);
        if ($montant <= 0) {
            return '0 FCFA';
        }
        return number_format($montant, 0, ',', ' ') . ' FCFA';
    }

    /**
     * Accesseur pour la date du contrat formatée
     */
    public function getDateContratFormattedAttribute()
    {
        if (!$this->date_contrat) {
            return '-';
        }
        return $this->date_contrat->format('d/m/Y');
    }

    /**
     * Accesseur pour la date de création formatée
     */
    public function getCreatedAtFormattedAttribute()
    {
        if (!$this->created_at) {
            return '-';
        }
        return $this->created_at->format('d/m/Y H:i');
    }

    /**
     * Scope pour exclure les contrats supprimés
     */
    public function scopeNotDeleted($query)
    {
        return $query->where('valeursupprimee', '!=', 1)
                     ->orWhereNull('valeursupprimee');
    }

    /**
     * Scope pour les contrats soldés
     */
    public function scopeSolde($query)
    {
        return $query->where('statut', 'SOLDE');
    }

    /**
     * Scope pour les contrats en cours
     */
    public function scopeEnCours($query)
    {
        return $query->where('statut', 'EN_COURS');
    }

    /**
     * Scope pour les contrats en retard
     */
    public function scopeEnRetard($query)
    {
        return $query->where('statut', 'EN_RETARD');
    }

    /**
     * Suppression logique
     */
    public function delete()
    {
        $this->valeursupprimee = 1;
        return $this->save();
    }
}
