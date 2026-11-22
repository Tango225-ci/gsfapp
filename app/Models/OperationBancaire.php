<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class OperationBancaire extends Model
{
    protected $table = 'operations_bancaires';

    protected $fillable = [
        'id_compte',
        'type',
        'categorie',
        'libelle',
        'montant',
        'date_operation',
        'reference',
        'note',
        'statut',
        'motif_rejet',
        'id_utilisateur',
        'valide_par',
        'valide_le',
    ];

    protected $casts = [
        'montant'        => 'decimal:2',
        'date_operation' => 'date:Y-m-d',
        'valide_le'      => 'datetime',
    ];

    protected $appends = [
        'categorie_label',
        'date_operation_formatee',
    ];

    public const CATEGORIES = [
        'CREDIT' => [
            'DEPOT_ESPECES'     => 'Dépôt d\'espèces (encaissements)',
            'VIREMENT_RECU'     => 'Virement reçu',
            'CHEQUE_DEPOSE'     => 'Remise de chèque',
            'MOBILE_MONEY'      => 'Reversement Mobile Money',
            'AUTRE_ENTREE'      => 'Autre entrée',
        ],
        'DEBIT' => [
            'VERSEMENT_PROPRIETAIRE' => 'Versement propriétaire',
            'RETRAIT_ESPECES'        => 'Retrait d\'espèces',
            'VIREMENT_EMIS'          => 'Virement émis',
            'CHEQUE_EMIS'            => 'Chèque émis',
            'FRAIS_BANCAIRES'        => 'Frais bancaires / agios',
            'DEPENSE'                => 'Dépense / charge',
            'AUTRE_SORTIE'           => 'Autre sortie',
        ],
    ];

    public static function categoriesKeys(): array
    {
        return array_merge(array_keys(self::CATEGORIES['CREDIT']), array_keys(self::CATEGORIES['DEBIT']));
    }

    public function compte()
    {
        return $this->belongsTo(CompteBancaire::class, 'id_compte');
    }

    public function auteur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur', 'id_utilisateur');
    }

    public function validateur()
    {
        return $this->belongsTo(Utilisateur::class, 'valide_par', 'id_utilisateur');
    }

    public function getCategorieLabelAttribute()
    {
        return self::CATEGORIES[$this->type][$this->categorie] ?? $this->categorie;
    }

    public function getDateOperationFormateeAttribute()
    {
        return $this->date_operation ? Carbon::parse($this->date_operation)->format('d/m/Y') : null;
    }
}
