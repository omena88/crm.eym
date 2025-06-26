<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Visita;
use Carbon\Carbon;
use App\Models\Contacto;
use App\Models\Cotizacion;
use App\Models\Pedido;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear roles y usuarios de prueba
        $this->call(UserSeeder::class);
        
        // Crear vendedores
        $vendedores = User::factory(5)->create(['rol' => 'vendedor']);

        // Crear clientes y asociarles datos relacionados
        Cliente::factory(20)
            ->has(Contacto::factory()->count(3), 'contactos')
            ->has(Visita::factory()->count(5), 'visitas')
            ->has(Cotizacion::factory()->count(3), 'cotizaciones')
            ->create()
            ->each(function ($cliente) use ($vendedores) {
                // Asignar vendedor
                $cliente->update(['vendedor_id' => $vendedores->random()->id]);

                // Crear pedidos para algunas cotizaciones aprobadas
                $cotizacionesAprobadas = $cliente->cotizaciones()->where('estado', 'aprobada')->get();
                foreach ($cotizacionesAprobadas as $cotizacion) {
                    if (rand(0, 1)) { // 50% de probabilidad de crear un pedido
                        Pedido::factory()->create([
                            'cliente_id' => $cliente->id,
                            'user_id' => $cotizacion->user_id,
                            'cotizacion_id' => $cotizacion->id,
                            'monto_total' => $cotizacion->monto_total,
                            'direccion_envio' => $cliente->direccion,
                        ]);
                    }
                }
            });

        // Crear visitas de prueba para esta semana
        $hoy = Carbon::now();
        $lunes = $hoy->copy()->startOfWeek(Carbon::MONDAY);
        
        for ($i = 0; $i < 6; $i++) {
            $fecha = $lunes->copy()->addDays($i);
            $clienteSeleccionado = Cliente::inRandomOrder()->first();
            
            Visita::create([
                'user_id' => User::inRandomOrder()->first()->id,
                'cliente_id' => $clienteSeleccionado->id,
                'titulo' => 'Visita a ' . $clienteSeleccionado->razon_social,
                'descripcion' => 'Reunión comercial programada',
                'objetivos' => 'Presentar propuesta comercial y cerrar venta',
                'fecha_programada' => $fecha,
                'turno' => $i % 2 == 0 ? 'mañana' : 'tarde',
                'tipo' => 'comercial',
                'estado' => 'programada',
                'semana' => $fecha->weekOfYear,
                'año' => $fecha->year,
            ]);
        }

        // Crear algunas visitas en diferentes estados para mostrar el flujo
        Visita::create([
            'user_id' => User::inRandomOrder()->first()->id,
            'cliente_id' => Cliente::inRandomOrder()->first()->id,
            'titulo' => 'Visita enviada para aprobación',
            'descripcion' => 'Visita pendiente de aprobación del gerente',
            'objetivos' => 'Cerrar contrato anual',
            'fecha_programada' => $hoy->copy()->addDays(7),
            'turno' => 'mañana',
            'tipo' => 'comercial',
            'estado' => 'pendiente', // Enviada para aprobación
            'fecha_envio_aprobacion' => $hoy->copy()->subDays(1),
            'semana' => $hoy->copy()->addDays(7)->weekOfYear,
            'año' => $hoy->year,
        ]);

        $this->command->info('¡Datos de prueba creados exitosamente!');
        $this->command->info('Usuarios de prueba:');
        $this->command->info('- admin@crm.local / password123 (Admin)');
        $this->command->info('- juan.perez@crm.local / password123 (Vendedor)');
        $this->command->info('- maria.rodriguez@crm.local / password123 (Gerente)');
        $this->command->info('- carlos.mendez@crm.local / password123 (Calidad)');

        $this->call(ProductoSeeder::class);
    }
}
