<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('visitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->foreignId('vendedor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('gerente_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->text('objetivos')->nullable();
            
            $table->date('fecha_planificada');
            $table->time('hora_planificada')->nullable();
            $table->integer('duracion_estimada')->default(60); // minutos
            
            $table->enum('tipo', ['planificada', 'no_planificada'])->default('planificada');
            $table->enum('estado', ['pendiente', 'aprobada', 'rechazada', 'completada', 'cancelada'])->default('pendiente');
            
            // Datos de la visita realizada
            $table->datetime('fecha_realizada')->nullable();
            $table->text('resumen_visita')->nullable();
            $table->text('acuerdos')->nullable();
            $table->text('proximos_pasos')->nullable();
            $table->decimal('probabilidad_cierre', 5, 2)->nullable(); // 0.00 a 100.00
            $table->decimal('valor_estimado', 12, 2)->nullable();
            
            // Semana de planificación
            $table->integer('semana')->nullable(); // número de semana del año
            $table->integer('año')->nullable();
            
            // Campos de aprobación
            $table->datetime('fecha_aprobacion')->nullable();
            $table->datetime('fecha_envio_aprobacion')->nullable();
            $table->text('comentarios_gerente')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index(['vendedor_id', 'fecha_planificada']);
            $table->index(['estado', 'tipo']);
            $table->index(['semana', 'año']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitas');
    }
};
