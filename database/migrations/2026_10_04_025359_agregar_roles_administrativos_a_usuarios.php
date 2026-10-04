<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Amplia la jerarquia de roles del panel administrativo.
     *
     * Antes: usu_rol solo aceptaba 'estudiante' y 'admin'.
     * Ahora: 'estudiante', 'administrador', 'analista' y 'taquillero'.
     *
     * Laravel creo la columna como VARCHAR con una restriccion CHECK
     * (usuarios_usu_rol_check), por eso hay que reemplazarla: primero se
     * elimina la restriccion antigua, se normalizan los datos y luego se
     * crea la nueva con los cuatro roles.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE usuarios DROP CONSTRAINT IF EXISTS usuarios_usu_rol_check');

        // El rol "admin" pasa a llamarse "administrador"
        DB::table('usuarios')
            ->where('usu_rol', 'admin')
            ->update(['usu_rol' => 'administrador']);

        DB::statement(
            'ALTER TABLE usuarios ADD CONSTRAINT usuarios_usu_rol_check '
            ."CHECK (usu_rol IN ('estudiante', 'administrador', 'analista', 'taquillero'))"
        );
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE usuarios DROP CONSTRAINT IF EXISTS usuarios_usu_rol_check');

        // Volvemos aADMINISTRADOR -> admin; los roles nuevos pasan a admin
        DB::table('usuarios')
            ->whereIn('usu_rol', ['administrador', 'analista', 'taquillero'])
            ->update(['usu_rol' => 'admin']);

        DB::statement(
            'ALTER TABLE usuarios ADD CONSTRAINT usuarios_usu_rol_check '
            ."CHECK (usu_rol IN ('estudiante', 'admin'))"
        );
    }
};
