<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cliente>
 */
class ClienteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $vendedorIds = User::where('rol', 'vendedor')->pluck('id');

        return [
            'ruc' => '20' . $this->faker->unique()->numerify('#########'),
            'razon_social' => $this->faker->company . ' ' . $this->faker->companySuffix,
            'nombre_comercial' => $this->faker->company,
            'sector' => $this->faker->randomElement(['Minería', 'Construcción', 'Tecnología', 'Retail', 'Servicios', 'Industrial']),
            'estado' => $this->faker->randomElement(['potencial', 'visitado', 'cotizado', 'cliente', 'inactivo']),
            'user_id' => $this->faker->randomElement($vendedorIds),
            'telefono' => $this->faker->phoneNumber,
            'website' => 'https://www.' . $this->faker->domainName,
            'direccion' => $this->faker->address,
            'ultima_actividad' => $this->faker->dateTimeThisYear(),
            'valor_potencial' => $this->faker->randomFloat(2, 5000, 100000),
            'probabilidad_cierre' => $this->faker->numberBetween(10, 90),
        ];
    }
}
