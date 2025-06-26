<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::with(['canales', 'documentos'])->paginate(10);

        return Inertia::render('Productos/Index', [
            'productos' => $productos,
        ]);
    }
}
