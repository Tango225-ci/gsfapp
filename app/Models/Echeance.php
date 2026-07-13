<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class Echeance extends Model
{
    /**
     * Désactiver les timestamps automatiques
     */
    public $timestamps = false;

    /**
     * Le nom de la table associée au modèle.
     */
    protected $table = 'echeances';

    /**
     * Les attributs qui sont assignables en masse.
     */
    protected $fillable = [
        'id_contrat',
        'date_echeance',
        'montant_prevu',
        'montant_paye',
        'statut',
        'valeursupprimee'
    ];

    /**
     * Les attributs qui doivent être castés.
     */
    protected $casts = [
        'date_echeance' => 'date',
        'montant_prevu' => 'decimal:2',
        'montant_paye' => 'decimal:2',
        'valeursupprimee' => 'boolean'
    ];

    /**
     * Les attributs avec leurs valeurs par défaut.
     */
    protected $attributes = [
        'montant_paye' => 0.00,
        'statut' => 'A_PAYER',
        'valeursupprimee' => 0
    ];

    /**
     * Les attributs à ajouter automatiquement aux réponses JSON.
     */
    protected $appends = [
        'reste_a_payer',
        'reste_a_payer_formatted',
        'est_en_retard',
        'est_paye',
        'statut_label',
        'statut_class',
        'date_echeance_formatted',
        'montant_prevu_formatted',
        'montant_paye_formatted',
        'numero_echeance',
        'progression_paiement'
    ];

    /**
     * Boot method pour définir des événements du modèle.
     */
    protected static function boot()
    {
        parent::boot();

        // Avant la sauvegarde, vérifier le statut automatiquement
        static::saving(function ($echeance) {
            $echeance->updateStatutAutomatique();
        });

        // Après la mise à jour d'une échéance, vérifier le statut global du contrat
        static::updated(function ($echeance) {
            if ($echeance->contrat) {
                $echeance->contrat->verifierEtMettreAJourStatut();
            }
        });
    }

    /**
     * Relation avec le contrat
     */
    public function contrat()
    {
        return $this->belongsTo(Contrat::class, 'id_contrat', 'id_contrat');
    }

    /**
     * Accesseur pour le reste à payer sur cette échéance
     */
    public function getResteAPayerAttribute()
    {
        return max(0, $this->montant_prevu - $this->montant_paye);
    }

    /**
     * Accesseur pour le reste à payer formaté
     */
    public function getResteAPayerFormattedAttribute()
    {
        $reste = $this->reste_a_payer ?? 0;
        if ($reste <= 0) {
            return '0 FCFA';
        }
        return number_format($reste, 0, ',', ' ') . ' FCFA';
    }

    /**
     * Accesseur pour le montant prévu formaté
     */
    public function getMontantPrevuFormattedAttribute()
    {
        $montant = floatval($this->montant_prevu ?? 0);
        if ($montant <= 0) {
            return '0 FCFA';
        }
        return number_format($montant, 0, ',', ' ') . ' FCFA';
    }

    /**
     * Accesseur pour le montant payé formaté
     */
    public function getMontantPayeFormattedAttribute()
    {
        $montant = floatval($this->montant_paye ?? 0);
        if ($montant <= 0) {
            return '0 FCFA';
        }
        return number_format($montant, 0, ',', ' ') . ' FCFA';
    }

    /**
     * Accesseur pour la date d'échéance formatée
     */
    public function getDateEcheanceFormattedAttribute()
    {
        if (!$this->date_echeance) {
            return '-';
        }
        return $this->date_echeance->format('d/m/Y');
    }

    /**
     * Accesseur pour vérifier si l'échéance est en retard
     */
    public function getEstEnRetardAttribute()
    {
        // Si déjà payée, elle n'est pas en retard
        if ($this->est_paye) {
            return false;
        }

        // Si le statut est déjà EN_RETARD
        if ($this->statut === 'EN_RETARD') {
            return true;
        }

        // Vérifier si la date est dépassée
        return Carbon::parse($this->date_echeance)->isPast();
    }

    /**
     * Accesseur pour vérifier si l'échéance est payée
     */
    public function getEstPayeAttribute()
    {
        return $this->statut === 'PAYE' || $this->montant_paye >= $this->montant_prevu;
    }

    /**
     * Accesseur pour le libellé du statut
     */
    public function getStatutLabelAttribute()
    {
        $labels = [
            'A_PAYER' => 'À payer',
            'PAYE' => 'Payée',
            'EN_RETARD' => 'En retard'
        ];
        return $labels[$this->statut] ?? $this->statut;
    }

    /**
     * Accesseur pour la classe CSS du statut
     */
    public function getStatutClassAttribute()
    {
        $classes = [
            'A_PAYER' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
            'PAYE' => 'bg-green-100 text-green-800 border-green-300',
            'EN_RETARD' => 'bg-red-100 text-red-800 border-red-300'
        ];
        return $classes[$this->statut] ?? 'bg-gray-100 text-gray-800 border-gray-300';
    }

    /**
     * Accesseur pour le numéro d'échéance (formaté avec séquence)
     * Format: ECH-{ANNEE}-{MOIS}-{SEQUENCE}
     */
    public function getNumeroEcheanceAttribute()
    {
        // La position est déjà stockée en base (définie lors de la génération des échéances),
        // pas besoin de recharger toutes les échéances du contrat pour la recalculer.
        $position = $this->attributes['numero_echeance'] ?? null;

        if (!$position || !$this->id_contrat) {
            return 'ECH-' . date('Y-m') . '-001';
        }

        if ($this->relationLoaded('contrat')) {
            // Relation déjà chargée par l'appelant : on la réutilise sans requête supplémentaire
            $contrat = $this->contrat;
            $refContrat = $contrat ? ($contrat->reference_contrat ?? $contrat->id_contrat) : $this->id_contrat;
        } else {
            // Requête scalaire légère : on évite de charger (et mettre en cache comme relation)
            // le modèle Contrat complet, ce qui déclencherait tous ses accessors lourds dès que
            // cette échéance est sérialisée en JSON (la relation chargée est incluse dans toArray()).
            $refContrat = self::query()->getConnection()->table('contrats')
                ->where('id_contrat', $this->id_contrat)
                ->value('reference_contrat') ?? $this->id_contrat;
        }

        if (!$refContrat) {
            return 'ECH-' . date('Y-m') . '-001';
        }

        // Formater le numéro avec la référence du contrat et la séquence
        $refContrat = preg_replace('/[^A-Za-z0-9]/', '', $refContrat); // Nettoyer la référence

        // Format: ECH-{REF_CONTRAT}-{POSITION_SUR_3_CHIFFRES}
        return 'ECH-' . strtoupper(substr($refContrat, 0, 10)) . '-' . str_pad($position, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Accesseur pour la progression du paiement en pourcentage
     */
    public function getProgressionPaiementAttribute()
    {
        if ($this->montant_prevu <= 0) {
            return 0;
        }
        return min(100, round(($this->montant_paye / $this->montant_prevu) * 100));
    }

    /**
     * Mettre à jour automatiquement le statut en fonction de la date et du montant payé
     */
    public function updateStatutAutomatique()
    {
        // Si le montant payé est supérieur ou égal au montant prévu
        if ($this->montant_paye >= $this->montant_prevu) {
            $this->statut = 'PAYE';
            $this->montant_paye = $this->montant_prevu; // Ne pas dépasser
            return;
        }

        // Si la date est dépassée et non payée
        if (Carbon::parse($this->date_echeance)->isPast() && $this->montant_paye < $this->montant_prevu) {
            $this->statut = 'EN_RETARD';
            return;
        }

        // Sinon, à payer
        if ($this->montant_paye < $this->montant_prevu && !Carbon::parse($this->date_echeance)->isPast()) {
            $this->statut = 'A_PAYER';
        }
    }

    /**
     * Appliquer un paiement sur cette échéance
     *
     * @param float $montant Montant à appliquer
     * @return float Le montant restant à affecter (si le paiement dépasse)
     */
    public function appliquerPaiement($montant)
    {
        $reste_a_payer = $this->reste_a_payer;

        // Si l'échéance est déjà payée, on passe au montant suivant
        if ($reste_a_payer <= 0) {
            return $montant;
        }

        // Si le montant est suffisant pour payer toute l'échéance
        if ($montant >= $reste_a_payer) {
            $this->montant_paye = $this->montant_prevu;
            $this->statut = 'PAYE';
            $this->save();

            Log::info('Échéance payée intégralement', [
                'echeance_id' => $this->id,
                'contrat_id' => $this->id_contrat,
                'montant_paye' => $reste_a_payer
            ]);

            return $montant - $reste_a_payer;
        }

        // Sinon, paiement partiel
        $this->montant_paye += $montant;
        $this->save();

        Log::info('Paiement partiel appliqué sur échéance', [
            'echeance_id' => $this->id,
            'contrat_id' => $this->id_contrat,
            'montant_ajoute' => $montant,
            'nouveau_montant_paye' => $this->montant_paye
        ]);

        return 0; // Tout le montant a été utilisé
    }

    /**
     * Scope pour les échéances non payées
     */
    public function scopeNonPayees($query)
    {
        return $query->whereIn('statut', ['A_PAYER', 'EN_RETARD']);
    }

    /**
     * Scope pour les échéances en retard
     */
    public function scopeEnRetard($query)
    {
        return $query->where('statut', 'EN_RETARD');
    }

    /**
     * Scope pour les échéances à payer (non en retard)
     */
    public function scopeAPayer($query)
    {
        return $query->where('statut', 'A_PAYER');
    }

    /**
     * Scope pour les échéances payées
     */
    public function scopePayees($query)
    {
        return $query->where('statut', 'PAYE');
    }

    /**
     * Scope pour exclure les échéances supprimées
     */
    public function scopeNotDeleted($query)
    {
        return $query->where('valeursupprimee', '!=', 1)
                     ->orWhereNull('valeursupprimee');
    }
}
