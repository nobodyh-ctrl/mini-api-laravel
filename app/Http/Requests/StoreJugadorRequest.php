<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'JugadorInput',
    required: ['nombre', 'apellido', 'fecha_nacimiento', 'posicion', 'numero_camiseta', 'nacionalidad'],
    properties: [
        new OA\Property(property: 'nombre', type: 'string', maxLength: 255, example: 'Lionel'),
        new OA\Property(property: 'apellido', type: 'string', maxLength: 255, example: 'Messi'),
        new OA\Property(property: 'fecha_nacimiento', type: 'string', format: 'date', example: '1987-06-24'),
        new OA\Property(property: 'posicion', type: 'string', enum: StoreJugadorRequest::POSICIONES, example: 'Delantero'),
        new OA\Property(property: 'numero_camiseta', type: 'integer', minimum: 1, maximum: 99, example: 10),
        new OA\Property(property: 'nacionalidad', type: 'string', maxLength: 100, example: 'Argentina'),
    ]
)]
class StoreJugadorRequest extends FormRequest
{
    public const POSICIONES = ['Arquero', 'Defensor', 'Mediocampista', 'Delantero'];

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'fecha_nacimiento' => ['required', 'date_format:Y-m-d', 'before:today'],
            'posicion' => ['required', 'in:'.implode(',', self::POSICIONES)],
            'numero_camiseta' => ['required', 'integer', 'between:1,99', 'unique:jugadores,numero_camiseta'],
            'nacionalidad' => ['required', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe ser un texto.',
            'max' => 'El campo :attribute no puede superar los :max caracteres.',
            'date_format' => 'El campo :attribute debe tener el formato AAAA-MM-DD.',
            'before' => 'El campo :attribute debe ser una fecha anterior a hoy.',
            'in' => 'El campo :attribute debe ser uno de: :values.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'between' => 'El campo :attribute debe estar entre :min y :max.',
            'unique' => 'El :attribute ya está asignado a otro jugador.',
        ];
    }

    public function attributes(): array
    {
        return [
            'fecha_nacimiento' => 'fecha de nacimiento',
            'posicion' => 'posición',
            'numero_camiseta' => 'número de camiseta',
        ];
    }
}
