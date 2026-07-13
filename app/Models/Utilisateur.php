<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use App\Notifications\ResetPasswordNotification;

class Utilisateur extends Authenticatable
{
    use SoftDeletes, Notifiable;

    protected $table = 'utilisateurs';
    protected $primaryKey = 'id_utilisateur';

    protected $fillable = [
        'nom', 'login', 'email', 'password', 'role', 'statut', 'must_change_password',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'statut'              => 'boolean',
        'must_change_password' => 'boolean',
    ];

    public function getIdAttribute(): int
    {
        return $this->id_utilisateur;
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'ADMIN'     => 'Administrateur',
            'ASSISTANT' => 'Assistant',
            'DIRECTEUR' => 'Directeur',
            default     => $this->role,
        };
    }

    public function getStatutLabelAttribute(): string
    {
        return $this->statut ? 'Actif' : 'Inactif';
    }

    public function getDateCreationAttribute(): string
    {
        return $this->created_at?->format('d/m/Y') ?? '';
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
