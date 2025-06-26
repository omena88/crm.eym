<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Visita extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'user_id',
        'gerente_id',
        'titulo',
        'descripcion',
        'objetivos',
        'fecha_programada',
        'duracion_estimada',
        'duracion_real',
        'tipo',
        'estado',
        'fecha_realizada',
        'resultado',
        'notas',
        'comentarios',
        'satisfaccion_cliente',
        'objetivos_alcanzados',
        'requiere_seguimiento',
        'fecha_siguiente_contacto',
        'semana',
        'año',
        'fecha_aprobacion',
        'fecha_envio_aprobacion',
        'comentarios_gerente',
        // Campos legacy para compatibilidad
        'resumen_visita',
        'acuerdos',
        'proximos_pasos',
        'probabilidad_cierre',
        'valor_estimado',
        'prioridad',
        'turno',
        // Campos para clasificación
        'tipo_planificacion' // 'planificada' o 'no_planificada'
    ];

    protected $casts = [
        'fecha_programada' => 'datetime:Y-m-d',
        'fecha_realizada' => 'datetime',
        'fecha_aprobacion' => 'datetime',
        'fecha_envio_aprobacion' => 'datetime',
        'fecha_siguiente_contacto' => 'date',
        'requiere_seguimiento' => 'boolean',
        'objetivos_alcanzados' => 'boolean',
        'satisfaccion_cliente' => 'integer',
        'duracion_estimada' => 'integer',
        'duracion_real' => 'integer',
        'semana' => 'integer',
        'año' => 'integer',
        'probabilidad_cierre' => 'decimal:2',
        'valor_estimado' => 'decimal:2'
    ];

    // Relaciones
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scopes
    public function scopePorSemana($query, $semana, $año)
    {
        $startOfWeek = now()->setISODate($año, $semana)->startOfWeek();
        $endOfWeek = now()->setISODate($año, $semana)->endOfWeek();
        
        return $query->whereBetween('fecha_programada', [$startOfWeek, $endOfWeek]);
    }

    public function scopePendientesAprobacion($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeAprobadas($query)
    {
        return $query->where('estado', 'realizada');
    }

    public function scopeCompletadas($query)
    {
        return $query->where('estado', 'realizada');
    }

    public function scopePlanificadas($query)
    {
        return $query->where('tipo', 'planificada');
    }

    public function scopeNoPlanificadas($query)
    {
        return $query->where('tipo', 'no_planificada');
    }

    // Scopes adicionales para compatibilidad
    public function scopeRealizadas($query)
    {
        return $query->where('estado', 'completada');
    }

    public function scopeProgramadas($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeCanceladas($query)
    {
        return $query->where('estado', 'cancelada');
    }

    public function scopeHoy($query)
    {
        return $query->whereDate('fecha_programada', Carbon::today());
    }

    public function scopeEstaSemana($query)
    {
        return $query->whereBetween('fecha_programada', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    public function scopeVencidas($query)
    {
        return $query->where('estado', 'pendiente')
                    ->where('fecha_programada', '<', Carbon::now());
    }

    public function scopeProximas($query)
    {
        return $query->where('estado', 'pendiente')
                    ->where('fecha_programada', '>', Carbon::now())
                    ->orderBy('fecha_programada', 'asc');
    }

    // Métodos auxiliares
    public function puedeSerAprobada()
    {
        return $this->estado === 'pendiente';
    }

    public function puedeSerCompletada()
    {
        return $this->estado === 'programada';
    }

    public function esPlanificada()
    {
        return $this->tipo === 'planificada';
    }

    public function esNoPlanificada()
    {
        return $this->tipo === 'no_planificada';
    }

    public function estaCompletada()
    {
        return $this->estado === 'realizada';
    }




    // Accesorios
    public function getEstadoBadgeColorAttribute(): string
    {
        return match($this->estado) {
            'programada' => 'blue',
            'realizada' => 'green',
            'cancelada' => 'red',
            default => 'gray'
        };
    }

    public function getPrioridadBadgeColorAttribute(): string
    {
        return match($this->prioridad) {
            'baja' => 'gray',
            'media' => 'yellow',
            'alta' => 'orange',
            'urgente' => 'red',
            default => 'gray'
        };
    }

    public function getTipoBadgeColorAttribute(): string
    {
        return match($this->tipo) {
            'comercial' => 'green',
            'tecnica' => 'blue',
            'seguimiento' => 'purple',
            'postventa' => 'indigo',
            default => 'gray'
        };
    }

    public function getDuracionFormateadaAttribute(): string
    {
        $duracion = $this->duracion_real ?: $this->duracion_estimada;
        if (!$duracion) return 'No especificada';
        
        $horas = floor($duracion / 60);
        $minutos = $duracion % 60;
        
        $resultado = [];
        if ($horas > 0) $resultado[] = $horas . 'h';
        if ($minutos > 0) $resultado[] = $minutos . 'm';
        
        return implode(' ', $resultado);
    }

    public function getEsVencidaAttribute(): bool
    {
        return $this->estado === 'programada' && $this->fecha_programada->isPast();
    }

    public function getEsHoyAttribute(): bool
    {
        return $this->fecha_programada->isToday();
    }

    public function getDiasHastaVisitaAttribute(): int
    {
        return $this->fecha_programada->diffInDays(Carbon::now(), false);
    }

    // Métodos de instancia
    public function marcarComoRealizada(?string $resultado = null, ?int $duracionReal = null): void
    {
        $this->update([
            'estado' => 'realizada',
            'fecha_realizada' => Carbon::now(),
            'resultado' => $resultado,
            'duracion_real' => $duracionReal,
        ]);

        // Actualizar fecha de último contacto del cliente
        $this->cliente->update([
            'fecha_ultimo_contacto' => Carbon::now()
        ]);
    }

    public function cancelar(?string $motivo = null): void
    {
        $this->update([
            'estado' => 'cancelada',
            'notas' => $this->notas ? $this->notas . "\n\nMotivo de cancelación: " . $motivo : "Motivo de cancelación: " . $motivo,
        ]);
    }

    public function reprogramar(Carbon $nuevaFecha): void
    {
        $this->update([
            'fecha_programada' => $nuevaFecha,
            'estado' => 'programada',
        ]);
    }

    // Eventos del modelo para asegurar que semana y año se actualicen
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($visita) {
            if ($visita->fecha_programada) {
                $fecha = Carbon::parse($visita->fecha_programada);
                $visita->semana = $fecha->week;
                $visita->año = $fecha->year;
            }
        });
    }
}
