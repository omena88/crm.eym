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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique(); // EMP-000001
            $table->string('ruc', 11)->unique();
            $table->string('razon_social');
            $table->string('sector');
            $table->enum('estado', ['Pendiente', 'Visitado', 'Por cotizar', 'Cotizado', 'Aprobado', 'Rechazado'])->default('Pendiente');
            $table->text('notas')->nullable();
            $table->string('telefono')->nullable();
            $table->string('website')->nullable();
            $table->string('direccion')->nullable();
            $table->enum('tamaño', ['pequeña', 'mediana', 'grande'])->default('mediana');
            $table->decimal('valor_potencial', 10, 2)->nullable();
            $table->integer('probabilidad_cierre')->nullable(); // 0-100
            $table->json('etiquetas')->nullable();
            $table->timestamp('fecha_ultimo_contacto')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
