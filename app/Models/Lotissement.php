<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Lotissement extends Model
{
    /**
     * Désactiver les timestamps automatiques
     */
    public $timestamps = false;

    /**
     * Le nom de la table associée au modèle.
     */
    protected $table = 'lotissements';

    /**
     * La clé primaire de la table.
     */
    protected $primaryKey = 'id_lotissement';

    /**
     * Les attributs qui sont assignables en masse.
     */
    protected $fillable = [
        'nom',
        'localisation',
        'ville',
        'statut_lotissement',
        'numero_approbation',
        'date_approbation',
        'created_at',
        'valeursupprimee'
    ];

    /**
     * Les attributs qui doivent être castés.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'date_approbation' => 'date',
        'valeursupprimee' => 'boolean'
    ];

    /**
     * Les attributs avec leurs valeurs par défaut.
     */
    protected $attributes = [
        'valeursupprimee' => 0
    ];

    /**
     * Les attributs à ajouter automatiquement aux réponses JSON.
     */
    protected $appends = [
        'statut_lotissement_label',
        'date_approbation_formatee',
        'date_creation_formatee'
    ];

    /**
     * Boot method pour définir des événements du modèle.
     */
    protected static function boot()
    {
        parent::boot();

        // Définir automatiquement la date de création lors de la création
        static::creating(function ($model) {
            if (!$model->created_at) {
                $model->created_at = now();
            }
        });
    }

    /**
     * Scope pour exclure les lotissements supprimés.
     */
    public function scopeNotDeleted($query)
    {
        return $query->where(function($q) {
            $q->where('valeursupprimee', '!=', 1)
              ->orWhereNull('valeursupprimee');
        });
    }

    /**
     * Scope pour les lotissements par statut.
     */
    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut_lotissement', $statut);
    }

    /**
     * Scope pour les lotissements par ville.
     */
    public function scopeByVille($query, $ville)
    {
        return $query->where('ville', $ville);
    }

    /**
     * Relation avec les lots
     */
    public function lots()
    {
        return $this->hasMany(Lot::class, 'id_lotissement', 'id_lotissement');
    }

    /**
     * Accesseur pour le libellé du statut.
     */
    public function getStatutLotissementLabelAttribute()
    {
        $statuts = [
            'NON_APPROUVE' => 'Non approuvé',
            'APPROUVE' => 'Approuvé',
            'APPROUVE_DIFFUSE' => 'Approuvé et diffusé',
            'ACD_INDIVIDUEL' => 'ACD Individuel',
            'ACD_GLOBAL' => 'ACD Global'
        ];

        return $statuts[$this->statut_lotissement] ?? $this->statut_lotissement ?? 'Non défini';
    }

    /**
     * Accesseur pour formater la date d'approbation.
     */
    public function getDateApprobationFormateeAttribute()
    {
        if (!$this->date_approbation) {
            return null;
        }
        return Carbon::parse($this->date_approbation)->format('d/m/Y');
    }

    /**
     * Accesseur pour formater la date de création.
     */
    public function getDateCreationFormateeAttribute()
    {
        if (!$this->created_at) {
            return null;
        }
        return Carbon::parse($this->created_at)->format('d/m/Y H:i');
    }

    /**
     * Mutateur pour la date d'approbation.
     */
    public function setDateApprobationAttribute($value)
    {
        if ($value) {
            $this->attributes['date_approbation'] = Carbon::parse($value)->format('Y-m-d');
        } else {
            $this->attributes['date_approbation'] = null;
        }
    }

    /**
 * Relation avec les frais de documents par ville
 */
public function fraisDocuments()
{
    return $this->hasMany(VilleDocumentFrais::class, 'ville', 'ville');
}
}
