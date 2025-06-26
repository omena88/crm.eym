<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Canal extends Model
{
    use HasFactory;

    protected $table = 'canales';

    protected $fillable = ['nombre'];

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'canal_producto')->withPivot('precio')->withTimestamps();
    }
}
