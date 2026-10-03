<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Esta migración agrega las columnas que le faltaban a tipo_solicitudes
     * para poder hacer lo que el admin necesita: dejar un trámite disponible
     * solo durante una ventana de fechas (ej: "solo del 1 al 30 de septiembre").
     */
    public function up(): void
    {
        Schema::table('tipo_solicitudes', function (Blueprint $table) {
            // nullable = true porque un trámite puede quedar activo indefinidamente,
            // sin fecha de cierre obligatoria (el admin decide caso por caso)
            $table->date('tsi_fecha_inicio')->nullable()->after('tsi_estado_tipo');
            $table->date('tsi_fecha_fin')->nullable()->after('tsi_fecha_inicio');
        });
    }

    public function down(): void
    {
        Schema::table('tipo_solicitudes', function (Blueprint $table) {
            $table->dropColumn(['tsi_fecha_inicio', 'tsi_fecha_fin']);
        });
    }
};
