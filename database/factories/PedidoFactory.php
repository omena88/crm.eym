<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\User;
use App\Models\Pedido;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pedido>
 */
class PedidoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $vendedores = User::where('rol', 'vendedor')->pluck('id');
        $fechaPedido = $this->faker->dateTimeBetween('-6 months', 'now');

        return [
            'cliente_id' => Cliente::factory(),
            'user_id' => $this->faker->randomElement($vendedores),
            'cotizacion_id' => null, // Se puede asociar después si es necesario
            'codigo' => 'PED-' . $this->faker->unique()->numberBetween(1000, 9999),
            'fecha_pedido' => $fechaPedido,
            'fecha_entrega_prevista' => $this->faker->dateTimeBetween($fechaPedido, '+45 days'),
            'monto_total' => $this->faker->randomFloat(2, 150, 30000),
            'estado' => $this->faker->randomElement(['pendiente', 'en_proceso', 'completado', 'cancelado']),
            'direccion_envio' => $this->faker->address,
        ];
    }
}
