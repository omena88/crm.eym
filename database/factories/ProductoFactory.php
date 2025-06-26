<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Producto>
 */
class ProductoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->unique()->randomElement([
                'Aditivo Superplastificante para Concreto',
                'Impermeabilizante Acrílico para Techos',
                'Sellador de Juntas de Poliuretano',
                'Mortero de Reparación Estructural',
                'Epóxico para Anclajes y Adhesión',
            ]),
            'codigo' => 'PROD-' . $this->faker->unique()->numberBetween(1000, 9999),
            'descripcion' => $this->faker->sentence(20),
            'precio_base' => $this->faker->randomFloat(2, 50, 500),
            'unidad' => $this->faker->randomElement(['Galón', 'Saco 25kg', 'Cartucho 300ml', 'Kit']),
        ];
    }
}
