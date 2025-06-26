<?php

namespace App\Http\Controllers;

use App\Models\Contacto;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContactoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Contacto::with(['cliente:id,razon_social,codigo']);

            // Filtros
            if ($request->filled('search')) {
                $query->search($request->search);
            }

            if ($request->filled('cliente_id')) {
                $query->where('cliente_id', $request->cliente_id);
            }

            if ($request->filled('es_principal')) {
                $query->where('es_contacto_principal', $request->boolean('es_principal'));
            }

            // Ordenación
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            $query->orderBy($sortBy, $sortDirection);

            $contactos = $query->paginate(15)->withQueryString();

            // Estadísticas (solo si no hay filtros para mejorar rendimiento)
            $stats = [
                'total' => Contacto::count(),
                'principales' => Contacto::principales()->count(),
                'con_email' => Contacto::whereNotNull('email')->count(),
                'con_telefono' => Contacto::where(function($q) {
                    $q->whereNotNull('telefono')->orWhereNotNull('celular');
                })->count(),
            ];

            // Lista de clientes para el filtro (solo necesitamos id y razon_social)
            $clientes = Cliente::select('id', 'razon_social')
                ->orderBy('razon_social')
                ->get();

            return Inertia::render('Contactos/Index', [
                'contactos' => $contactos,
                'stats' => $stats,
                'filters' => $request->only(['search', 'cliente_id', 'es_principal']),
                'sort' => $request->only(['sort_by', 'sort_direction']),
                'clientes' => $clientes,
                'auth' => ['user' => auth()->user()]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en ContactoController@index: ' . $e->getMessage());
            
            return Inertia::render('Contactos/Index', [
                'contactos' => collect(['data' => [], 'links' => [], 'from' => 0, 'to' => 0, 'total' => 0]),
                'stats' => ['total' => 0, 'principales' => 0, 'con_email' => 0, 'con_telefono' => 0],
                'filters' => [],
                'sort' => [],
                'clientes' => collect(),
                'error' => 'Error al cargar los contactos. Por favor, inténtalo de nuevo.',
                'auth' => ['user' => auth()->user()]
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $clienteId = $request->get('cliente_id');
        $cliente = $clienteId ? Cliente::find($clienteId) : null;

        // Cargar lista de clientes si no se especifica uno
        $clientes = !$cliente ? Cliente::select('id', 'razon_social', 'codigo')
            ->orderBy('razon_social')
            ->get() : collect();

        return Inertia::render('Contactos/Create', [
            'cliente' => $cliente,
            'clientes' => $clientes,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'nombre' => 'required|string|max:100',
            'apellidos' => 'nullable|string|max:100',
            'puesto' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'es_contacto_principal' => 'boolean',
            'notas' => 'nullable|string',
        ]);

        $contacto = Contacto::create($validated);

        return redirect()->route('contactos.index')
            ->with('success', 'Contacto creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Contacto $contacto)
    {
        $contacto->load('cliente');

        return Inertia::render('Contactos/Show', [
            'contacto' => $contacto,
            'auth' => ['user' => auth()->user()]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contacto $contacto)
    {
        $contacto->load('cliente');

        return Inertia::render('Contactos/Edit', [
            'contacto' => $contacto,
            'auth' => ['user' => auth()->user()]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contacto $contacto)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellidos' => 'nullable|string|max:100',
            'puesto' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'es_contacto_principal' => 'boolean',
            'notas' => 'nullable|string',
        ]);

        $contacto->update($validated);

        return redirect()->route('contactos.index')
            ->with('success', 'Contacto actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contacto $contacto)
    {
        // Verificar si es el único contacto del cliente
        if ($contacto->cliente->contactos()->count() === 1) {
            return redirect()->route('contactos.index')
                ->with('error', 'No se puede eliminar el único contacto del cliente.');
        }

        $contacto->delete();

        return redirect()->route('contactos.index')
            ->with('success', 'Contacto eliminado exitosamente.');
    }

    /**
     * Marcar como contacto principal
     */
    public function makePrincipal(Contacto $contacto)
    {
        $contacto->update(['es_contacto_principal' => true]);

        return redirect()->back()
            ->with('success', 'Contacto marcado como principal exitosamente.');
    }

    /**
     * Exportar contactos a CSV
     */
    public function export(Request $request)
    {
        $query = Contacto::with('cliente');

        // Aplicar filtros
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        if ($request->filled('es_principal')) {
            $query->where('es_contacto_principal', $request->boolean('es_principal'));
        }

        $contactos = $query->get();

        $csv = fopen('php://temp', 'w+');
        
        // Encabezados
        fputcsv($csv, [
            'ID',
            'Nombre Completo',
            'Puesto',
            'Email',
            'Teléfono',
            'Celular',
            'Cliente',
            'RUC Cliente',
            'Es Principal',
            'Fecha Creación'
        ]);

        // Datos
        foreach ($contactos as $contacto) {
            fputcsv($csv, [
                $contacto->id,
                $contacto->nombre_completo,
                $contacto->puesto,
                $contacto->email,
                $contacto->telefono,
                $contacto->celular,
                $contacto->cliente->razon_social ?? '',
                $contacto->cliente->ruc ?? '',
                $contacto->es_contacto_principal ? 'Sí' : 'No',
                $contacto->created_at->format('Y-m-d H:i:s')
            ]);
        }

        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);

        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="contactos_' . date('Y-m-d_H-i-s') . '.csv"',
        ]);
    }

    /**
     * Obtener contactos por cliente
     */
    public function porCliente(Request $request, Cliente $cliente)
    {
        $contactos = $cliente->contactos()
            ->orderBy('es_contacto_principal', 'desc')
            ->orderBy('nombre')
            ->get();

        return response()->json($contactos);
    }
}
