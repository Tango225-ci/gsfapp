<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
     protected $fillable = [
        'nom_prenoms',
        'contact',
        'email',
        'adresse',
        'piece_identite',
        'numero_piece',
        'date_validite',
        'statut',
        'valeursupprimee'
     ];
}
