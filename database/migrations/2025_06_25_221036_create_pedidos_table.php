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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('cotizacion_id')->nullable()->constrained('cotizaciones')->onDelete('set null');
            $table->string('codigo')->unique();
            $table->date('fecha_pedido');
            $table->date('fecha_entrega_prevista')->nullable();
            $table->decimal('monto_total', 15, 2);
            $table->string('estado')->default('pendiente'); // pendiente, en_proceso, completado, cancelado
            $table->text('direccion_envio')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
