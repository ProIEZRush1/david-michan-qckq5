<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class NumeroTelefonico extends Model
{
    protected $table = 'numeros_telefonicos';

    protected $fillable = [
        'numero',
        'lada',
        'estado',
        'asignado_at',
    ];

    protected $casts = [
        'asignado_at' => 'datetime',
    ];

    public function pedido(): HasOne
    {
        return $this->hasOne(Pedido::class);
    }
}
