<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Cliente;
use App\Models\Contacto;
use App\Models\Visita;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        
        // Métricas principales
        $metricas = $this->obtenerMetricasPrincipales();
        
        // Gráficos de tendencias
        $graficos = $this->obtenerDatosGraficos();
        
        // Actividades recientes
        $actividades = $this->obtenerActividadesRecientes();
        
        // Alertas y notificaciones
        $alertas = $this->obtenerAlertas();
        
        // Visitas próximas
        $visitasProximas = $this->obtenerVisitasProximas();

        return Inertia::render('Dashboard', [
            'metricas' => $metricas,
            'graficos' => $graficos,
            'actividades' => $actividades,
            'alertas' => $alertas,
            'visitasProximas' => $visitasProximas,
            'auth' => [
                'user' => auth()->user()
            ]
        ]);
    }

    private function obtenerMetricasPrincipales()
    {
        $hoy = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth();
        $mesAnterior = Carbon::now()->subMonth()->startOfMonth();
        $finMesAnterior = Carbon::now()->subMonth()->endOfMonth();

        return [
            'clientes' => [
                'total' => Cliente::count(),
                'activos' => Cliente::activos()->count(),
                'potenciales' => Cliente::potenciales()->count(),
                'nuevos_mes' => Cliente::where('created_at', '>=', $inicioMes)->count(),
                'cambio_mes_anterior' => $this->calcularCambioMensual(
                    Cliente::whereBetween('created_at', [$mesAnterior, $finMesAnterior])->count(),
                    Cliente::where('created_at', '>=', $inicioMes)->count()
                ),
            ],
            'visitas' => [
                'total_mes' => Visita::where('created_at', '>=', $inicioMes)->count(),
                'realizadas_mes' => Visita::realizadas()
                    ->where('fecha_realizada', '>=', $inicioMes)->count(),
                'programadas' => Visita::programadas()->count(),
                'hoy' => Visita::hoy()->count(),
                'vencidas' => Visita::vencidas()->count(),
                'tasa_realizacion' => $this->calcularTasaRealizacion(),
            ],
            'pipeline' => [
                'valor_total' => Cliente::sum('valor_potencial') ?? 0,
                'promedio_probabilidad' => Cliente::whereNotNull('probabilidad_cierre')
                    ->avg('probabilidad_cierre') ?? 0,
                'clientes_alta_probabilidad' => Cliente::where('probabilidad_cierre', '>=', 70)->count(),
                'valor_estimado' => $this->calcularValorEstimado(),
            ],
            'actividad' => [
                'contactos_total' => Contacto::count(),
                'contactos_con_email' => Contacto::whereNotNull('email')->count(),
                'ultimo_contacto_promedio' => $this->calcularTiempoPromedioUltimoContacto(),
                'clientes_sin_contacto' => $this->contarClientesSinContactoReciente(),
            ],
        ];
    }

    private function obtenerDatosGraficos()
    {
        return [
            'visitas_por_mes' => $this->obtenerVisitasPorMes(),
            'clientes_por_estado' => $this->obtenerClientesPorEstado(),
            'visitas_por_tipo' => $this->obtenerVisitasPorTipo(),
            'pipeline_por_sector' => $this->obtenerPipelinePorSector(),
            'tendencia_clientes' => $this->obtenerTendenciaClientes(),
            'satisfaccion_promedio' => $this->obtenerSatisfaccionPromedio(),
        ];
    }

    private function obtenerActividadesRecientes()
    {
        $actividades = collect();

        // Clientes recientes
        $clientesRecientes = Cliente::with('contactoPrincipal')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($cliente) {
                return [
                    'tipo' => 'cliente_nuevo',
                    'titulo' => 'Nuevo cliente registrado',
                    'descripcion' => $cliente->razon_social,
                    'fecha' => $cliente->created_at,
                    'icono' => 'user-plus',
                    'color' => 'green',
                ];
            });

        // Visitas realizadas recientemente
        $visitasRealizadas = Visita::with(['cliente', 'user'])
            ->realizadas()
            ->latest('fecha_realizada')
            ->limit(5)
            ->get()
            ->map(function ($visita) {
                return [
                    'tipo' => 'visita_realizada',
                    'titulo' => 'Visita realizada',
                    'descripcion' => $visita->titulo . ' - ' . $visita->cliente->razon_social,
                    'fecha' => $visita->fecha_realizada,
                    'icono' => 'check-circle',
                    'color' => 'blue',
                    'usuario' => $visita->user->name,
                ];
            });

        $actividades = $actividades->merge($clientesRecientes)->merge($visitasRealizadas);

        return $actividades->sortByDesc('fecha')->take(10)->values();
    }

    private function obtenerAlertas()
    {
        $alertas = [];

        // Visitas vencidas
        $visitasVencidas = Visita::vencidas()->count();
        if ($visitasVencidas > 0) {
            $alertas[] = [
                'tipo' => 'warning',
                'titulo' => 'Visitas vencidas',
                'mensaje' => "Tienes {$visitasVencidas} visita(s) vencida(s) que requieren atención",
                'icono' => 'exclamation-triangle',
                'link' => '/visitas?filtro_rapido=vencidas',
            ];
        }

        // Clientes sin contacto reciente
        $clientesSinContacto = $this->contarClientesSinContactoReciente();
        if ($clientesSinContacto > 0) {
            $alertas[] = [
                'tipo' => 'info',
                'titulo' => 'Seguimiento pendiente',
                'mensaje' => "{$clientesSinContacto} cliente(s) sin contacto en los últimos 30 días",
                'icono' => 'clock',
                'link' => '/clientes',
            ];
        }

        // Visitas de hoy
        $visitasHoy = Visita::hoy()->programadas()->count();
        if ($visitasHoy > 0) {
            $alertas[] = [
                'tipo' => 'success',
                'titulo' => 'Visitas de hoy',
                'mensaje' => "Tienes {$visitasHoy} visita(s) programada(s) para hoy",
                'icono' => 'calendar',
                'link' => '/visitas?filtro_rapido=hoy',
            ];
        }

        return $alertas;
    }

    private function obtenerVisitasProximas()
    {
        return Visita::with(['cliente', 'user'])
            ->proximas()
            ->limit(5)
            ->get()
            ->map(function ($visita) {
                return [
                    'id' => $visita->id,
                    'titulo' => $visita->titulo,
                    'cliente' => $visita->cliente->razon_social,
                    'fecha' => $visita->fecha_programada,
                    'usuario' => $visita->user->name,
                    'tipo' => $visita->tipo,
                    'prioridad' => $visita->prioridad,
                    'es_hoy' => $visita->es_hoy,
                    'dias_hasta_visita' => $visita->dias_hasta_visita,
                ];
            });
    }

    private function obtenerVisitasPorMes()
    {
        $meses = [];
        for ($i = 11; $i >= 0; $i--) {
            $fecha = Carbon::now()->subMonths($i);
            $meses[] = [
                'mes' => $fecha->format('M Y'),
                'programadas' => Visita::whereYear('fecha_programada', $fecha->year)
                    ->whereMonth('fecha_programada', $fecha->month)
                    ->count(),
                'realizadas' => Visita::realizadas()
                    ->whereYear('fecha_realizada', $fecha->year)
                    ->whereMonth('fecha_realizada', $fecha->month)
                    ->count(),
            ];
        }
        return $meses;
    }

    private function obtenerClientesPorEstado()
    {
        return Cliente::select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->get()
            ->map(function ($item) {
                return [
                    'estado' => ucfirst($item->estado),
                    'total' => $item->total,
                    'color' => match($item->estado) {
                        'activo' => '#10b981',
                        'potencial' => '#f59e0b',
                        'inactivo' => '#ef4444',
                        default => '#6b7280'
                    },
                ];
            });
    }

    private function obtenerVisitasPorTipo()
    {
        return Visita::select('tipo', DB::raw('count(*) as total'))
            ->groupBy('tipo')
            ->get()
            ->map(function ($item) {
                return [
                    'tipo' => ucfirst($item->tipo),
                    'total' => $item->total,
                ];
            });
    }

    private function obtenerPipelinePorSector()
    {
        return Cliente::select('sector', DB::raw('sum(valor_potencial) as valor_total'))
            ->whereNotNull('valor_potencial')
            ->groupBy('sector')
            ->orderByDesc('valor_total')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'sector' => $item->sector,
                    'valor' => (float) $item->valor_total,
                ];
            });
    }

    private function obtenerTendenciaClientes()
    {
        $meses = [];
        for ($i = 11; $i >= 0; $i--) {
            $fecha = Carbon::now()->subMonths($i);
            $meses[] = [
                'mes' => $fecha->format('M Y'),
                'nuevos' => Cliente::whereYear('created_at', $fecha->year)
                    ->whereMonth('created_at', $fecha->month)
                    ->count(),
            ];
        }
        return $meses;
    }

    private function obtenerSatisfaccionPromedio()
    {
        return Visita::realizadas()
            ->whereNotNull('satisfaccion_cliente')
            ->avg('satisfaccion_cliente') ?? 0;
    }

    private function calcularCambioMensual($valorAnterior, $valorActual)
    {
        if ($valorAnterior == 0) return $valorActual > 0 ? 100 : 0;
        return round((($valorActual - $valorAnterior) / $valorAnterior) * 100, 1);
    }

    private function calcularTasaRealizacion()
    {
        $totalVisitas = Visita::count();
        if ($totalVisitas == 0) return 0;
        
        $visitasRealizadas = Visita::realizadas()->count();
        return round(($visitasRealizadas / $totalVisitas) * 100, 1);
    }

    private function calcularValorEstimado()
    {
        return Cliente::whereNotNull('valor_potencial')
            ->whereNotNull('probabilidad_cierre')
            ->get()
            ->sum(function ($cliente) {
                return $cliente->valor_potencial * ($cliente->probabilidad_cierre / 100);
            });
    }

    private function calcularTiempoPromedioUltimoContacto()
    {
        $clientes = Cliente::whereNotNull('fecha_ultimo_contacto')->get();
        if ($clientes->isEmpty()) return 0;

        $totalDias = $clientes->sum(function ($cliente) {
            return Carbon::now()->diffInDays($cliente->fecha_ultimo_contacto);
        });

        return round($totalDias / $clientes->count());
    }

    private function contarClientesSinContactoReciente()
    {
        $fechaLimite = Carbon::now()->subDays(30);
        return Cliente::where(function ($query) use ($fechaLimite) {
            $query->whereNull('fecha_ultimo_contacto')
                  ->orWhere('fecha_ultimo_contacto', '<', $fechaLimite);
        })->count();
    }
}
