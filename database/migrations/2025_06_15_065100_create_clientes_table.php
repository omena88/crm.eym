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
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Vendedor asignado
            $table->string('ruc', 11)->unique();
            $table->string('razon_social');
            $table->string('nombre_comercial')->nullable();
            $table->string('sector');
            $table->string('estado')->default('potencial'); // potencial, visitado, cotizado, cliente, inactivo
            $table->text('notas')->nullable();
            $table->string('telefono')->nullable();
            $table->string('website')->nullable();
            $table->string('direccion')->nullable();
            $table->decimal('valor_potencial', 10, 2)->nullable();
            $table->integer('probabilidad_cierre')->nullable(); // 0-100
            $table->timestamp('ultima_actividad')->nullable();
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
