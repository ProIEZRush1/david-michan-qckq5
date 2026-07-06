<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $table = 'faqs';

    protected $fillable = [
        'pregunta',
        'respuesta',
        'palabras_clave',
        'categoria',
        'activo',
        'orden',
    ];

    protected $casts = [
        'activo' => 'bool',
        'orden' => 'int',
    ];
}
