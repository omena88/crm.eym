<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Contacto;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Cliente::with(['contacto_principal', 'vendedor'])
            ->when($request->search, function ($query, $search) {
                $query->search($search);
            })
            ->when($request->estado, function ($query, $estado) {
                $query->where('estado', $estado);
            })
            ->when($request->sector, function ($query, $sector) {
                $query->where('sector', $sector);
            });

        $clientes = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Clientes/Index', [
            'clientes' => $clientes,
            'filters' => $request->only(['search', 'estado', 'sector']),
            'estados' => Cliente::distinct()->pluck('estado'),
            'sectores' => Cliente::distinct()->pluck('sector'),
            'auth' => [
                'user' => auth()->user()
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Clientes/Create', [
            'estados' => Cliente::getEstadosOptions(),
            'sectores' => Cliente::getSectoresOptions(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ruc' => ['required', 'string', 'size:11', 'unique:clientes,ruc', 'regex:/^\d{11}$/'],
            'razon_social' => ['required', 'string', 'max:255'],
            'sector' => ['required', 'string', Rule::in(array_keys(Cliente::getSectoresOptions()))],
            'estado' => ['required', 'string', Rule::in(array_keys(Cliente::getEstadosOptions()))],
            'telefono' => ['nullable', 'string', 'max:20'],
            'website' => ['nullable', 'url', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:500'],
            'notas' => ['nullable', 'string'],
            'contacto_principal' => ['required', 'array'],
            'contacto_principal.nombre' => ['required', 'string', 'max:100'],
            'contacto_principal.apellidos' => ['required', 'string', 'max:100'],
            'contacto_principal.puesto' => ['nullable', 'string', 'max:100'],
            'contacto_principal.email' => ['required', 'email', 'max:255'],
            'contacto_principal.celular' => ['nullable', 'string', 'max:20'],
        ]);

        $cliente = Cliente::create($validated);

        // Crear contacto principal
        $contactoPrincipal = $validated['contacto_principal'];
        $contactoPrincipal['es_contacto_principal'] = true;
        $cliente->contactos()->create($contactoPrincipal);

        return Redirect::route('clientes.index')
            ->with('success', 'Cliente creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Cliente $cliente): Response
    {
        $cliente->load(['contactos', 'visitas.user']);

        return Inertia::render('Clientes/Show', [
            'cliente' => $cliente,
            'contactos' => $cliente->contactos,
            'visitas' => $cliente->visitas()->latest()->paginate(10),
            'auth' => ['user' => auth()->user()]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cliente $cliente): Response
    {
        $cliente->load('contactos');

        return Inertia::render('Clientes/Edit', [
            'cliente' => $cliente,
            'estados' => Cliente::getEstadosOptions(),
            'sectores' => Cliente::getSectoresOptions(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cliente $cliente): RedirectResponse
    {
        $validated = $request->validate([
            'ruc' => ['required', 'string', 'size:11', 'regex:/^\d{11}$/', Rule::unique('clientes')->ignore($cliente)],
            'razon_social' => ['required', 'string', 'max:255'],
            'sector' => ['required', 'string', Rule::in(array_keys(Cliente::getSectoresOptions()))],
            'estado' => ['required', 'string', Rule::in(array_keys(Cliente::getEstadosOptions()))],
            'telefono' => ['nullable', 'string', 'max:20'],
            'website' => ['nullable', 'url', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:500'],
            'notas' => ['nullable', 'string'],
        ]);

        $cliente->update($validated);

        return Redirect::route('clientes.index')
            ->with('success', 'Cliente actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cliente $cliente): RedirectResponse
    {
        $cliente->delete();

        return Redirect::route('clientes.index')
            ->with('success', 'Cliente eliminado exitosamente.');
    }

    /**
     * Export clientes to Excel/CSV
     */
    public function export(Request $request)
    {
        $query = Cliente::with('contactoPrincipal')
            ->when($request->search, function ($query, $search) {
                $query->search($search);
            })
            ->when($request->estado, function ($query, $estado) {
                $query->where('estado', $estado);
            })
            ->when($request->sector, function ($query, $sector) {
                $query->where('sector', $sector);
            });

        $clientes = $query->get();

        $csvData = [
            ['Código', 'RUC', 'Razón Social', 'Sector', 'Estado', 'Teléfono', 'Website', 'Dirección', 'Contacto Principal', 'Email Contacto', 'Celular Contacto', 'Puesto Contacto']
        ];

        foreach ($clientes as $cliente) {
            $contacto = $cliente->contactoPrincipal;
            $csvData[] = [
                $cliente->codigo,
                $cliente->ruc,
                $cliente->razon_social,
                $cliente->sector,
                $cliente->estado,
                $cliente->telefono,
                $cliente->website,
                $cliente->direccion,
                $contacto ? $contacto->nombre_completo : '',
                $contacto ? $contacto->email : '',
                $contacto ? $contacto->celular : '',
                $contacto ? $contacto->puesto : '',
            ];
        }

        $filename = 'clientes_' . date('Y-m-d_H-i-s') . '.csv';
        $path = storage_path('app/temp/' . $filename);

        // Crear directorio si no existe
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $file = fopen($path, 'w');
        fputcsv($file, array_map('utf8_decode', $csvData[0])); // Header
        foreach (array_slice($csvData, 1) as $row) {
            fputcsv($file, array_map('utf8_decode', $row));
        }
        fclose($file);

        return ResponseFacade::download($path, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Download import template
     */
    public function downloadTemplate()
    {
        $templateData = [
            ['RUC', 'Razón Social', 'Sector', 'Estado', 'Teléfono', 'Website', 'Dirección', 'Contacto Nombre', 'Contacto Apellidos', 'Contacto Email', 'Contacto Celular', 'Contacto Puesto'],
            ['20123456789', 'Empresa Ejemplo S.A.C.', 'Sector 01', 'Pendiente', '01-1234567', 'https://www.ejemplo.com', 'Av. Ejemplo 123, Lima', 'Juan', 'Pérez', 'juan.perez@ejemplo.com', '987654321', 'Gerente General']
        ];

        $filename = 'template_clientes.csv';
        $path = storage_path('app/temp/' . $filename);

        // Crear directorio si no existe
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $file = fopen($path, 'w');
        foreach ($templateData as $row) {
            fputcsv($file, array_map('utf8_decode', $row));
        }
        fclose($file);

        return ResponseFacade::download($path, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Import clientes from CSV
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $data = array_map('str_getcsv', file($path));
        $header = array_shift($data);

        $imported = 0;
        $errors = [];

        foreach ($data as $index => $row) {
            try {
                $rowData = array_combine($header, $row);
                
                // Validar RUC único
                if (Cliente::where('ruc', $rowData['RUC'])->exists()) {
                    $errors[] = "Fila " . ($index + 2) . ": RUC {$rowData['RUC']} ya existe";
                    continue;
                }

                // Crear cliente
                $cliente = Cliente::create([
                    'ruc' => $rowData['RUC'],
                    'razon_social' => $rowData['Razón Social'],
                    'sector' => $rowData['Sector'],
                    'estado' => $rowData['Estado'],
                    'telefono' => $rowData['Teléfono'] ?? null,
                    'website' => $rowData['Website'] ?? null,
                    'direccion' => $rowData['Dirección'] ?? null,
                ]);

                // Crear contacto principal
                $cliente->contactos()->create([
                    'nombre' => $rowData['Contacto Nombre'],
                    'apellidos' => $rowData['Contacto Apellidos'],
                    'email' => $rowData['Contacto Email'],
                    'celular' => $rowData['Contacto Celular'] ?? null,
                    'puesto' => $rowData['Contacto Puesto'] ?? null,
                    'es_contacto_principal' => true,
                ]);

                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Fila " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        $message = "Se importaron {$imported} clientes exitosamente.";
        if (!empty($errors)) {
            $message .= " Errores: " . implode(', ', array_slice($errors, 0, 5));
        }

        return Redirect::route('clientes.index')
            ->with($imported > 0 ? 'success' : 'error', $message);
    }

    /**
     * Devuelve una lista simple de clientes para selects.
     */
    public function list()
    {
        return Cliente::select('id', 'razon_social')->orderBy('razon_social')->get();
    }

    /**
     * Muestra el historial de un cliente.
     */
    public function historial(Cliente $cliente)
    {
        // Cargar relaciones para obtener los datos
        $cliente->load('contactos', 'vendedor');

        // Obtener visitas
        $visitas = $cliente->visitas()->with('usuario')->get()->map(function ($visita) {
            return [
                'tipo' => 'Visita',
                'fecha' => $visita->fecha_programada,
                'titulo' => $visita->titulo,
                'descripcion' => $visita->objetivos ?? $visita->descripcion,
                'estado' => $visita->estado,
                'usuario' => $visita->usuario->name ?? 'N/A',
                'url' => route('visitas.show', $visita->id),
            ];
        });

        // Obtener cotizaciones
        $cotizaciones = $cliente->cotizaciones()->with('usuario')->get()->map(function ($cotizacion) {
            return [
                'tipo' => 'Cotización',
                'fecha' => $cotizacion->fecha_emision,
                'titulo' => "Cotización #{$cotizacion->codigo}",
                'descripcion' => "Monto: S/ " . number_format($cotizacion->monto_total, 2),
                'estado' => $cotizacion->estado,
                'usuario' => $cotizacion->usuario->name ?? 'N/A',
                'url' => route('cotizaciones.show', $cotizacion->id),
            ];
        });

        // Obtener pedidos
        $pedidos = $cliente->pedidos()->with('usuario')->get()->map(function ($pedido) {
            return [
                'tipo' => 'Pedido',
                'fecha' => $pedido->fecha_pedido,
                'titulo' => "Pedido #{$pedido->codigo}",
                'descripcion' => "Monto: S/ " . number_format($pedido->monto_total, 2),
                'estado' => $pedido->estado,
                'usuario' => $pedido->usuario->name ?? 'N/A',
                'url' => '#', // No hay ruta para mostrar pedidos aún
            ];
        });
        
        // Unir y ordenar el historial
        $historial = $visitas->concat($cotizaciones)->concat($pedidos)->sortByDesc('fecha');

        return Inertia::render('Clientes/Historial', [
            'cliente' => $cliente,
            'historial' => $historial->values()->all(),
            'auth' => ['user' => auth()->user()]
        ]);
    }
}
