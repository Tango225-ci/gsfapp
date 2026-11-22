<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompteBancaire extends Model
{
    public $timestamps = false;

    protected $table = 'comptes_bancaires';

    protected $fillable = [
        'banque',
        'numero_compte',
        'intitule',
        'solde_initial',
        'date_solde_initial',
        'actif',
        'valeursupprimee',
    ];

    protected $casts = [
        'solde_initial'      => 'decimal:2',
        'date_solde_initial' => 'date:Y-m-d',
        'actif'              => 'boolean',
        'created_at'         => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->created_at = now();
        });
    }

    public function scopeNotDeleted($query)
    {
        return $query->where(function ($q) {
            $q->where('valeursupprimee', '!=', 1)->orWhereNull('valeursupprimee');
        });
    }

    public function operations()
    {
        return $this->hasMany(OperationBancaire::class, 'id_compte');
    }

    /**
     * Solde = solde initial + crédits validés − débits validés.
     * Seules les opérations VALIDE impactent le solde.
     */
    public function soldeActuel(): float
    {
        $mouvements = $this->operations()
            ->where('statut', 'VALIDE')
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'CREDIT' THEN montant ELSE -montant END), 0) AS net")
            ->value('net');

        return (float) $this->solde_initial + (float) $mouvements;
    }

    /**
     * Montant encore disponible pour une sortie : solde validé moins les
     * sorties déjà saisies et en attente de validation.
     */
    public function disponible(?int $exceptOperationId = null): float
    {
        $sortiesEnAttente = $this->operations()
            ->where('statut', 'EN_ATTENTE')
            ->where('type', 'DEBIT')
            ->when($exceptOperationId, fn ($q) => $q->where('id', '!=', $exceptOperationId))
            ->sum('montant');

        return $this->soldeActuel() - (float) $sortiesEnAttente;
    }
}
