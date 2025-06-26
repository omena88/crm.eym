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
        Schema::table('visitas', function (Blueprint $table) {
            if (Schema::hasColumn('visitas', 'hora_planificada')) {
                $table->dropColumn('hora_planificada');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visitas', function (Blueprint $table) {
            if (!Schema::hasColumn('visitas', 'hora_planificada')) {
                $table->time('hora_planificada')->nullable();
            }
        });
    }
};
