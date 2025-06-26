<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    use HasFactory;

    protected $fillable = [
        'producto_id',
        'nombre',
        'ruta',
        'tipo_archivo',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
