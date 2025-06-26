<?php

namespace App\Http\Controllers;

use App\Models\Visita;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class VisitaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Visita::with(['cliente', 'usuario']);

        // Filtrar por usuario - cada usuario ve sus propias visitas + permisos según rol actual
        $baseUserQuery = function($query) use ($user) {
            // Cada usuario siempre ve sus propias visitas
            $query->where('user_id', $user->id);
            
            // Permisos adicionales según rol actual
            if ($user->rol === 'gerente') {
                // Gerente puede ver visitas de todos los vendedores para aprobación
                $vendedores = User::where('rol', 'vendedor')->pluck('id');
                $query->orWhereIn('user_id', $vendedores);
            }
        };
        
        $query->where($baseUserQuery);

        // Filtros
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('titulo', 'like', "%{$request->search}%")
                  ->orWhereHas('cliente', function ($clienteQuery) use ($request) {
                      $clienteQuery->where('razon_social', 'like', "%{$request->search}%");
                  });
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('tipo')) {
            // Filtrar por tipo de planificación (planificada/no_planificada) o por tipo de visita (comercial/tecnica/etc)
            $query->where(function($q) use ($request) {
                $q->where('tipo', $request->tipo)
                  ->orWhere('tipo_planificacion', $request->tipo);
            });
        }

        if ($request->filled('vendedor_id')) {
            $query->where('user_id', $request->vendedor_id);
        }

        if ($request->filled('semana') && $request->filled('año')) {
            $query->porSemana($request->semana, $request->año);
        }

        // Ordenación
        $sortBy = $request->get('sort_by', 'fecha_programada');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $visitas = $query->paginate(20)->withQueryString();

        // Estadísticas - usando el mismo filtro que la consulta principal
        $baseQuery = Visita::query();
        $baseQuery->where($baseUserQuery);

        $stats = [
            'total' => $baseQuery->count(),
            'pendientes' => $baseQuery->where('estado', 'pendiente')->count(),
            'programadas' => $baseQuery->where('estado', 'programada')->count(),
            'aprobadas' => $baseQuery->where('estado', 'aprobada')->count(),
            'realizadas' => $baseQuery->where('estado', 'realizada')->count(),
            'completadas' => $baseQuery->where('estado', 'realizada')->count(),
            'planificadas' => $baseQuery->where('tipo_planificacion', 'planificada')->count(),
            'no_planificadas' => $baseQuery->where('tipo_planificacion', 'no_planificada')->count(),
            'hoy' => $baseQuery->whereDate('fecha_programada', today())->count(),
        ];

        // Visitas pendientes de aprobación (solo para gerentes)
        $visitasPendientesAprobacion = collect();
        if ($user->rol === 'gerente') {
            $visitasPendientesAprobacion = Visita::with(['cliente', 'usuario'])
                ->where('estado', 'programada')
                ->whereNotNull('fecha_envio_aprobacion')
                ->whereNull('fecha_aprobacion')
                ->orderBy('fecha_envio_aprobacion', 'asc')
                ->paginate(50);
        }

        // Opciones para filtros
        $filterOptions = [
            'estados' => ['pendiente', 'programada', 'aprobada', 'realizada', 'cancelada'],
            'tipos' => ['planificada', 'no_planificada', 'comercial', 'tecnica', 'seguimiento', 'postventa'],
            'vendedores' => User::where('rol', 'vendedor')->select('id', 'name')->orderBy('name')->get(),
            'roles' => [
                'vendedor' => 'Vendedor',
                'gerente' => 'Gerente', 
            ]
        ];

        return Inertia::render('Visitas/Index', [
            'visitas' => $visitas,
            'stats' => $stats,
            'filterOptions' => $filterOptions,
            'filters' => $request->only([
                'search', 'estado', 'tipo', 'vendedor_id', 'semana', 'año'
            ]),
            'sort' => $request->only(['sort_by', 'sort_direction']),
            'userRole' => $user->rol,
            'visitasPendientesAprobacion' => $visitasPendientesAprobacion,
            'auth' => [
                'user' => auth()->user()
            ]
        ]);
    }

    /**
     * Planificación semanal de visitas
     */
    public function planificacionSemanal(Request $request)
    {
        $user = auth()->user();
        
        if ($user->rol !== 'vendedor') {
            abort(403, 'Solo los vendedores pueden acceder a la planificación semanal');
        }

        $semana = $request->get('semana', now()->week);
        $año = $request->get('año', now()->year);

        $visitas = Visita::where('user_id', $user->id)
            ->porSemana($semana, $año)
            ->with(['cliente'])
            ->orderBy('fecha_programada')
            ->get();

        $clientes = Cliente::select('id', 'razon_social', 'nombre_comercial')
            ->orderBy('razon_social')
            ->get();

        // Verificar si ya se envió la planificación de esta semana
        $planificacionEnviada = false; // Simplificado para la estructura actual

        return Inertia::render('Visitas/PlanificacionSemanal', [
            'visitas' => $visitas,
            'clientes' => $clientes,
            'semana' => $semana,
            'año' => $año,
            'planificacionEnviada' => $planificacionEnviada,
        ]);
    }

    /**
     * Obtener datos para el calendario de planificación
     */
    public function planificacionDatos(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Usuario no autenticado'], 401);
            }

            $fecha = $request->get('fecha', now()->toISOString());
            $startOfWeek = Carbon::parse($fecha)->startOfWeek(Carbon::MONDAY);

            // Datos de demo
            $clientesDemo = Cliente::take(5)->get();
            if ($clientesDemo->isEmpty()) {
                 return response()->json(['visitas' => [], 'planificacionEnviada' => false]);
            }

            $visitasDemo = collect([
                [
                    'id' => 1001,
                    'cliente_id' => $clientesDemo[0]->id,
                    'cliente' => ['razon_social' => $clientesDemo[0]->razon_social],
                    'fecha_programada' => $startOfWeek->copy()->addDays(0)->format('Y-m-d'), // Lunes
                    'turno' => 'mañana',
                    'tipo' => 'comercial',
                    'objetivos' => 'Presentación de nuevo producto',
                    'estado' => 'programada'
                ],
                [
                    'id' => 1002,
                    'cliente_id' => $clientesDemo[1]->id,
                    'cliente' => ['razon_social' => $clientesDemo[1]->razon_social],
                    'fecha_programada' => $startOfWeek->copy()->addDays(0)->format('Y-m-d'), // Lunes
                    'turno' => 'tarde',
                    'tipo' => 'seguimiento',
                    'objetivos' => 'Seguimiento cotización #1234',
                    'estado' => 'programada'
                ],
                [
                    'id' => 1003,
                    'cliente_id' => $clientesDemo[2]->id,
                    'cliente' => ['razon_social' => $clientesDemo[2]->razon_social],
                    'fecha_programada' => $startOfWeek->copy()->addDays(2)->format('Y-m-d'), // Miércoles
                    'turno' => 'mañana',
                    'tipo' => 'tecnica',
                    'objetivos' => 'Revisión de equipo instalado',
                    'estado' => 'aprobada'
                ],
                [
                    'id' => 1004,
                    'cliente_id' => $clientesDemo[3]->id,
                    'cliente' => ['razon_social' => $clientesDemo[3]->razon_social],
                    'fecha_programada' => $startOfWeek->copy()->addDays(3)->format('Y-m-d'), // Jueves
                    'turno' => 'mañana',
                    'tipo' => 'postventa',
                    'objetivos' => 'Capacitación de personal',
                    'estado' => 'aprobada'
                ],
                [
                    'id' => 1005,
                    'cliente_id' => $clientesDemo[4]->id,
                    'cliente' => ['razon_social' => $clientesDemo[4]->razon_social],
                    'fecha_programada' => $startOfWeek->copy()->addDays(4)->format('Y-m-d'), // Viernes
                    'turno' => 'tarde',
                    'tipo' => 'comercial',
                    'objetivos' => 'Negociación y cierre',
                    'estado' => 'realizada'
                ],
            ]);

            return response()->json([
                'visitas' => $visitasDemo,
                'planificacionEnviada' => false // Para modo demo, siempre se puede editar
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en planificacionDatos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error inesperado al cargar los datos de planificación.'
            ], 500);
        }
    }

    /**
     * Descargar plantilla de Excel para importar visitas
     */
    public function descargarPlantilla(Request $request)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', 'Prueba simple');

            $writer = new Xlsx($spreadsheet);
            
            $fileName = "plantilla-prueba.xlsx";
            $tempFile = tempnam(sys_get_temp_dir(), $fileName);
            
            $writer->save($tempFile);
            
            return response()->download($tempFile, $fileName)->deleteFileAfterSend();
            
        } catch (\Exception $e) {
            \Log::error('Error al generar plantilla Excel (versión simple): ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno al generar la plantilla. Revise los logs.'
            ], 500);
        }
    }

    /**
     * Guarda las visitas planificadas desde el modal.
     */
    public function guardarPlanificacion(Request $request)
    {
        // MODO DEMO
        return response()->json([
            'success' => true,
            'message' => 'Planificación guardada (Modo Demo)',
        ]);
    }

    /**
     * Enviar planificación para aprobación y notificar por WhatsApp
     */
    public function enviarPlanificacion(Request $request)
    {
        // MODO DEMO
        return response()->json([
            'success' => true,
            'message' => 'Planificación enviada para aprobación (Modo Demo)'
        ]);
    }

    /**
     * Revertir planificación enviada
     */
    public function revertirPlanificacion(Request $request)
    {
        // MODO DEMO
        return response()->json([
            'success' => true,
            'message' => 'Planificación revertida (Modo Demo)'
        ]);
    }

    /**
     * Aprobar o rechazar planificación
     */
    public function aprobarPlanificacion(Request $request)
    {
        // MODO DEMO
        return response()->json([
            'success' => true,
            'message' => 'Acción realizada (Modo Demo)'
        ]);
    }

    /**
     * Realizar visita
     */
    public function realizarVisita(Request $request, Visita $visita)
    {
        // MODO DEMO
        return response()->json([
            'success' => true,
            'message' => 'Visita marcada como realizada (Modo Demo)'
        ]);
    }

    /**
     * Enviar notificación por WhatsApp
     */
    private function enviarNotificacionWhatsApp($vendedor, $visitas, $semana, $año)
    {
        try {
            $totalVisitas = $visitas->count();
            $clientes = $visitas->pluck('cliente.razon_social')->unique()->take(3)->implode(', ');
            $masClientes = $visitas->pluck('cliente.razon_social')->unique()->count() > 3 ? ' y otros' : '';
            
            $mensaje = "🗓️ *Nueva Planificación de Visitas*\n\n";
            $mensaje .= "👤 *Vendedor:* {$vendedor->name}\n";
            $mensaje .= "📅 *Semana:* {$semana} del {$año}\n";
            $mensaje .= "📊 *Total de visitas:* {$totalVisitas}\n";
            $mensaje .= "🏢 *Clientes:* {$clientes}{$masClientes}\n\n";
            $mensaje .= "Para revisar y aprobar, ingresa al sistema: " . url('/visitas');

            $data = [
                'Phone' => '51934890223',
                'Body' => $mensaje
            ];

            $client = new \GuzzleHttp\Client();
            $response = $client->post('https://apps-wuzapi-server.di8b44.easypanel.host/chat/send/text', [
                'headers' => [
                    'Token' => '123456789',
                    'Content-Type' => 'application/json'
                ],
                'json' => $data,
                'timeout' => 10
            ]);

            \Log::info('Notificación WhatsApp enviada', [
                'vendedor' => $vendedor->name,
                'semana' => $semana,
                'año' => $año,
                'visitas' => $totalVisitas
            ]);

        } catch (\Exception $e) {
            \Log::error('Error enviando notificación WhatsApp: ' . $e->getMessage());
            // No fallar el proceso principal si falla el WhatsApp
        }
    }

    /**
     * Ejecutar visita planificada (cambiar estado de planificada a ejecutada)
     */
    public function ejecutarVisitaPlanificada(Request $request, Visita $visita)
    {
        // MODO DEMO
        return response()->json([
            'success' => true,
            'message' => 'Visita ejecutada exitosamente (Modo Demo)'
        ]);
    }

    /**
     * Completar visita
     */
    public function completarVisita(Request $request, Visita $visita)
    {
        // MODO DEMO
        return back()->with('success', 'Visita completada (Modo Demo)');
    }

    /**
     * Crear visita no planificada
     */
    public function crearNoPlanificada(Request $request)
    {
        // MODO DEMO
        return back()->with('success', 'Visita no planificada registrada (Modo Demo)');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        
        if ($user->rol !== 'vendedor') {
            abort(403, 'Solo los vendedores pueden crear visitas');
        }

        $clienteId = $request->get('cliente_id');
        $cliente = $clienteId ? Cliente::find($clienteId) : null;

        return Inertia::render('Visitas/Create', [
            'cliente' => $cliente,
            'clientes' => Cliente::select('id', 'razon_social', 'nombre_comercial')->orderBy('razon_social')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // MODO DEMO
        return redirect()->route('visitas.index')
            ->with('success', 'Visita planificada (Modo Demo)');
    }

    /**
     * Display the specified resource.
     */
    public function show(Visita $visita)
    {
        $user = auth()->user();
        
        // Verificar permisos
        if ($user->rol === 'vendedor' && $visita->user_id !== $user->id) {
            abort(403, 'No tienes permisos para ver esta visita');
        }

        $visita->load(['cliente', 'usuario']);

        return Inertia::render('Visitas/Show', [
            'visita' => $visita,
            'userRole' => $user->rol,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Visita $visita)
    {
        $user = auth()->user();
        
        if ($user->rol !== 'vendedor' || $visita->user_id !== $user->id) {
            abort(403, 'No tienes permisos para editar esta visita');
        }

        if ($visita->estado === 'realizada') {
            abort(403, 'No puedes editar una visita realizada');
        }

        $visita->load(['cliente']);

        return Inertia::render('Visitas/Edit', [
            'visita' => $visita,
            'clientes' => Cliente::select('id', 'razon_social', 'nombre_comercial')->orderBy('razon_social')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Visita $visita)
    {
        // MODO DEMO
        return redirect()->route('visitas.index')
            ->with('success', 'Visita actualizada (Modo Demo)');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Visita $visita)
    {
        // MODO DEMO
        return back()->with('success', 'Visita eliminada (Modo Demo)');
    }

    /**
     * Actualizar solo los comentarios de una visita
     */
    public function updateComentarios(Request $request, Visita $visita)
    {
        // MODO DEMO
        return response()->json([
            'success' => true,
            'message' => 'Comentarios actualizados (Modo Demo)'
        ]);
    }
}
