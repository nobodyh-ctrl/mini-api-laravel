<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Jugador',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'nombre', type: 'string', example: 'Lionel'),
        new OA\Property(property: 'apellido', type: 'string', example: 'Messi'),
        new OA\Property(property: 'fecha_nacimiento', type: 'string', format: 'date', example: '1987-06-24'),
        new OA\Property(property: 'posicion', type: 'string', example: 'Delantero'),
        new OA\Property(property: 'numero_camiseta', type: 'integer', example: 10),
        new OA\Property(property: 'nacionalidad', type: 'string', example: 'Argentina'),
    ]
)]
class JugadorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'fecha_nacimiento' => $this->fecha_nacimiento->toDateString(),
            'posicion' => $this->posicion,
            'numero_camiseta' => $this->numero_camiseta,
            'nacionalidad' => $this->nacionalidad,
        ];
    }
}
