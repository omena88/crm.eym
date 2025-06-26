<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ContactoController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\VisitaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\PedidoController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas de Clientes
    Route::resource('clientes', ClienteController::class);
    Route::resource('contactos', ContactoController::class);
    Route::resource('cotizaciones', CotizacionController::class);

    // Rutas de Visitas
    Route::resource('visitas', VisitaController::class);
    Route::get('/visitas/planificacion/datos', [VisitaController::class, 'planificacionDatos'])->name('visitas.planificacion.datos');
    Route::post('/visitas/planificacion/guardar', [VisitaController::class, 'guardarPlanificacion'])->name('visitas.guardarPlanificacion');
    Route::post('/visitas/enviar-planificacion', [VisitaController::class, 'enviarPlanificacion'])->name('visitas.enviarPlanificacion');
    Route::post('/visitas/revertir-planificacion', [VisitaController::class, 'revertirPlanificacion'])->name('visitas.revertirPlanificacion');
    Route::post('/visitas/aprobar-planificacion', [VisitaController::class, 'aprobarPlanificacion'])->name('visitas.aprobarPlanificacion');
    Route::post('/visitas/{visita}/realizar', [VisitaController::class, 'realizarVisita'])->name('visitas.realizar');
    Route::post('/visitas/{visita}/completar', [VisitaController::class, 'completarVisita'])->name('visitas.completar');
    Route::post('/visitas/no-planificada', [VisitaController::class, 'crearNoPlanificada'])->name('visitas.crearNoPlanificada');
    Route::put('/visitas/{visita}/update-comentarios', [VisitaController::class, 'updateComentarios'])->name('visitas.updateComentarios');
    Route::get('/visitas', [VisitaController::class, 'index'])->name('visitas.index');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Nueva ruta para visualizar el historial de un cliente
    Route::get('/clientes/{cliente}/historial', [ClienteController::class, 'historial'])->name('clientes.historial');
    Route::get('/clientes-list', [ClienteController::class, 'list'])->name('clientes.list');

    Route::get('/cotizaciones', [CotizacionController::class, 'index'])->name('cotizaciones.index');
    Route::get('/cotizaciones/{visita}', [CotizacionController::class, 'show'])->name('cotizaciones.show');
    Route::post('/cotizaciones/simular-accion', [CotizacionController::class, 'simularAccion'])->name('cotizaciones.simularAccion');

    Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');

    // Rutas de Pedidos
    Route::resource('pedidos', PedidoController::class);
    Route::post('/pedidos/simular-accion', [PedidoController::class, 'simularAccion'])->name('pedidos.simularAccion');
});

require __DIR__.'/auth.php';
