<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\ClientResetPasswordNotification;

class Client extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'reference_client',
        'nom_prenoms',
        'contact',
        'email',
        'adresse',
        'piece_identite',
        'numero_piece',
        'date_validite',
        'statut',
        'valeursupprimee',
        'password',
        'must_change_password',
        'portal_account_created_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'must_change_password' => 'boolean',
        'portal_account_created_at' => 'datetime',
        'date_validite' => 'date',
    ];

    protected $appends = ['reference_display'];

    /**
     * Contrats du client (utilisé notamment par le portail client).
     */
    public function contrats()
    {
        return $this->hasMany(Contrat::class, 'id_client', 'id')
                    ->where('valeursupprimee', 0);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ClientResetPasswordNotification($token));
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->reference_client)) {
                $model->reference_client = self::generateReference();
            }
        });
    }

    public static function generateReference(): string
    {
        $last = self::where('reference_client', 'LIKE', 'CLI%')
            ->orderByRaw('CAST(SUBSTRING(reference_client, 4) AS UNSIGNED) DESC')
            ->value('reference_client');

        $seq = $last ? intval(substr($last, 3)) + 1 : 1;

        return 'CLI' . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    public function getReferenceDisplayAttribute(): string
    {
        return $this->reference_client ?? ('CLI' . str_pad($this->id, 5, '0', STR_PAD_LEFT));
    }
}
