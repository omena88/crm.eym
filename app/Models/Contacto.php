<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contacto extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'nombre',
        'apellidos',
        'puesto',
        'telefono',
        'celular',
        'email',
        'email_alternativo',
        'es_contacto_principal',
        'notas',
    ];

    protected $casts = [
        'es_contacto_principal' => 'boolean',
    ];

    protected $appends = [
        'nombre_completo',
    ];

    // Relaciones
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    // Accesorios
    public function getNombreCompletoAttribute(): string
    {
        return trim($this->nombre . ' ' . $this->apellidos);
    }

    public function getTelefonoPreferidoAttribute(): ?string
    {
        return $this->celular ?: $this->telefono;
    }

    public function getEmailPreferidoAttribute(): ?string
    {
        return $this->email ?: $this->email_alternativo;
    }

    // Scopes
    public function scopePrincipales($query)
    {
        return $query->where('es_contacto_principal', true);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nombre', 'like', "%{$search}%")
              ->orWhere('apellidos', 'like', "%{$search}%")
              ->orWhere('puesto', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    // Eventos del modelo
    protected static function boot()
    {
        parent::boot();

        // Cuando se marca un contacto como principal, desmarcar los otros del mismo cliente
        static::saving(function ($contacto) {
            if ($contacto->es_contacto_principal) {
                static::where('cliente_id', $contacto->cliente_id)
                    ->where('id', '!=', $contacto->id)
                    ->update(['es_contacto_principal' => false]);
            }
        });

        // Asegurar que siempre haya un contacto principal
        static::created(function ($contacto) {
            $cliente = $contacto->cliente;
            if (!$cliente->contactos()->where('es_contacto_principal', true)->exists()) {
                $contacto->update(['es_contacto_principal' => true]);
            }
        });

        // Prevenir eliminar el último contacto principal
        static::deleting(function ($contacto) {
            if ($contacto->es_contacto_principal) {
                $cliente = $contacto->cliente;
                $otrosContactos = $cliente->contactos()->where('id', '!=', $contacto->id)->get();
                
                if ($otrosContactos->count() > 0) {
                    // Hacer principal al primer contacto disponible
                    $otrosContactos->first()->update(['es_contacto_principal' => true]);
                } else {
                    // No permitir eliminar si es el único contacto
                    throw new \Exception('No se puede eliminar el último contacto del cliente. Un cliente debe tener al menos un contacto principal.');
                }
            }
        });
    }
}
