<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Cliente;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contacto>
 */
class ContactoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cliente_id' => Cliente::factory(),
            'nombre' => $this->faker->firstName,
            'apellidos' => $this->faker->lastName,
            'puesto' => $this->faker->jobTitle,
            'email' => $this->faker->unique()->safeEmail,
            'celular' => $this->faker->numerify('9########'),
            'es_contacto_principal' => false, // Se manejará en el seeder
        ];
    }
}
