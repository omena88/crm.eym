<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ruc',
        'razon_social',
        'nombre_comercial',
        'sector',
        'estado',
        'notas',
        'telefono',
        'website',
        'direccion',
        'valor_potencial',
        'probabilidad_cierre',
        'ultima_actividad',
    ];

    protected $casts = [
        'ultima_actividad' => 'datetime',
        'valor_potencial' => 'decimal:2',
    ];

    // Relaciones
    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function contactos(): HasMany
    {
        return $this->hasMany(Contacto::class);
    }

    public function contacto_principal(): HasOne
    {
        return $this->hasOne(Contacto::class)->where('es_contacto_principal', true);
    }

    public function visitas(): HasMany
    {
        return $this->hasMany(Visita::class);
    }

    public function cotizaciones(): HasMany
    {
        return $this->hasMany(Cotizacion::class);
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }

    // Scopes de búsqueda y filtro
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('razon_social', 'like', "%{$search}%")
              ->orWhere('nombre_comercial', 'like', "%{$search}%")
              ->orWhere('ruc', 'like', "%{$search}%")
              ->orWhere('sector', 'like', "%{$search}%");
        });
    }

    // Accesorios
    public function getEstadoBadgeColorAttribute(): string
    {
        return match($this->estado) {
            'potencial' => 'yellow',
            'visitado' => 'blue',
            'cotizado' => 'purple',
            'cliente' => 'green',
            'inactivo' => 'gray',
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
            'potencial' => 'Potencial',
            'visitado' => 'Visitado',
            'cotizado' => 'Cotizado',
            'cliente' => 'Cliente',
            'inactivo' => 'Inactivo',
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
