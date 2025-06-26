<?php

namespace Database\Factories;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Documento>
 */
class DocumentoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Simular la creación de un archivo PDF falso
        Storage::makeDirectory('public/productos/documentos');
        $fileName = Str::random(20) . '.pdf';
        $filePath = 'productos/documentos/' . $fileName;
        Storage::disk('public')->put($filePath, 'Este es un PDF de prueba.');

        return [
            'producto_id' => Producto::factory(),
            'nombre' => $this->faker->randomElement(['Ficha Técnica', 'Hoja de Seguridad', 'Certificado de Calidad']),
            'ruta' => $filePath,
            'tipo_archivo' => 'pdf',
        ];
    }
}
