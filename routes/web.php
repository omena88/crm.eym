<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ContactoController;
use App\Http\Controllers\VisitaController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Ruta de prueba simple
Route::get('/test', function () {
    return '<h1>¡Laravel funciona!</h1><p>Servidor corriendo correctamente</p>';
});

// Ruta de prueba para Inertia
Route::get('/test-inertia', function () {
    return Inertia::render('TestPage', [
        'message' => '¡Inertia funciona!',
        'user' => auth()->user()
    ]);
});

// Ruta principal - siempre muestra el login
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas de Clientes
    Route::resource('clientes', ClienteController::class);
    Route::get('clientes/export', [ClienteController::class, 'export'])->name('clientes.export');
    Route::get('clientes/template/download', [ClienteController::class, 'downloadTemplate'])->name('clientes.template');
    Route::post('clientes/import', [ClienteController::class, 'import'])->name('clientes.import');

    // Rutas de Contactos
    Route::get('contactos/export', [ContactoController::class, 'export'])->name('contactos.export');
    Route::resource('contactos', ContactoController::class);
    Route::post('contactos/{contacto}/make-principal', [ContactoController::class, 'makePrincipal'])->name('contactos.make-principal');

    // Rutas de Visitas
    Route::resource('visitas', VisitaController::class);
    Route::get('visitas/planificacion/semanal', [VisitaController::class, 'planificacionSemanal'])->name('visitas.planificacion');
    Route::post('visitas/planificacion/enviar', [VisitaController::class, 'enviarPlanificacion'])->name('visitas.enviar-planificacion');
    Route::patch('visitas/planificacion/{id}/aprobar', [VisitaController::class, 'aprobarPlanificacion'])->name('visitas.aprobar-planificacion');
    Route::patch('visitas/{visita}/completar', [VisitaController::class, 'completarVisita'])->name('visitas.completar');
    Route::post('visitas/no-planificada', [VisitaController::class, 'crearNoPlanificada'])->name('visitas.no-planificada');
});

require __DIR__.'/auth.php';
