<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cotizacion>
 */
class CotizacionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Cotizacion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fechaEmision = $this->faker->dateTimeBetween('-1 year', 'now');
        $vendedores = User::where('rol', 'vendedor')->pluck('id');

        return [
            'cliente_id' => Cliente::factory(),
            'user_id' => $this->faker->randomElement($vendedores),
            'codigo' => 'COT-' . $this->faker->unique()->numberBetween(1000, 9999),
            'fecha_emision' => $fechaEmision,
            'fecha_vencimiento' => $this->faker->dateTimeBetween($fechaEmision, '+30 days'),
            'monto_total' => $this->faker->randomFloat(2, 100, 20000),
            'estado' => $this->faker->randomElement(['borrador', 'enviada', 'aprobada', 'rechazada', 'vencida']),
            'observaciones' => $this->faker->sentence(),
        ];
    }
}
