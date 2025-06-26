<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    /** @use HasFactory<\Database\Factories\PedidoFactory> */
    use HasFactory;

    protected $table = 'pedidos';

    protected $fillable = [
        'cliente_id',
        'user_id',
        'cotizacion_id',
        'codigo',
        'fecha_pedido',
        'fecha_entrega_prevista',
        'monto_total',
        'estado',
        'direccion_envio',
    ];

    protected $casts = [
        'fecha_pedido' => 'date',
        'fecha_entrega_prevista' => 'date',
        'monto_total' => 'decimal:2',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class);
    }
}
