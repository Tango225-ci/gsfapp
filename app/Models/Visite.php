<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visite extends Model
{
    // DÉSACTIVER LES TIMESTAMPS (TRÈS IMPORTANT)
    public $timestamps = false;

    protected $table = 'visites';
    protected $primaryKey = 'id_visite';

    protected $fillable = [
        'nom_visiteur',
        'contact_visiteur',
        'adresse_visiteur',
        'date_visite',
        'heure_arrivee',
        'heure_depart',
        'motif',
        'date_create',
        'valeursupprimee'
    ];
}
