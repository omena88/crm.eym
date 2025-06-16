<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'ruc',
        'razon_social',
        'sector',
        'estado',
        'notas',
        'telefono',
        'website',
        'direccion',
        'tamaño',
        'valor_potencial',
        'probabilidad_cierre',
        'etiquetas',
        'fecha_ultimo_contacto',
    ];

    protected $casts = [
        'etiquetas' => 'array',
        'fecha_ultimo_contacto' => 'datetime',
        'valor_potencial' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cliente) {
            if (empty($cliente->codigo)) {
                $cliente->codigo = self::generateNextCode();
            }
        });
    }

    public static function generateNextCode(): string
    {
        $lastCliente = self::orderBy('id', 'desc')->first();
        $nextNumber = $lastCliente ? (int) substr($lastCliente->codigo, 4) + 1 : 1;
        return 'EMP-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    // Relaciones
    public function contactos(): HasMany
    {
        return $this->hasMany(Contacto::class);
    }

    public function contactoPrincipal(): HasOne
    {
        return $this->hasOne(Contacto::class)->where('es_contacto_principal', true);
    }

    public function visitas(): HasMany
    {
        return $this->hasMany(Visita::class);
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado', 'Pendiente');
    }

    public function scopeVisitados($query)
    {
        return $query->where('estado', 'Visitado');
    }

    public function scopePorCotizar($query)
    {
        return $query->where('estado', 'Por cotizar');
    }

    public function scopeCotizados($query)
    {
        return $query->where('estado', 'Cotizado');
    }

    public function scopeAprobados($query)
    {
        return $query->where('estado', 'Aprobado');
    }

    public function scopeRechazados($query)
    {
        return $query->where('estado', 'Rechazado');
    }

    public function scopeActivos($query)
    {
        return $query->whereIn('estado', ['Visitado', 'Por cotizar', 'Cotizado', 'Aprobado']);
    }

    public function scopePotenciales($query)
    {
        return $query->whereIn('estado', ['Pendiente', 'Visitado', 'Por cotizar']);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('razon_social', 'like', "%{$search}%")
              ->orWhere('codigo', 'like', "%{$search}%")
              ->orWhere('ruc', 'like', "%{$search}%")
              ->orWhere('sector', 'like', "%{$search}%");
        });
    }

    // Accesorios
    public function getEstadoBadgeColorAttribute(): string
    {
        return match($this->estado) {
            'Pendiente' => 'yellow',
            'Visitado' => 'blue',
            'Por cotizar' => 'purple',
            'Cotizado' => 'orange',
            'Aprobado' => 'green',
            'Rechazado' => 'red',
            default => 'gray'
        };
    }

    public function getTotalVisitasAttribute(): int
    {
        return $this->visitas()->count();
    }

    public function getUltimaVisitaAttribute()
    {
        return $this->visitas()->latest('fecha_programada')->first();
    }

    public static function getEstadosOptions(): array
    {
        return [
            'Pendiente' => 'Pendiente',
            'Visitado' => 'Visitado',
            'Por cotizar' => 'Por cotizar',
            'Cotizado' => 'Cotizado',
            'Aprobado' => 'Aprobado',
            'Rechazado' => 'Rechazado',
        ];
    }

    public static function getSectoresOptions(): array
    {
        return [
            'Sector 01' => 'Sector 01',
            'Sector 02' => 'Sector 02',
            'Sector 03' => 'Sector 03',
            'Sector 04' => 'Sector 04',
            'Sector 05' => 'Sector 05',
            'Sector 06' => 'Sector 06',
            'Sector 07' => 'Sector 07',
            'Sector 08' => 'Sector 08',
            'Sector 09' => 'Sector 09',
            'Sector 10' => 'Sector 10',
        ];
    }
}
