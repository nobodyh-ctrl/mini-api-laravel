<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Jugador>
 */
class JugadorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => fake()->firstName('male'),
            'apellido' => fake()->lastName(),
            'fecha_nacimiento' => fake()->dateTimeBetween('-38 years', '-17 years')->format('Y-m-d'),
            'posicion' => fake()->randomElement(['Arquero', 'Defensor', 'Mediocampista', 'Delantero']),
            'numero_camiseta' => fake()->unique()->numberBetween(1, 99),
            'nacionalidad' => fake()->randomElement(['Argentina', 'Uruguay', 'Paraguay', 'Chile', 'Colombia']),
        ];
    }
}
