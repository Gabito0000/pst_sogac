<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bitácora de cambios del sistema: qué registro se tocó, quién lo tocó,
 * en qué pantalla y con qué valores antes y después.
 *
 * Es una tabla aparte y no una vista sobre el historial_estado_solicitudes
 * porque el historial de estados solo existe para las solicitudes y solo
 * cuando el admin las mueve de estado. Aqui queda registrado cualquier cambio:
 * altas y bajas de tramites y requisitos, edicion de preguntas frecuentes,
 * estados del chat, y por supuesto las propias solicitudes.
 *
 * Los valores se guardan ya resueltos a texto ("pendiente" en vez de 1) porque
 * una bitacora que muestra claves foraneas es indescifrable y, ademas, deja de
 * tener sentido cuando el registro al que apunta ya no existe.
 *
 * Prefijo de columnas: hcm_, como hes_ en historial_estado_solicitudes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_cambios', function (Blueprint $table) {
            $table->increments('hcm_id');

            // Quien ejecuto el cambio. NullOnDelete y no cascade a proposito:
            // si se borra un usuario, su rastro sigue teniendo que ser legible.
            $table->unsignedInteger('hcm_usu_id')->nullable();

            // Que registro se toco. hcm_entidad es el nombre de la clase
            // ("Solicitud", "Requisito") y hcm_entidad_id su clave primaria.
            $table->string('hcm_entidad', 60);
            $table->unsignedBigInteger('hcm_entidad_id')->nullable();

            // Referencia directa a la solicitud, para poder mostrarle al
            // estudiante la bitacora de su propio tramite sin recorrer el resto.
            $table->unsignedInteger('hcm_sol_id')->nullable();

            $table->string('hcm_accion', 20);
            $table->string('hcm_resumen', 255)->nullable();

            // Lista de {campo, anterior, nuevo} ya traducida a texto legible.
            $table->json('hcm_cambios')->nullable();

            // Contexto de donde salio el cambio.
            $table->string('hcm_ruta', 255)->nullable();
            $table->string('hcm_ip', 45)->nullable();
            $table->string('hcm_navegador', 255)->nullable();

            $table->timestamp('hcm_fecha')->useCurrent();

            $table->foreign('hcm_usu_id', 'fk_hcm_usu')->references('usu_id')->on('usuarios')->nullOnDelete();
            $table->foreign('hcm_sol_id', 'fk_hcm_sol')->references('sol_id')->on('solicitudes')->nullOnDelete();

            $table->index(['hcm_entidad', 'hcm_entidad_id'], 'idx_hcm_entidad');
            $table->index('hcm_sol_id', 'idx_hcm_sol');
            $table->index('hcm_usu_id', 'idx_hcm_usu');
            $table->index('hcm_fecha', 'idx_hcm_fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_cambios');
    }
};
