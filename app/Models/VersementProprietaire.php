<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class VersementProprietaire extends Model
{
    public $timestamps = false;

    protected $table = 'versements_proprietaires';

    protected $primaryKey = 'id_versement';

    protected $fillable = [
        'id_proprietaire',
        'montant',
        'date_versement',
        'reference',
        'mode_paiement',
        'banque',
        'operateur',
        'reference_cheque',
        'note',
        'id_utilisateur',
        'created_at',
    ];

    protected $casts = [
        'montant'         => 'decimal:2',
        'date_versement'  => 'date',
        'created_at'      => 'datetime',
    ];

    protected $appends = [
        'montant_formate',
        'date_versement_formatee',
        'mode_paiement_label',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->created_at = now();
            if (!$model->reference) {
                $model->reference = 'VRS-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
            }
        });
    }

    public function proprietaire()
    {
        return $this->belongsTo(ProprietaireTerrien::class, 'id_proprietaire', 'id_proprietaire');
    }

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur', 'id_utilisateur');
    }

    public function getMontantFormateAttribute()
    {
        return number_format(floatval($this->montant), 0, ',', ' ') . ' FCFA';
    }

    public function getDateVersementFormateeAttribute()
    {
        return $this->date_versement ? Carbon::parse($this->date_versement)->format('d/m/Y') : null;
    }

    public function getModesPaiementLabels(): array
    {
        return [
            'ESPECES'      => 'Espèces',
            'VIREMENT'     => 'Virement bancaire',
            'CHEQUE'       => 'Chèque',
            'MOBILE_MONEY' => 'Mobile Money',
        ];
    }

    public function getModePaiementLabelAttribute()
    {
        return $this->getModesPaiementLabels()[$this->mode_paiement] ?? $this->mode_paiement;
    }
}
