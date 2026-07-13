<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ProprietaireTerrien extends Model
{
    public $timestamps = false;

    protected $table = 'proprietaires_terriens';

    protected $primaryKey = 'id_proprietaire';

    protected $fillable = [
        'nom',
        'contact',
        'email',
        'adresse',
        'note',
        'valeursupprimee',
        'created_at',
    ];

    protected $casts = [
        'valeursupprimee' => 'boolean',
        'created_at'      => 'datetime',
    ];

    protected $attributes = [
        'valeursupprimee' => false,
    ];

    protected $appends = [
        'date_creation_formatee',
        'stats',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->created_at = now();
        });
    }

    public function lots()
    {
        return $this->hasMany(Lot::class, 'id_proprietaire', 'id_proprietaire')
                    ->where(function ($q) {
                        $q->where('valeursupprimee', '!=', 1)->orWhereNull('valeursupprimee');
                    });
    }

    public function versements()
    {
        return $this->hasMany(VersementProprietaire::class, 'id_proprietaire', 'id_proprietaire')
                    ->orderBy('date_versement', 'desc');
    }

    public function scopeNotDeleted($query)
    {
        return $query->where(function ($q) {
            $q->where('valeursupprimee', '!=', 1)->orWhereNull('valeursupprimee');
        });
    }

    public function delete()
    {
        $this->valeursupprimee = true;
        return $this->save();
    }

    public function getDateCreationFormateeAttribute()
    {
        return $this->created_at ? Carbon::parse($this->created_at)->format('d/m/Y') : null;
    }

    public function getStatsAttribute()
    {
        $lots = $this->lots()->withCount([])->get(['id_lot', 'statut', 'prix_achat', 'prix_comptant', 'prix_echelonne']);

        $totalPrixAchat  = $lots->sum('prix_achat');
        $totalVerse      = $this->versements()->sum('montant');
        $resteAVerser    = $totalPrixAchat - $totalVerse;

        $parStatut = $lots->groupBy('statut')->map->count();

        return [
            'nb_lots'         => $lots->count(),
            'disponible'      => $parStatut->get('DISPONIBLE', 0),
            'reserve'         => $parStatut->get('RESERVE', 0),
            'vendu'           => $parStatut->get('VENDU', 0),
            'total_prix_achat'=> floatval($totalPrixAchat),
            'total_verse'     => floatval($totalVerse),
            'reste_a_verser'  => floatval($resteAVerser),
        ];
    }
}
