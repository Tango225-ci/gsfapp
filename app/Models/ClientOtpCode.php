<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientOtpCode extends Model
{
    protected $fillable = [
        'id_client',
        'code_hash',
        'expires_at',
        'consumed_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'consumed_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'id_client', 'id');
    }
}
