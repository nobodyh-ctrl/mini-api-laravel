<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jugador extends Model
{
    /** @use HasFactory<\Database\Factories\JugadorFactory> */
    use HasFactory;

    protected $table = 'jugadores';

    protected $fillable = [
        'nombre',
        'apellido',
        'fecha_nacimiento',
        'posicion',
        'numero_camiseta',
        'nacionalidad',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'numero_camiseta' => 'integer',
        ];
    }
}
