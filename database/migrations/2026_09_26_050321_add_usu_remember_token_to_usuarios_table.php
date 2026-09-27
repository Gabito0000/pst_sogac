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
        Schema::table('usuarios', function (Blueprint $table) {
            // Token "recuerdame" de Laravel (mantiene la sesion abierta
            // aunque se cierre el navegador). Nombre con prefijo usu_
            // segun la convencion del proyecto.
            $table->string('usu_remember_token', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn('usu_remember_token');
        });
    }
};
