<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Lot extends Model
{
    /**
     * Désactiver les timestamps automatiques
     */
    public $timestamps = false;

    /**
     * Le nom de la table associée au modèle.
     */
    protected $table = 'lots';

    /**
     * La clé primaire de la table.
     */
    protected $primaryKey = 'id_lot';

    /**
     * Les attributs qui sont assignables en masse.
     */
    protected $fillable = [
        'numero_lot',
        'numero_ilot',
        'type_lot',
        'superficie',
        'prix_comptant',
        'prix_echelonne',
        'statut',
        'id_lotissement',
        'created_at',
        'valeursupprimee',
        'ville_libelle'
    ];

    /**
     * Les attributs qui doivent être castés.
     */
    protected $casts = [
        'superficie' => 'decimal:2',
        'prix_comptant' => 'decimal:2',
        'prix_echelonne' => 'decimal:2',
        'created_at' => 'datetime',
        'valeursupprimee' => 'boolean'
    ];

    /**
     * Les attributs avec leurs valeurs par défaut.
     */
    protected $attributes = [
        'statut' => 'DISPONIBLE',
        'valeursupprimee' => 0,
        'prix_comptant' => 0,
        'prix_echelonne' => 0
    ];

    /**
     * Les attributs à ajouter automatiquement aux réponses JSON.
     */
    protected $appends = [
        'statut_label',
        'prix_comptant_formatted',
        'prix_echelonne_formatted',
        'lotissement_nom',
        'contrat_actif',
        'can_be_modified',
        'can_be_deleted',
        'ville_nom'
    ];

    /**
     * Boot method pour définir des événements du modèle.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = now();

            if (!isset($model->prix_comptant)) {
                $model->prix_comptant = 0;
            }
            if (!isset($model->prix_echelonne)) {
                $model->prix_echelonne = 0;
            }
        });
    }

    /**
     * Relation avec le lotissement
     */
    public function lotissement()
    {
        return $this->belongsTo(Lotissement::class, 'id_lotissement', 'id_lotissement');
    }

    /**
     * Relation avec les contrats
     */
    public function contrats()
    {
        return $this->hasMany(Contrat::class, 'id_lot', 'id_lot');
    }

    /**
     * Relation avec les documents de contrat liés à ce lot
     */
    public function contratDocuments()
    {
        return $this->hasMany(ContratDocument::class, 'id_lot', 'id_lot');
    }

    /**
     * Accesseur pour le libellé du statut
     */
    public function getStatutLabelAttribute()
    {
        $statuts = [
            'DISPONIBLE' => 'Disponible',
            'RESERVE' => 'Réservé',
            'VENDU' => 'Vendu'
        ];

        return $statuts[$this->statut] ?? $this->statut;
    }

    /**
     * Accesseur pour le prix comptant formaté
     */
    public function getPrixComptantFormattedAttribute()
    {
        $prix = floatval($this->prix_comptant ?? 0);
        if ($prix <= 0) {
            return '0 FCFA';
        }
        return number_format($prix, 0, ',', ' ') . ' FCFA';
    }

    /**
     * Accesseur pour le prix échelonné formaté
     */
    public function getPrixEchelonneFormattedAttribute()
    {
        $prix = floatval($this->prix_echelonne ?? 0);
        if ($prix <= 0) {
            return '0 FCFA';
        }
        return number_format($prix, 0, ',', ' ') . ' FCFA';
    }

    /**
     * Accesseur pour le nom du lotissement
     */
    public function getLotissementNomAttribute()
    {
        if ($this->relationLoaded('lotissement') && $this->lotissement) {
            return $this->lotissement->nom;
        }

        if ($this->id_lotissement) {
            $lotissement = Lotissement::find($this->id_lotissement);
            return $lotissement ? $lotissement->nom : null;
        }

        return null;
    }

    /**
     * Accesseur pour le nom de la ville
     */
    public function getVilleNomAttribute()
    {
        // Priorité à ville_libelle s'il est rempli
        if (!empty($this->ville_libelle)) {
            return $this->ville_libelle;
        }

        // Sinon, récupérer la ville du lotissement
        if ($this->relationLoaded('lotissement') && $this->lotissement) {
            return $this->lotissement->ville;
        }

        if ($this->id_lotissement) {
            $lotissement = Lotissement::find($this->id_lotissement);
            return $lotissement ? $lotissement->ville : null;
        }

        return null;
    }

    /**
     * Vérifier si le lot a un contrat actif
     */
    public function getContratActifAttribute()
    {
        if (!class_exists(Contrat::class)) {
            return false;
        }

        return Contrat::where('id_lot', $this->id_lot)
                      ->where(function($query) {
                          $query->where('valeursupprimee', '!=', 1)
                                ->orWhereNull('valeursupprimee');
                      })
                      ->exists();
    }

    /**
     * Vérifier si le lot peut être modifié
     */
    public function getCanBeModifiedAttribute()
    {
        // Un lot vendu ne peut pas être modifié
        if ($this->statut === 'VENDU') {
            return false;
        }

        // Un lot réservé ne peut pas être modifié
        if ($this->statut === 'RESERVE') {
            return false;
        }

        return true;
    }

    /**
     * Vérifier si le lot peut être supprimé
     */
    public function getCanBeDeletedAttribute()
    {
        // Un lot vendu ne peut pas être supprimé
        if ($this->statut === 'VENDU') {
            return false;
        }

        // Un lot réservé ne peut pas être supprimé
        if ($this->statut === 'RESERVE') {
            return false;
        }

        return true;
    }

    /**
     * Récupérer le contrat actif du lot
     */
    public function getContratActif()
    {
        if (!class_exists(Contrat::class)) {
            return null;
        }

        return Contrat::with(['client'])
                      ->where('id_lot', $this->id_lot)
                      ->where(function($query) {
                          $query->where('valeursupprimee', '!=', 1)
                                ->orWhereNull('valeursupprimee');
                      })
                      ->first();
    }

    /**
     * Scope pour exclure les lots supprimés
     */
    public function scopeNotDeleted($query)
    {
        return $query->where(function($q) {
            $q->where('valeursupprimee', '!=', 1)
              ->orWhereNull('valeursupprimee');
        });
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
