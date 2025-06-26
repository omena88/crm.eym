<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'precio_base',
        'unidad',
    ];

    public function canales()
    {
        return $this->belongsToMany(Canal::class, 'canal_producto')->withPivot('precio')->withTimestamps();
    }

    public function documentos()
    {
        return $this->hasMany(Documento::class);
    }
}
