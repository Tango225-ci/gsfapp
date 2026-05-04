<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Paiement extends Model
{
    /**
     * Désactiver les timestamps automatiques
     */
    public $timestamps = false;

    /**
     * Le nom de la table associée au modèle.
     */
    protected $table = 'paiements';

    /**
     * La clé primaire de la table.
     */
    protected $primaryKey = 'id_paiement';

    /**
     * Les attributs qui sont assignables en masse.
     */
    protected $fillable = [
        'id_contrat',
        'date_paiement',
        'montant',
        'mode_paiement',
        'reference',
        'statut',
        'valeursupprimee',
        'banque_paiement',
        'notes',
        'type_paiement',
        'id_contrat_document'
    ];

    /**
     * Les attributs qui doivent être castés.
     */
    protected $casts = [
        'date_paiement' => 'date',
        'montant' => 'decimal:2',
        'valeursupprimee' => 'boolean'
    ];

    /**
     * Les attributs avec leurs valeurs par défaut.
     */
    protected $attributes = [
        'statut' => 'EN_ATTENTE',
        'valeursupprimee' => 0,
        'type_paiement' => 'ECHELONNE'
    ];

    /**
     * Les attributs à ajouter automatiquement aux réponses JSON.
     */
    protected $appends = [
        'statut_label',
        'mode_paiement_label',
        'montant_formatted',
        'date_paiement_formatted',
        'banque_label',
        'type_paiement_label'
    ];

    /**
     * Boot method pour définir des événements du modèle.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->reference) {
                $model->reference = self::generateReference();
            }
        });

        static::created(function ($model) {
            // Si c'est un paiement de frais de document
            if ($model->type_paiement === 'FRAIS_DOCUMENT' && $model->id_contrat_document) {
                $contratDocument = ContratDocument::find($model->id_contrat_document);
                if ($contratDocument && $model->statut === 'VALIDE') {
                    $contratDocument->marquerPaye($model->reference);
                }
            }

            if ($model->contrat) {
                $model->contrat->updateContratStatut();
            }
        });

        static::updated(function ($model) {
            // Si le paiement est validé après coup
            if ($model->type_paiement === 'FRAIS_DOCUMENT' && $model->id_contrat_document) {
                $contratDocument = ContratDocument::find($model->id_contrat_document);
                if ($contratDocument && $model->statut === 'VALIDE' && $contratDocument->statut_paiement !== 'paye') {
                    $contratDocument->marquerPaye($model->reference);
                }
            }

            if ($model->contrat) {
                $model->contrat->updateContratStatut();
            }
        });

        static::deleted(function ($model) {
            if ($model->contrat) {
                $model->contrat->updateContratStatut();
            }
        });
    }

    /**
     * Générer une référence unique pour le paiement
     */
    public static function generateReference()
    {
        $yearMonth = date('Ym');

        $lastPaiement = self::where('reference', 'LIKE', 'PAI-' . $yearMonth . '-%')
                            ->orderBy('id_paiement', 'desc')
                            ->first();

        if ($lastPaiement && $lastPaiement->reference) {
            $parts = explode('-', $lastPaiement->reference);
            $lastNumber = intval(end($parts));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return 'PAI-' . $yearMonth . '-' . $newNumber;
    }

    /**
     * Relation avec le contrat
     */
    public function contrat()
    {
        return $this->belongsTo(Contrat::class, 'id_contrat', 'id_contrat');
    }

    /**
     * Relation avec le document du contrat (pour les frais de document)
     */
    public function contratDocument()
    {
        return $this->belongsTo(ContratDocument::class, 'id_contrat_document', 'id_contrat_document');
    }

    /**
     * Accesseur pour le libellé du type de paiement
     */
    public function getTypePaiementLabelAttribute()
    {
        $types = [
            'COMPTANT' => 'Paiement comptant',
            'ECHELONNE' => 'Mensualité',
            'FRAIS_DOCUMENT' => 'Frais document ACD',
            'ACOMPTE' => 'Acompte',
            'AUTRE' => 'Autre'
        ];

        return $types[$this->type_paiement] ?? $this->type_paiement;
    }

    /**
     * Accesseur pour le libellé du statut
     */
    public function getStatutLabelAttribute()
    {
        $statuts = [
            'EN_ATTENTE' => 'En attente',
            'VALIDE' => 'Validé',
            'REJETE' => 'Rejeté',
            'ANNULE' => 'Annulé'
        ];

        return $statuts[$this->statut] ?? $this->statut;
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

        return $modes[$this->mode_paiement] ?? $this->mode_paiement;
    }

    /**
     * Accesseur pour le libellé de la banque
     */
    public function getBanqueLabelAttribute()
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
     * Accesseur pour le montant formaté
     */
    public function getMontantFormattedAttribute()
    {
        $montant = floatval($this->montant ?? 0);
        if ($montant <= 0) {
            return '0 FCFA';
        }
        return number_format($montant, 0, ',', ' ') . ' FCFA';
    }

    /**
     * Accesseur pour la date de paiement formatée
     */
    public function getDatePaiementFormattedAttribute()
    {
        if (!$this->date_paiement) {
            return '-';
        }
        return $this->date_paiement instanceof \DateTime
            ? $this->date_paiement->format('d/m/Y')
            : date('d/m/Y', strtotime($this->date_paiement));
    }

    /**
     * Valider le paiement
     */
    public function valider()
    {
        $this->update(['statut' => 'VALIDE']);

        // Si c'est un paiement de frais de document, marquer le document comme payé
        if ($this->type_paiement === 'FRAIS_DOCUMENT' && $this->id_contrat_document) {
            $contratDocument = ContratDocument::find($this->id_contrat_document);
            if ($contratDocument) {
                $contratDocument->marquerPaye($this->reference);
            }
        }

        return $this;
    }

    /**
     * Rejeter le paiement
     */
    public function rejeter()
    {
        $this->update(['statut' => 'REJETE']);
        return $this;
    }

    /**
     * Annuler le paiement
     */
    public function annuler()
    {
        $this->update(['statut' => 'ANNULE']);
        return $this;
    }

    /**
     * Scope pour les paiements validés
     */
    public function scopeValide($query)
    {
        return $query->where('statut', 'VALIDE');
    }

    /**
     * Scope pour les paiements de type frais document
     */
    public function scopeFraisDocument($query)
    {
        return $query->where('type_paiement', 'FRAIS_DOCUMENT');
    }

    /**
     * Scope pour les paiements de type mensualité
     */
    public function scopeMensualite($query)
    {
        return $query->where('type_paiement', 'ECHELONNE');
    }

    /**
     * Scope pour exclure les paiements supprimés
     */
    public function scopeNotDeleted($query)
    {
        return $query->where('valeursupprimee', '!=', 1)
                     ->orWhereNull('valeursupprimee');
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
