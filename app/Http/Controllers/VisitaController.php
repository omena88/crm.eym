<?php

namespace App\Http\Controllers;

use App\Models\Visita;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class VisitaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Visita::with(['cliente', 'usuario']);

        // Filtrar por rol del usuario
        if ($user->rol === 'vendedor') {
            $query->where('user_id', $user->id);
        } elseif ($user->rol === 'gerente') {
            // El gerente puede ver todas las visitas de sus vendedores
            $vendedores = User::where('rol', 'vendedor')->pluck('id');
            $query->whereIn('user_id', $vendedores);
        }

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
            $query->where('tipo', $request->tipo);
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

        // Estadísticas
        $baseQuery = Visita::query();
        if ($user->rol === 'vendedor') {
            $baseQuery->where('user_id', $user->id);
        }

        $stats = [
            'total' => $baseQuery->count(),
            'pendientes' => $baseQuery->where('estado', 'pendiente')->count(),
            'aprobadas' => $baseQuery->where('estado', 'realizada')->count(),
            'completadas' => $baseQuery->where('estado', 'realizada')->count(),
            'planificadas' => $baseQuery->where('tipo', 'planificada')->count(),
            'no_planificadas' => $baseQuery->where('tipo', 'no_planificada')->count(),
            'hoy' => $baseQuery->whereDate('fecha_programada', today())->count(),
        ];

        // Opciones para filtros
        $filterOptions = [
            'estados' => ['pendiente', 'programada', 'realizada', 'cancelada'],
            'tipos' => ['comercial', 'tecnica', 'seguimiento', 'postventa'],
            'vendedores' => User::where('rol', 'vendedor')->select('id', 'name')->orderBy('name')->get(),
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
     * Enviar planificación semanal para aprobación
     */
    public function enviarPlanificacion(Request $request)
    {
        $user = auth()->user();
        
        if ($user->rol !== 'vendedor') {
            abort(403, 'Solo los vendedores pueden enviar planificaciones');
        }

        $validated = $request->validate([
            'semana' => 'required|integer|min:1|max:53',
            'año' => 'required|integer|min:2024',
        ]);

        // Obtener visitas de la semana
        $visitas = Visita::where('user_id', $user->id)
            ->porSemana($validated['semana'], $validated['año'])
            ->where('estado', 'pendiente')
            ->get();

        if ($visitas->isEmpty()) {
            return back()->with('error', 'No hay visitas planificadas para enviar');
        }

        // Marcar como programadas (cambiar estado)
        $visitas->each(function ($visita) {
            $visita->update([
                'estado' => 'programada',
            ]);
        });

        return back()->with('success', 'Planificación enviada para aprobación del gerente');
    }

    /**
     * Aprobar planificación (solo gerentes)
     */
    public function aprobarPlanificacion(Request $request, $id)
    {
        $user = auth()->user();
        
        if ($user->rol !== 'gerente') {
            abort(403, 'Solo los gerentes pueden aprobar planificaciones');
        }

        $validated = $request->validate([
            'aprobar' => 'required|boolean',
            'comentarios' => 'nullable|string|max:1000',
        ]);

        $visita = Visita::findOrFail($id);

        $nuevoEstado = $validated['aprobar'] ? 'programada' : 'cancelada';
        
        $visita->update([
            'estado' => $nuevoEstado,
            'notas' => $validated['comentarios'],
        ]);

        $accion = $validated['aprobar'] ? 'aprobada' : 'rechazada';
        return back()->with('success', "Visita {$accion} exitosamente");
    }

    /**
     * Completar visita
     */
    public function completarVisita(Request $request, Visita $visita)
    {
        $user = auth()->user();
        
        if ($user->rol !== 'vendedor' || $visita->user_id !== $user->id) {
            abort(403, 'No tienes permisos para completar esta visita');
        }

        $validated = $request->validate([
            'resultado' => 'required|string|max:2000',
            'notas' => 'nullable|string|max:1000',
            'satisfaccion_cliente' => 'nullable|integer|min:1|max:5',
            'requiere_seguimiento' => 'nullable|boolean',
            'fecha_siguiente_contacto' => 'nullable|date|after:today',
        ]);

        $visita->update([
            'estado' => 'realizada',
            'fecha_realizada' => now(),
            'resultado' => $validated['resultado'],
            'notas' => $validated['notas'],
            'satisfaccion_cliente' => $validated['satisfaccion_cliente'],
            'requiere_seguimiento' => $validated['requiere_seguimiento'] ?? false,
            'fecha_siguiente_contacto' => $validated['fecha_siguiente_contacto'],
        ]);

        return back()->with('success', 'Visita completada exitosamente');
    }

    /**
     * Crear visita no planificada
     */
    public function crearNoPlanificada(Request $request)
    {
        $user = auth()->user();
        
        if ($user->rol !== 'vendedor') {
            abort(403, 'Solo los vendedores pueden crear visitas no planificadas');
        }

        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'resumen_visita' => 'required|string|max:2000',
            'acuerdos' => 'nullable|string|max:1000',
            'proximos_pasos' => 'nullable|string|max:1000',
            'probabilidad_cierre' => 'nullable|numeric|min:0|max:100',
            'valor_estimado' => 'nullable|numeric|min:0',
        ]);

        $visita = Visita::create([
            'cliente_id' => $validated['cliente_id'],
            'user_id' => $user->id,
            'titulo' => $validated['titulo'],
            'descripcion' => $validated['descripcion'],
            'fecha_programada' => now(),
            'tipo' => 'seguimiento',
            'estado' => 'realizada',
            'fecha_realizada' => now(),
            'resultado' => $validated['resumen_visita'],
        ]);

        return back()->with('success', 'Visita no planificada registrada exitosamente');
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
        $user = auth()->user();
        
        if ($user->rol !== 'vendedor') {
            abort(403, 'Solo los vendedores pueden crear visitas');
        }

        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'fecha_programada' => 'required|date|after_or_equal:today',
            'tipo' => 'required|in:comercial,tecnica,seguimiento,postventa',
            'prioridad' => 'required|in:baja,media,alta,urgente',
            'duracion_estimada' => 'nullable|integer|min:15|max:480',
        ]);

        $visita = Visita::create([
            'cliente_id' => $validated['cliente_id'],
            'user_id' => $user->id,
            'titulo' => $validated['titulo'],
            'descripcion' => $validated['descripcion'],
            'fecha_programada' => $validated['fecha_programada'],
            'tipo' => $validated['tipo'],
            'prioridad' => $validated['prioridad'],
            'duracion_estimada' => $validated['duracion_estimada'] ?? 60,
            'estado' => 'programada',
        ]);

        return redirect()->route('visitas.index')
            ->with('success', 'Visita planificada exitosamente.');
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
        $user = auth()->user();
        
        if ($user->rol !== 'vendedor' || $visita->user_id !== $user->id) {
            abort(403, 'No tienes permisos para editar esta visita');
        }

        if ($visita->estado === 'realizada') {
            abort(403, 'No puedes editar una visita realizada');
        }

        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'fecha_programada' => 'required|date|after_or_equal:today',
            'tipo' => 'required|in:comercial,tecnica,seguimiento,postventa',
            'prioridad' => 'required|in:baja,media,alta,urgente',
            'duracion_estimada' => 'nullable|integer|min:15|max:480',
        ]);

        $visita->update($validated);

        return redirect()->route('visitas.index')
            ->with('success', 'Visita actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Visita $visita)
    {
        $user = auth()->user();
        
        if ($user->rol !== 'vendedor' || $visita->user_id !== $user->id) {
            abort(403, 'No tienes permisos para eliminar esta visita');
        }

        if ($visita->estado === 'realizada') {
            abort(403, 'No puedes eliminar una visita realizada');
        }

        $visita->delete();

        return back()->with('success', 'Visita eliminada exitosamente.');
    }
}
