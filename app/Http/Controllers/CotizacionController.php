<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Visita;
use App\Models\User;
use App\Models\Producto;

class CotizacionController extends Controller
{
    /**
     * Muestra una lista de visitas para iniciar una cotización.
     */
    public function index()
    {
        $visitas = Visita::with('cliente')
            ->orderBy('fecha_programada', 'desc')
            ->take(20)
            ->get();

        return Inertia::render('Cotizaciones/Index', [
            'visitas' => $visitas,
        ]);
    }

    /**
     * Muestra el flujo de una cotización para una visita específica.
     * Aquí es donde simulamos todo.
     */
    public function show(Request $request, Visita $visita)
    {
        $visita->loadMissing('cliente'); // Asegurar que la relación se carga

        // El estado se pasa por la URL para simular el flujo.
        $estadoActual = $request->input('estado', 'Borrador');
        
        // Log para debug
        \Log::info('Mostrando cotización', [
            'visita_id' => $visita->id,
            'estado_actual' => $estadoActual,
            'user' => auth()->user()->name,
            'user_role' => auth()->user()->rol
        ]);
        
        $productos = Producto::with('documentos')->inRandomOrder()->take(3)->get();

        $productosDemo = $productos->map(function($producto) {
            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'precio_base' => $producto->precio_base,
                'cantidad' => rand(1, 5),
                'documentos' => $producto->documentos,
            ];
        });
        
        $cotizacionDemo = [
            'id' => 123, // ID de demo
            'visita_id' => $visita->id,
            'vendedor' => auth()->user(),
            'gerente' => User::where('rol', 'gerente')->first() ?? auth()->user(), // Un gerente de demo
            'estado' => $estadoActual,
            'total' => $productosDemo->sum(function($p) {
                return $p['precio_base'] * $p['cantidad'];
            }),

            'productos' => $productosDemo,
        ];

        $props = [
            'visita' => $visita,
            'cotizacion' => $cotizacionDemo,
            'userRole' => auth()->user()->rol,
        ];

        return Inertia::render('Cotizaciones/Show', $props);
    }

    /**
     * Simula un cambio de estado en la cotización.
     */
    public function simularAccion(Request $request)
    {
        // Log para debug ANTES de validar
        \Log::info('Datos recibidos en simularAccion', [
            'all_data' => $request->all(),
            'visita_id' => $request->get('visita_id'),
            'nuevo_estado' => $request->get('nuevo_estado'),
            'user' => auth()->user()->name
        ]);
        
        $request->validate([
            'visita_id' => 'required|integer|exists:visitas,id',
            'nuevo_estado' => 'required|string',
        ]);
        
        // Log para debug DESPUÉS de validar
        \Log::info('Simulando acción de cotización', [
            'visita_id' => $request->visita_id,
            'nuevo_estado' => $request->nuevo_estado,
            'user' => auth()->user()->name
        ]);
        
        // Simplemente redirigimos a la misma página pero con el nuevo estado en la URL.
        return redirect()->route('cotizaciones.show', [
            'visita' => $request->visita_id,
            'estado' => $request->nuevo_estado,
        ])->with('success', 'Estado actualizado correctamente (Demo)');
    }
}
