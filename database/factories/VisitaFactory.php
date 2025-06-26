<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\User;
use App\Models\Visita;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Visita>
 */
class VisitaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Visita::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $vendedores = User::where('rol', 'vendedor')->pluck('id');
        $fechaProgramada = $this->faker->dateTimeBetween('-6 months', '+1 month');

        return [
            'cliente_id' => Cliente::factory(),
            'user_id' => $this->faker->randomElement($vendedores),
            'titulo' => 'Visita ' . $this->faker->company,
            'descripcion' => $this->faker->sentence,
            'objetivos' => $this->faker->text(150),
            'fecha_programada' => $fechaProgramada,
            'estado' => $this->faker->randomElement(['programada', 'realizada', 'cancelada', 'aprobada']),
            'tipo' => $this->faker->randomElement(['comercial', 'tecnica', 'seguimiento']),
            'prioridad' => $this->faker->randomElement(['baja', 'media', 'alta']),
            'resultado' => $this->faker->text,
        ];
    }
}
