<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Pedido;
use App\Models\Cotizacion;
use App\Models\Visita;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\User;

class PedidoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Datos demo de pedidos
        $pedidosDemo = collect([
            [
                'id' => 1,
                'numero' => 'PED-2025-001',
                'cliente' => ['razon_social' => 'Constructora ABC S.A.'],
                'vendedor' => ['name' => 'Vendedor Demo'],
                'total' => 2500000,
                'estado' => 'borrador',
                'fecha_creacion' => now()->subDays(2)->format('Y-m-d'),
                'productos_count' => 3
            ],
            [
                'id' => 2,
                'numero' => 'PED-2025-002',
                'cliente' => ['razon_social' => 'Empresa XYZ Ltda.'],
                'vendedor' => ['name' => 'Vendedor Demo'],
                'total' => 1800000,
                'estado' => 'revision',
                'fecha_creacion' => now()->subDays(1)->format('Y-m-d'),
                'productos_count' => 2
            ],
            [
                'id' => 3,
                'numero' => 'PED-2025-003',
                'cliente' => ['razon_social' => 'Industrias 123 S.A.'],
                'vendedor' => ['name' => 'Vendedor Demo'],
                'total' => 3200000,
                'estado' => 'aprobado',
                'fecha_creacion' => now()->subDays(3)->format('Y-m-d'),
                'productos_count' => 4
            ],
            [
                'id' => 4,
                'numero' => 'PED-2025-004',
                'cliente' => ['razon_social' => 'Comercial DEF S.A.'],
                'vendedor' => ['name' => 'Vendedor Demo'],
                'total' => 950000,
                'estado' => 'enviado',
                'fecha_creacion' => now()->subDays(5)->format('Y-m-d'),
                'productos_count' => 1
            ]
        ]);

        // Estadísticas
        $stats = [
            'total' => $pedidosDemo->count(),
            'borrador' => $pedidosDemo->where('estado', 'borrador')->count(),
            'revision' => $pedidosDemo->where('estado', 'revision')->count(),
            'aprobado' => $pedidosDemo->where('estado', 'aprobado')->count(),
            'enviado' => $pedidosDemo->where('estado', 'enviado')->count(),
            'total_valor' => $pedidosDemo->sum('total')
        ];

        return Inertia::render('Pedidos/Index', [
            'pedidos' => $pedidosDemo,
            'stats' => $stats,
            'userRole' => $user->rol,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $cotizacionId = $request->get('cotizacion_id');
        $cotizacion = null;
        
        if ($cotizacionId) {
            // Simular datos de cotización
            $cotizacion = [
                'id' => $cotizacionId,
                'cliente' => ['razon_social' => 'Cliente Demo'],
                'productos' => []
            ];
        }

        // Datos demo para evitar consultas lentas
        $clientesDemo = collect([
            ['id' => 1, 'razon_social' => 'Constructora ABC S.A.', 'nombre_comercial' => 'ABC'],
            ['id' => 2, 'razon_social' => 'Empresa XYZ Ltda.', 'nombre_comercial' => 'XYZ'],
            ['id' => 3, 'razon_social' => 'Industrias 123 S.A.', 'nombre_comercial' => '123'],
            ['id' => 4, 'razon_social' => 'Comercial DEF S.A.', 'nombre_comercial' => 'DEF'],
            ['id' => 5, 'razon_social' => 'Servicios GHI Ltda.', 'nombre_comercial' => 'GHI'],
        ]);

        $productosDemo = collect([
            ['id' => 1, 'nombre' => 'Cemento Portland', 'descripcion' => 'Cemento de alta calidad para construcción', 'precio_base' => 25000],
            ['id' => 2, 'nombre' => 'Arena Fina', 'descripcion' => 'Arena fina para mezclas de construcción', 'precio_base' => 18000],
            ['id' => 3, 'nombre' => 'Grava Gruesa', 'descripcion' => 'Grava para base de construcción', 'precio_base' => 22000],
            ['id' => 4, 'nombre' => 'Ladrillos', 'descripcion' => 'Ladrillos cerámicos rojos', 'precio_base' => 450],
            ['id' => 5, 'nombre' => 'Varillas de Acero', 'descripcion' => 'Varillas corrugadas 12mm', 'precio_base' => 3500],
        ]);

        return Inertia::render('Pedidos/Create', [
            'cotizacion' => $cotizacion,
            'clientes' => $clientesDemo,
            'productos' => $productosDemo,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // MODO DEMO
        return redirect()->route('pedidos.index')
            ->with('success', 'Pedido creado exitosamente (Modo Demo)');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $user = auth()->user();
        
        // El estado se pasa por la URL para simular el flujo
        $estadoActual = $request->get('estado', 'borrador');
        
        // Datos demo de productos para evitar consultas a la base de datos
        $productosDemo = collect([
            [
                'id' => 1,
                'nombre' => 'Cemento Portland',
                'descripcion' => 'Cemento de alta calidad para construcción',
                'precio_base' => 25000,
                'cantidad' => 2,
                'documentos' => []
            ],
            [
                'id' => 2,
                'nombre' => 'Arena Fina',
                'descripcion' => 'Arena fina para mezclas de construcción',
                'precio_base' => 18000,
                'cantidad' => 3,
                'documentos' => []
            ],
            [
                'id' => 3,
                'nombre' => 'Grava Gruesa',
                'descripcion' => 'Grava para base de construcción',
                'precio_base' => 22000,
                'cantidad' => 1,
                'documentos' => []
            ]
        ]);
        
        $pedidoDemo = [
            'id' => $id,
            'numero' => 'PED-2025-' . str_pad($id, 3, '0', STR_PAD_LEFT),
            'cliente' => ['razon_social' => 'Cliente Demo S.A.'],
            'vendedor' => auth()->user(),
            'estado' => $estadoActual,
            'total' => $productosDemo->sum(function($p) {
                return $p['precio_base'] * $p['cantidad'];
            }),
            'fecha_creacion' => now()->subDays(rand(1, 5))->format('Y-m-d'),
            'productos' => $productosDemo,
            'observaciones' => $estadoActual === 'revision' ? 'Revisar cantidades del producto principal antes de aprobar.' : null,
        ];

        return Inertia::render('Pedidos/Show', [
            'pedido' => $pedidoDemo,
            'userRole' => $user->rol,
        ]);
    }

    /**
     * Simula un cambio de estado en el pedido.
     */
    public function simularAccion(Request $request)
    {
        $request->validate([
            'pedido_id' => 'required|integer',
            'nuevo_estado' => 'required|string',
        ]);
        
        // Log para debug
        \Log::info('Simulando acción de pedido', [
            'pedido_id' => $request->pedido_id,
            'nuevo_estado' => $request->nuevo_estado,
            'user' => auth()->user()->name
        ]);
        
        return redirect()->route('pedidos.show', [
            'pedido' => $request->pedido_id,
            'estado' => $request->nuevo_estado,
        ])->with('success', 'Estado del pedido actualizado (Modo Demo)');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user = auth()->user();
        
        if ($user->rol !== 'vendedor') {
            abort(403, 'Solo los vendedores pueden editar pedidos');
        }

        // Datos demo para evitar consultas lentas
        $clientesDemo = collect([
            ['id' => 1, 'razon_social' => 'Constructora ABC S.A.', 'nombre_comercial' => 'ABC'],
            ['id' => 2, 'razon_social' => 'Empresa XYZ Ltda.', 'nombre_comercial' => 'XYZ'],
            ['id' => 3, 'razon_social' => 'Industrias 123 S.A.', 'nombre_comercial' => '123'],
            ['id' => 4, 'razon_social' => 'Comercial DEF S.A.', 'nombre_comercial' => 'DEF'],
            ['id' => 5, 'razon_social' => 'Servicios GHI Ltda.', 'nombre_comercial' => 'GHI'],
        ]);

        $productosDemo = collect([
            ['id' => 1, 'nombre' => 'Cemento Portland', 'descripcion' => 'Cemento de alta calidad para construcción', 'precio_base' => 25000],
            ['id' => 2, 'nombre' => 'Arena Fina', 'descripcion' => 'Arena fina para mezclas de construcción', 'precio_base' => 18000],
            ['id' => 3, 'nombre' => 'Grava Gruesa', 'descripcion' => 'Grava para base de construcción', 'precio_base' => 22000],
            ['id' => 4, 'nombre' => 'Ladrillos', 'descripcion' => 'Ladrillos cerámicos rojos', 'precio_base' => 450],
            ['id' => 5, 'nombre' => 'Varillas de Acero', 'descripcion' => 'Varillas corrugadas 12mm', 'precio_base' => 3500],
        ]);

        // Datos demo del pedido con algunos productos precargados
        $pedidoDemo = [
            'id' => $id,
            'numero' => 'PED-2025-' . str_pad($id, 3, '0', STR_PAD_LEFT),
            'cliente_id' => 1,
            'observaciones' => 'Pedido de prueba para edición',
            'productos' => [
                [
                    'id' => 1,
                    'nombre' => 'Cemento Portland',
                    'descripcion' => 'Cemento de alta calidad para construcción',
                    'precio_base' => 25000,
                    'cantidad' => 2
                ],
                [
                    'id' => 2,
                    'nombre' => 'Arena Fina',
                    'descripcion' => 'Arena fina para mezclas de construcción',
                    'precio_base' => 18000,
                    'cantidad' => 1
                ]
            ]
        ];

        return Inertia::render('Pedidos/Edit', [
            'pedido' => $pedidoDemo,
            'clientes' => $clientesDemo,
            'productos' => $productosDemo,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // MODO DEMO
        return redirect()->route('pedidos.index')
            ->with('success', 'Pedido actualizado exitosamente (Modo Demo)');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // MODO DEMO
        return back()->with('success', 'Pedido eliminado (Modo Demo)');
    }
} 