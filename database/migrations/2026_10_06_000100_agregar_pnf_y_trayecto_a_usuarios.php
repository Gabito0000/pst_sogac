<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Guarda el PNF y el trayecto que el formulario de registro ya pide.
     *
     * El formulario exigia ambos campos (selects con 'required') y el
     * controlador intentaba escribirlos en usu_pnf / usu_trayecto, pero esas
     * columnas no existian: el dato se descartaba en silencio. Se anaden aqui
     * en vez de quitar los campos, porque son parte de la ficha academica del
     * estudiante y no se debe cambiar el formulario del corporativo.
     */
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->string('usu_pnf', 50)->nullable()->after('usu_numero_telefono');
            $table->string('usu_trayecto', 20)->nullable()->after('usu_pnf');
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn(['usu_pnf', 'usu_trayecto']);
        });
    }
};
