<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VilleDocumentFrais extends Model
{
    /**
     * Activer les timestamps automatiques
     */
    public $timestamps = true;

    /**
     * Le nom de la table associée au modèle.
     */
    protected $table = 'ville_document_frais';

    /**
     * La clé primaire de la table.
     */
    protected $primaryKey = 'id_frais';

    /**
     * Les attributs qui sont assignables en masse.
     */
    protected $fillable = [
        'ville',
        'id_document_type',
        'montant',
        'valeursupprimee'
    ];

    /**
     * Les attributs qui doivent être castés.
     */
    protected $casts = [
        'montant' => 'decimal:2',
        'valeursupprimee' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
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
        'montant_formatted'
    ];

    /**
     * Relation avec le type de document
     */
    public function documentType()
    {
        return $this->belongsTo(DocumentType::class, 'id_document_type', 'id_document_type');
    }

    /**
     * Relation avec les documents de contrat
     */
    public function contratsDocuments()
    {
        return $this->hasMany(ContratDocument::class, 'id_frais', 'id_frais')
                    ->where('valeursupprimee', 0);
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
     * Scope pour exclure les frais supprimés
     */
    public function scopeNotDeleted($query)
    {
        return $query->where(function($q) {
            $q->where('valeursupprimee', '!=', 1)
              ->orWhereNull('valeursupprimee');
        });
    }

    /**
     * Scope pour une ville spécifique
     */
    public function scopeParVille($query, $ville)
    {
        return $query->where('ville', $ville);
    }

    /**
     * Suppression logique
     */
    public function delete()
    {
        $this->valeursupprimee = 1;
        return $this->save();
    }

    /**
     * Récupérer le montant pour un type de document et une ville
     */
    public static function getMontant($ville, $idDocumentType)
    {
        $frais = self::where('ville', $ville)
                     ->where('id_document_type', $idDocumentType)
                     ->where('valeursupprimee', 0)
                     ->first();

        return $frais ? $frais->montant : 0;
    }
}
