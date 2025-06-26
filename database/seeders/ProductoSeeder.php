<?php

namespace Database\Seeders;

use App\Models\Canal;
use App\Models\Producto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar directorio de documentos de prueba
        Storage::disk('public')->deleteDirectory('productos/documentos');

        // Crear Canales
        $canalesData = ['Distribuidor', 'Retail', 'Constructoras', 'Gobierno'];
        $canales = collect();
        foreach ($canalesData as $canalNombre) {
            $canales->push(Canal::create(['nombre' => $canalNombre]));
        }

        // Crear 5 Productos
        Producto::factory(5)->create()->each(function ($producto) use ($canales) {
            // Asignar precios por canal
            foreach ($canales as $canal) {
                // Precio con una variación sobre el precio base
                $variacion = 1 + fake()->randomFloat(2, -0.15, 0.25); // -15% a +25%
                $precioCanal = $producto->precio_base * $variacion;

                $producto->canales()->attach($canal->id, ['precio' => $precioCanal]);
            }

            // Crear 1 o 2 documentos por producto
            \App\Models\Documento::factory(rand(1, 2))->create([
                'producto_id' => $producto->id,
            ]);
        });
    }
}
