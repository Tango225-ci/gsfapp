<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    /**
     * Désactiver les timestamps automatiques (on utilise created_at et updated_at)
     */
    public $timestamps = true;

    /**
     * Le nom de la table associée au modèle.
     */
    protected $table = 'document_types';

    /**
     * La clé primaire de la table.
     */
    protected $primaryKey = 'id_document_type';

    /**
     * Les attributs qui sont assignables en masse.
     */
    protected $fillable = [
        'nom',
        'code',
        'description',
        'ordre_affichage',
        'est_obligatoire',
        'actif',
        'valeursupprimee'
    ];

    /**
     * Les attributs qui doivent être castés.
     */
    protected $casts = [
        'ordre_affichage' => 'integer',
        'est_obligatoire' => 'boolean',
        'actif' => 'boolean',
        'valeursupprimee' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Les attributs avec leurs valeurs par défaut.
     */
    protected $attributes = [
        'est_obligatoire' => 1,
        'actif' => 1,
        'valeursupprimee' => 0,
        'ordre_affichage' => 0
    ];

    /**
     * Les attributs à ajouter automatiquement aux réponses JSON.
     */
    protected $appends = [
        'est_obligatoire_label',
        'actif_label'
    ];

    /**
     * Relation avec les frais par ville
     */
    public function fraisParVille()
    {
        return $this->hasMany(VilleDocumentFrais::class, 'id_document_type', 'id_document_type')
                    ->where('valeursupprimee', 0);
    }

    /**
     * Relation avec les documents de contrat
     */
    public function contratsDocuments()
    {
        return $this->hasMany(ContratDocument::class, 'id_document_type', 'id_document_type')
                    ->where('valeursupprimee', 0);
    }

    /**
     * Accesseur pour le libellé est_obligatoire
     */
    public function getEstObligatoireLabelAttribute()
    {
        return $this->est_obligatoire ? 'Obligatoire' : 'Optionnel';
    }

    /**
     * Accesseur pour le libellé actif
     */
    public function getActifLabelAttribute()
    {
        return $this->actif ? 'Actif' : 'Inactif';
    }

    /**
     * Scope pour les types actifs
     */
    public function scopeActif($query)
    {
        return $query->where('actif', 1);
    }

    /**
     * Scope pour les types obligatoires
     */
    public function scopeObligatoire($query)
    {
        return $query->where('est_obligatoire', 1);
    }

    /**
     * Scope pour exclure les types supprimés
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
