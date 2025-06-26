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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'rol')) {
                $table->string('rol', 50)->default('vendedor')->after('password');
            } else {
                $table->string('rol', 50)->default('vendedor')->change();
            }

            if (!Schema::hasColumn('users', 'rol_original')) {
                $table->string('rol_original', 50)->nullable()->after('rol');
            } else {
                $table->string('rol_original', 50)->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // No es necesario hacer nada aquí para este caso
        });
    }
};
