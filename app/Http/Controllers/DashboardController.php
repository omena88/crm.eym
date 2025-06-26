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
        $user = auth()->user();
        
        // Redirigir según el rol del usuario
        switch ($user->rol) {
            case 'vendedor':
                return $this->dashboardVendedor();
            case 'gerente':
                return $this->dashboardGerente();
            default:
                return $this->dashboardGeneral();
        }
    }

    public function dashboardVendedor()
    {
        $user = auth()->user();
        
        // Métricas específicas para vendedores
        $metricas = $this->obtenerMetricasVendedor($user);
        $graficos = $this->obtenerDatosGraficosVendedor($user);
        $visitasProximas = $this->obtenerVisitasProximasVendedor($user);
        $alertas = $this->obtenerAlertasVendedor($user);

        return Inertia::render('Dashboard', [
            'metricas' => $metricas,
            'graficos' => $graficos,
            'visitasProximas' => $visitasProximas,
            'alertas' => $alertas,
            'userRole' => 'vendedor',
            'auth' => [
                'user' => $user
            ]
        ]);
    }

    public function dashboardGerente()
    {
        $user = auth()->user();
        
        // Métricas específicas para gerentes
        $metricas = $this->obtenerMetricasGerente($user);
        $graficos = $this->obtenerDatosGraficosGerente($user);
        $visitasProximas = $this->obtenerVisitasProximasGerente($user);
        $alertas = $this->obtenerAlertasGerente($user);

        return Inertia::render('Dashboard', [
            'metricas' => $metricas,
            'graficos' => $graficos,
            'visitasProximas' => $visitasProximas,
            'alertas' => $alertas,
            'userRole' => 'gerente',
            'auth' => [
                'user' => $user
            ]
        ]);
    }

    public function dashboardGeneral()
    {
        $user = auth()->user();
        
        // Métricas para el dashboard general
        $metricas = $this->obtenerMetricasDemo($user);
        $graficos = $this->obtenerDatosGraficos();
        $actividades = $this->obtenerActividadesRecientes();
        $alertas = $this->obtenerAlertas();
        $visitasProximas = $this->obtenerVisitasProximas();

        return Inertia::render('Dashboard', [
            'metricas' => $metricas,
            'graficos' => $graficos,
            'actividades' => $actividades,
            'alertas' => $alertas,
            'visitasProximas' => $visitasProximas,
            'userRole' => 'general',
            'auth' => [
                'user' => $user
            ]
        ]);
    }

    private function obtenerMetricasDemo($user)
    {
        // 1. % de Cumplimiento de Visitas Planificadas (Datos Ficticios)
        $cumplimientoVisitas = 92.5;
        $cambioCumplimiento = -2.5; // vs mes anterior

        // 2. Cotizaciones Emitidas (Datos Ficticios)
        $cotizacionesMes = 125;
        $cambioCotizaciones = 12.3; // vs mes anterior

        // 3. Pedidos Confirmados (Datos Ficticios)
        $pedidosConfirmados = 32;
        $cambioPedidos = 5.8; // vs mes anterior
        
        // 4. Margen de Ventas (Datos Ficticios)
        $margenBruto = 25.5;
        $cambioMargen = 1.2; // vs mes anterior

        return [
            'cumplimiento_visitas' => [
                'valor' => $cumplimientoVisitas,
                'cambio' => $cambioCumplimiento,
            ],
            'cotizaciones' => [
                'valor' => $cotizacionesMes,
                'cambio' => $cambioCotizaciones,
            ],
            'pedidos' => [
                'valor' => $pedidosConfirmados,
                'cambio' => $cambioPedidos,
            ],
            'margen' => [
                'valor' => $margenBruto,
                'cambio' => $cambioMargen,
            ],
        ];
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
            'visitas_semanales' => $this->obtenerVisitasSemanales(),
        ];
    }

    private function obtenerVisitasSemanales($semanasAtras = 6)
    {
        // --- START: Datos Ficticios para la Demo ---
        $datosSemanales = [];
        
        try {
            $hoy = Carbon::today();

            for ($i = $semanasAtras - 1; $i >= 0; $i--) {
                $inicioSemana = $hoy->copy()->subWeeks($i)->startOfWeek();
                
                $planificadas = mt_rand(15, 30);
                $realizadasPlanificadas = mt_rand(intval($planificadas * 0.6), $planificadas);
                $realizadasNoPlanificadas = mt_rand(2, 8);

                $datosSemanales[] = [
                    'semana' => $inicioSemana->format('d M'),
                    'planificadas' => $planificadas,
                    'realizadas_planificadas' => $realizadasPlanificadas,
                    'realizadas_no_planificadas' => $realizadasNoPlanificadas,
                    'total_realizadas' => $realizadasPlanificadas + $realizadasNoPlanificadas,
                ];
            }
            
            return $datosSemanales;
            
        } catch (\Exception $e) {
            \Log::error('Error generando datos semanales: ' . $e->getMessage());
            
            // Fallback con datos hardcodeados
            return [
                ['semana' => '16 Dic', 'planificadas' => 25, 'realizadas_planificadas' => 20, 'realizadas_no_planificadas' => 5, 'total_realizadas' => 25],
                ['semana' => '23 Dic', 'planificadas' => 22, 'realizadas_planificadas' => 18, 'realizadas_no_planificadas' => 3, 'total_realizadas' => 21],
                ['semana' => '30 Dic', 'planificadas' => 18, 'realizadas_planificadas' => 15, 'realizadas_no_planificadas' => 4, 'total_realizadas' => 19],
                ['semana' => '06 Ene', 'planificadas' => 28, 'realizadas_planificadas' => 25, 'realizadas_no_planificadas' => 6, 'total_realizadas' => 31],
                ['semana' => '13 Ene', 'planificadas' => 24, 'realizadas_planificadas' => 22, 'realizadas_no_planificadas' => 4, 'total_realizadas' => 26],
                ['semana' => '20 Ene', 'planificadas' => 26, 'realizadas_planificadas' => 24, 'realizadas_no_planificadas' => 7, 'total_realizadas' => 31],
            ];
        }
        // --- END: Datos Ficticios para la Demo ---
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
        $visitasRealizadas = Visita::with(['cliente', 'usuario'])
            ->where('estado', 'realizada')
            ->whereNotNull('fecha_realizada')
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
                    'usuario' => $visita->usuario->name,
                ];
            });

        $actividades = $actividades->merge($clientesRecientes)->merge($visitasRealizadas);

        // Si no hay actividades reales, devolver datos demo
        if ($actividades->isEmpty()) {
            return collect([
                [
                    'tipo' => 'cliente_nuevo',
                    'titulo' => 'Nuevo cliente registrado',
                    'descripcion' => 'Constructora ABC S.A.',
                    'fecha' => Carbon::now()->subHours(2),
                    'icono' => 'user-plus',
                    'color' => 'green',
                ],
                [
                    'tipo' => 'visita_realizada',
                    'titulo' => 'Visita realizada',
                    'descripcion' => 'Presentación de productos - Empresa XYZ Ltda.',
                    'fecha' => Carbon::now()->subHours(5),
                    'icono' => 'check-circle',
                    'color' => 'blue',
                    'usuario' => 'Vendedor Demo',
                ],
                [
                    'tipo' => 'cliente_nuevo',
                    'titulo' => 'Nuevo cliente registrado',
                    'descripcion' => 'Industrias 123 S.A.',
                    'fecha' => Carbon::now()->subDays(1),
                    'icono' => 'user-plus',
                    'color' => 'green',
                ],
                [
                    'tipo' => 'visita_realizada',
                    'titulo' => 'Visita realizada',
                    'descripcion' => 'Seguimiento cotización - Comercial DEF S.A.',
                    'fecha' => Carbon::now()->subDays(1)->subHours(3),
                    'icono' => 'check-circle',
                    'color' => 'blue',
                    'usuario' => 'Vendedor Demo',
                ],
                [
                    'tipo' => 'cliente_nuevo',
                    'titulo' => 'Nuevo cliente registrado',
                    'descripcion' => 'Servicios GHI Ltda.',
                    'fecha' => Carbon::now()->subDays(2),
                    'icono' => 'user-plus',
                    'color' => 'green',
                ],
            ]);
        }

        return $actividades->sortByDesc('fecha')->take(10)->values();
    }

    private function obtenerAlertas()
    {
        $alertas = [];

        // Visitas vencidas
        $visitasVencidas = Visita::where('estado', 'programada')
                               ->where('fecha_programada', '<', Carbon::today())
                               ->count();
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
        $visitasHoy = Visita::where('estado', 'programada')
                           ->whereDate('fecha_programada', Carbon::today())
                           ->count();
        if ($visitasHoy > 0) {
            $alertas[] = [
                'tipo' => 'success',
                'titulo' => 'Visitas de hoy',
                'mensaje' => "Tienes {$visitasHoy} visita(s) programada(s) para hoy",
                'icono' => 'calendar',
                'link' => '/visitas?filtro_rapido=hoy',
            ];
        }

        // Si no hay alertas reales, mostrar algunas alertas demo
        if (empty($alertas)) {
            $alertas = [
                [
                    'tipo' => 'info',
                    'titulo' => 'Visitas de hoy',
                    'mensaje' => "Tienes 3 visita(s) programada(s) para hoy",
                    'icono' => 'calendar',
                    'link' => '/visitas?filtro_rapido=hoy',
                ],
                [
                    'tipo' => 'success',
                    'titulo' => 'Cumplimiento del mes',
                    'mensaje' => "Vas muy bien este mes con un 89% de cumplimiento",
                    'icono' => 'check-circle',
                    'link' => '/visitas',
                ],
            ];
        }

        return $alertas;
    }

    private function obtenerVisitasProximas()
    {
        $visitas = Visita::with(['cliente', 'usuario'])
            ->where('estado', 'programada')
            ->where('fecha_programada', '>=', Carbon::today())
            ->orderBy('fecha_programada', 'asc')
            ->limit(5)
            ->get();

        if ($visitas->isEmpty()) {
            // Datos demo de visitas próximas
            return collect([
                [
                    'id' => 1,
                    'titulo' => 'Presentación de nuevos productos',
                    'cliente' => 'Constructora ABC S.A.',
                    'fecha' => Carbon::today()->addDays(1),
                    'usuario' => 'Vendedor Demo',
                    'tipo' => 'comercial',
                    'prioridad' => 'alta',
                    'es_hoy' => false,
                    'dias_hasta_visita' => 1,
                ],
                [
                    'id' => 2,
                    'titulo' => 'Seguimiento cotización',
                    'cliente' => 'Empresa XYZ Ltda.',
                    'fecha' => Carbon::today()->addDays(2),
                    'usuario' => 'Vendedor Demo',
                    'tipo' => 'seguimiento',
                    'prioridad' => 'media',
                    'es_hoy' => false,
                    'dias_hasta_visita' => 2,
                ],
                [
                    'id' => 3,
                    'titulo' => 'Reunión técnica',
                    'cliente' => 'Industrias 123 S.A.',
                    'fecha' => Carbon::today()->addDays(3),
                    'usuario' => 'Vendedor Demo',
                    'tipo' => 'tecnica',
                    'prioridad' => 'alta',
                    'es_hoy' => false,
                    'dias_hasta_visita' => 3,
                ],
            ]);
        }

        return $visitas->map(function ($visita) {
            return [
                'id' => $visita->id,
                'titulo' => $visita->titulo,
                'cliente' => $visita->cliente->razon_social,
                'fecha' => $visita->fecha_programada,
                'usuario' => $visita->usuario->name,
                'tipo' => $visita->tipo,
                'prioridad' => $visita->prioridad ?? 'media',
                'es_hoy' => $visita->fecha_programada->isToday(),
                'dias_hasta_visita' => Carbon::today()->diffInDays($visita->fecha_programada),
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

    private function obtenerMetricasVendedor($user)
    {
        // Métricas específicas para vendedores basadas en sus propias visitas
        $inicioMes = Carbon::now()->startOfMonth();
        
        // Visitas del vendedor este mes
        $visitasEsteVendedor = Visita::where('user_id', $user->id);
        $visitasMes = $visitasEsteVendedor->where('created_at', '>=', $inicioMes)->count();
        $visitasRealizadas = $visitasEsteVendedor->where('estado', 'realizada')
                                                ->where('fecha_realizada', '>=', $inicioMes)
                                                ->count();
        
        // Si no hay datos reales, usar datos demo
        if ($visitasMes == 0) {
            return [
                'mis_visitas' => [
                    'valor' => 28,
                    'cambio' => 15.2,
                ],
                'cumplimiento_visitas' => [
                    'valor' => 89.3,
                    'cambio' => -2.5,
                ],
                'clientes_visitados' => [
                    'valor' => 18,
                    'cambio' => 8.3,
                ],
                'visitas_pendientes' => [
                    'valor' => 6,
                    'cambio' => 0,
                ],
            ];
        }
        
        // Calcular cumplimiento
        $cumplimiento = $visitasMes > 0 ? ($visitasRealizadas / $visitasMes) * 100 : 0;
        
        return [
            'mis_visitas' => [
                'valor' => $visitasMes,
                'cambio' => 15.2, // Datos ficticios para demo
            ],
            'cumplimiento_visitas' => [
                'valor' => round($cumplimiento, 1),
                'cambio' => -2.5,
            ],
            'clientes_visitados' => [
                'valor' => $visitasEsteVendedor->distinct('cliente_id')->count(),
                'cambio' => 8.3,
            ],
            'visitas_pendientes' => [
                'valor' => $visitasEsteVendedor->where('estado', 'programada')->count(),
                'cambio' => 0,
            ],
        ];
    }

    private function obtenerMetricasGerente($user)
    {
        // Métricas específicas para gerentes - vista general del equipo
        $inicioMes = Carbon::now()->startOfMonth();
        
        // Obtener todos los vendedores
        $vendedores = User::where('rol', 'vendedor')->get();
        $totalVendedores = $vendedores->count();
        
        // Si no hay vendedores o pocos datos, usar datos demo
        if ($totalVendedores == 0) {
            return [
                'equipo_vendedores' => [
                    'valor' => 5,
                    'cambio' => 0,
                ],
                'visitas_equipo' => [
                    'valor' => 142,
                    'cambio' => 12.5,
                ],
                'tasa_cumplimiento' => [
                    'valor' => 91.2,
                    'cambio' => -1.8,
                ],
                'aprobaciones_pendientes' => [
                    'valor' => 3,
                    'cambio' => 0,
                ],
            ];
        }
        
        // Visitas de todo el equipo
        $visitasEquipo = Visita::whereIn('user_id', $vendedores->pluck('id'));
        $visitasMes = $visitasEquipo->where('created_at', '>=', $inicioMes)->count();
        $visitasRealizadas = $visitasEquipo->where('estado', 'realizada')
                                          ->where('fecha_realizada', '>=', $inicioMes)
                                          ->count();
        
        // Si no hay visitas este mes, usar datos demo
        if ($visitasMes == 0) {
            return [
                'equipo_vendedores' => [
                    'valor' => $totalVendedores,
                    'cambio' => 0,
                ],
                'visitas_equipo' => [
                    'valor' => 142,
                    'cambio' => 12.5,
                ],
                'tasa_cumplimiento' => [
                    'valor' => 91.2,
                    'cambio' => -1.8,
                ],
                'aprobaciones_pendientes' => [
                    'valor' => 3,
                    'cambio' => 0,
                ],
            ];
        }
        
        // Visitas pendientes de aprobación
        $visitasPendientes = Visita::where('estado', 'pendiente')
                                  ->whereNotNull('fecha_envio_aprobacion')
                                  ->whereNull('fecha_aprobacion')
                                  ->count();
        
        return [
            'equipo_vendedores' => [
                'valor' => $totalVendedores,
                'cambio' => 0,
            ],
            'visitas_equipo' => [
                'valor' => $visitasMes,
                'cambio' => 12.5,
            ],
            'tasa_cumplimiento' => [
                'valor' => $visitasMes > 0 ? round(($visitasRealizadas / $visitasMes) * 100, 1) : 0,
                'cambio' => -1.8,
            ],
            'aprobaciones_pendientes' => [
                'valor' => $visitasPendientes,
                'cambio' => 0,
            ],
        ];
    }

    private function obtenerDatosGraficosVendedor($user)
    {
        return [
            'visitas_semanales' => $this->obtenerVisitasSemanalesVendedor($user),
        ];
    }

    private function obtenerDatosGraficosGerente($user)
    {
        return [
            'visitas_semanales' => $this->obtenerVisitasSemanalesEquipo(),
        ];
    }

    private function obtenerVisitasSemanalesVendedor($user, $semanasAtras = 6)
    {
        // Datos ficticios específicos del vendedor
        $datosSemanales = [];
        
        try {
            $hoy = Carbon::today();

            for ($i = $semanasAtras - 1; $i >= 0; $i--) {
                $inicioSemana = $hoy->copy()->subWeeks($i)->startOfWeek();
                
                // Datos más conservadores para un vendedor individual
                $planificadas = mt_rand(8, 15);
                $realizadasPlanificadas = mt_rand(intval($planificadas * 0.7), $planificadas);
                $realizadasNoPlanificadas = mt_rand(1, 4);

                $datosSemanales[] = [
                    'semana' => $inicioSemana->format('d M'),
                    'planificadas' => $planificadas,
                    'realizadas_planificadas' => $realizadasPlanificadas,
                    'realizadas_no_planificadas' => $realizadasNoPlanificadas,
                    'total_realizadas' => $realizadasPlanificadas + $realizadasNoPlanificadas,
                ];
            }
            
            return $datosSemanales;
            
        } catch (\Exception $e) {
            \Log::error('Error generando datos semanales vendedor: ' . $e->getMessage());
            
            // Fallback con datos hardcodeados para vendedor
            return [
                ['semana' => '19 May', 'planificadas' => 12, 'realizadas_planificadas' => 10, 'realizadas_no_planificadas' => 3, 'total_realizadas' => 13],
                ['semana' => '26 May', 'planificadas' => 10, 'realizadas_planificadas' => 8, 'realizadas_no_planificadas' => 2, 'total_realizadas' => 10],
                ['semana' => '02 Jun', 'planificadas' => 14, 'realizadas_planificadas' => 12, 'realizadas_no_planificadas' => 1, 'total_realizadas' => 13],
                ['semana' => '09 Jun', 'planificadas' => 11, 'realizadas_planificadas' => 9, 'realizadas_no_planificadas' => 4, 'total_realizadas' => 13],
                ['semana' => '16 Jun', 'planificadas' => 13, 'realizadas_planificadas' => 11, 'realizadas_no_planificadas' => 2, 'total_realizadas' => 13],
                ['semana' => '23 Jun', 'planificadas' => 15, 'realizadas_planificadas' => 13, 'realizadas_no_planificadas' => 3, 'total_realizadas' => 16],
            ];
        }
    }

    private function obtenerVisitasSemanalesEquipo($semanasAtras = 6)
    {
        // Datos ficticios para el equipo completo (gerente)
        $datosSemanales = [];
        
        try {
            $hoy = Carbon::today();

            for ($i = $semanasAtras - 1; $i >= 0; $i--) {
                $inicioSemana = $hoy->copy()->subWeeks($i)->startOfWeek();
                
                // Datos más amplios para todo el equipo
                $planificadas = mt_rand(25, 45);
                $realizadasPlanificadas = mt_rand(intval($planificadas * 0.8), $planificadas);
                $realizadasNoPlanificadas = mt_rand(5, 12);

                $datosSemanales[] = [
                    'semana' => $inicioSemana->format('d M'),
                    'planificadas' => $planificadas,
                    'realizadas_planificadas' => $realizadasPlanificadas,
                    'realizadas_no_planificadas' => $realizadasNoPlanificadas,
                    'total_realizadas' => $realizadasPlanificadas + $realizadasNoPlanificadas,
                ];
            }
            
            return $datosSemanales;
            
        } catch (\Exception $e) {
            \Log::error('Error generando datos semanales equipo: ' . $e->getMessage());
            
            // Fallback con datos hardcodeados para equipo
            return [
                ['semana' => '19 May', 'planificadas' => 35, 'realizadas_planificadas' => 30, 'realizadas_no_planificadas' => 8, 'total_realizadas' => 38],
                ['semana' => '26 May', 'planificadas' => 32, 'realizadas_planificadas' => 28, 'realizadas_no_planificadas' => 6, 'total_realizadas' => 34],
                ['semana' => '02 Jun', 'planificadas' => 40, 'realizadas_planificadas' => 35, 'realizadas_no_planificadas' => 7, 'total_realizadas' => 42],
                ['semana' => '09 Jun', 'planificadas' => 38, 'realizadas_planificadas' => 33, 'realizadas_no_planificadas' => 9, 'total_realizadas' => 42],
                ['semana' => '16 Jun', 'planificadas' => 42, 'realizadas_planificadas' => 38, 'realizadas_no_planificadas' => 11, 'total_realizadas' => 49],
                ['semana' => '23 Jun', 'planificadas' => 45, 'realizadas_planificadas' => 40, 'realizadas_no_planificadas' => 10, 'total_realizadas' => 50],
            ];
        }
    }

    private function obtenerVisitasProximasVendedor($user)
    {
        $visitas = Visita::where('user_id', $user->id)
                    ->where('estado', 'programada')
                    ->where('fecha_programada', '>=', Carbon::today())
                    ->orderBy('fecha_programada', 'asc')
                    ->with(['cliente'])
                    ->limit(5)
                    ->get();

        if ($visitas->isEmpty()) {
            // Datos demo para vendedor
            return collect([
                (object)[
                    'id' => 1,
                    'titulo' => 'Presentación de nuevos productos',
                    'cliente' => (object)['razon_social' => 'Constructora ABC S.A.'],
                    'fecha_programada' => Carbon::today()->addDays(1),
                    'tipo' => 'comercial',
                ],
                (object)[
                    'id' => 2,
                    'titulo' => 'Seguimiento cotización',
                    'cliente' => (object)['razon_social' => 'Empresa XYZ Ltda.'],
                    'fecha_programada' => Carbon::today()->addDays(2),
                    'tipo' => 'seguimiento',
                ],
                (object)[
                    'id' => 3,
                    'titulo' => 'Reunión técnica',
                    'cliente' => (object)['razon_social' => 'Industrias 123 S.A.'],
                    'fecha_programada' => Carbon::today()->addDays(3),
                    'tipo' => 'tecnica',
                ],
            ]);
        }

        return $visitas;
    }

    private function obtenerVisitasProximasGerente($user)
    {
        $vendedores = User::where('rol', 'vendedor')->pluck('id');
        
        $visitas = Visita::whereIn('user_id', $vendedores)
                    ->where('estado', 'programada')
                    ->where('fecha_programada', '>=', Carbon::today())
                    ->orderBy('fecha_programada', 'asc')
                    ->with(['cliente', 'usuario'])
                    ->limit(10)
                    ->get();

        if ($visitas->isEmpty()) {
            // Datos demo para gerente
            return collect([
                (object)[
                    'id' => 1,
                    'titulo' => 'Presentación de nuevos productos',
                    'cliente' => (object)['razon_social' => 'Constructora ABC S.A.'],
                    'usuario' => (object)['name' => 'Vendedor Demo'],
                    'fecha_programada' => Carbon::today()->addDays(1),
                    'tipo' => 'comercial',
                ],
                (object)[
                    'id' => 2,
                    'titulo' => 'Seguimiento cotización',
                    'cliente' => (object)['razon_social' => 'Empresa XYZ Ltda.'],
                    'usuario' => (object)['name' => 'Vendedor Demo'],
                    'fecha_programada' => Carbon::today()->addDays(2),
                    'tipo' => 'seguimiento',
                ],
                (object)[
                    'id' => 3,
                    'titulo' => 'Reunión técnica',
                    'cliente' => (object)['razon_social' => 'Industrias 123 S.A.'],
                    'usuario' => (object)['name' => 'Vendedor Demo'],
                    'fecha_programada' => Carbon::today()->addDays(3),
                    'tipo' => 'tecnica',
                ],
            ]);
        }

        return $visitas;
    }

    private function obtenerAlertasVendedor($user)
    {
        $alertas = [];
        
        // Visitas vencidas
        $visitasVencidas = Visita::where('user_id', $user->id)
                                ->where('estado', 'programada')
                                ->where('fecha_programada', '<', Carbon::today())
                                ->count();
        
        if ($visitasVencidas > 0) {
            $alertas[] = [
                'tipo' => 'warning',
                'mensaje' => "Tienes {$visitasVencidas} visita(s) vencida(s)",
                'accion' => 'Ver visitas vencidas',
                'url' => '/visitas?estado=programada&vencidas=1'
            ];
        }
        
        // Visitas para hoy
        $visitasHoy = Visita::where('user_id', $user->id)
                           ->where('estado', 'programada')
                           ->whereDate('fecha_programada', Carbon::today())
                           ->count();
        
        if ($visitasHoy > 0) {
            $alertas[] = [
                'tipo' => 'info',
                'mensaje' => "Tienes {$visitasHoy} visita(s) programada(s) para hoy",
                'accion' => 'Ver agenda de hoy',
                'url' => '/visitas?fecha=' . Carbon::today()->format('Y-m-d')
            ];
        }

        // Si no hay alertas reales, mostrar alertas demo
        if (empty($alertas)) {
            $alertas = [
                [
                    'tipo' => 'info',
                    'mensaje' => "Tienes 3 visita(s) programada(s) para hoy",
                    'accion' => 'Ver agenda de hoy',
                    'url' => '/visitas'
                ],
                [
                    'tipo' => 'success',
                    'mensaje' => "Vas muy bien este mes con un 89% de cumplimiento",
                    'accion' => 'Ver mi desempeño',
                    'url' => '/reportes/mis-visitas'
                ],
            ];
        }
        
        return $alertas;
    }

    private function obtenerAlertasGerente($user)
    {
        $alertas = [];
        
        // Planificaciones pendientes de aprobación
        $planificacionesPendientes = Visita::where('estado', 'pendiente')
                                          ->whereNotNull('fecha_envio_aprobacion')
                                          ->whereNull('fecha_aprobacion')
                                          ->count();
        
        if ($planificacionesPendientes > 0) {
            $alertas[] = [
                'tipo' => 'warning',
                'mensaje' => "Hay {$planificacionesPendientes} planificación(es) pendiente(s) de aprobación",
                'accion' => 'Revisar planificaciones',
                'url' => '/visitas?estado=pendiente'
            ];
        }
        
        // Vendedores con bajo cumplimiento (simulado)
        $vendedoresBajoCumplimiento = 1; // Dato ficticio
        if ($vendedoresBajoCumplimiento > 0) {
            $alertas[] = [
                'tipo' => 'info',
                'mensaje' => "{$vendedoresBajoCumplimiento} vendedor(es) con cumplimiento bajo este mes",
                'accion' => 'Ver reporte de cumplimiento',
                'url' => '/reportes/cumplimiento'
            ];
        }

        // Si no hay alertas reales, mostrar alertas demo para gerente
        if (empty($alertas)) {
            $alertas = [
                [
                    'tipo' => 'warning',
                    'mensaje' => "Hay 2 planificación(es) pendiente(s) de aprobación",
                    'accion' => 'Revisar planificaciones',
                    'url' => '/visitas'
                ],
                [
                    'tipo' => 'info',
                    'mensaje' => "El equipo tiene un cumplimiento del 91% este mes",
                    'accion' => 'Ver reporte del equipo',
                    'url' => '/reportes/equipo'
                ],
                [
                    'tipo' => 'success',
                    'mensaje' => "5 vendedores activos en el sistema",
                    'accion' => 'Gestionar equipo',
                    'url' => '/usuarios'
                ],
            ];
        }
        
        return $alertas;
    }
}
