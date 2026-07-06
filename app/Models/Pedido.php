<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pedido extends Model
{
    protected $table = 'pedidos';

    protected $fillable = [
        'bot_contact_id',
        'cliente_id',
        'plan_id',
        'numero_telefonico_id',
        'cliente',
        'telefono',
        'estado',
        'monto',
        'notas',
        'pagado_at',
        'numero_asignado_at',
        'entregado_at',
    ];

    protected $casts = [
        'monto' => 'int',
        'pagado_at' => 'datetime',
        'numero_asignado_at' => 'datetime',
        'entregado_at' => 'datetime',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function botContact(): BelongsTo
    {
        return $this->belongsTo(BotContact::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function numeroTelefonico(): BelongsTo
    {
        return $this->belongsTo(NumeroTelefonico::class);
    }
}
