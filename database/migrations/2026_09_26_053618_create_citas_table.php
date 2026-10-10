<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('citas', function (Blueprint $table) {
            $table->increments('cit_id');
            
            // 1. OBLIGATORIO: Saber a qué estudiante pertenece la cita
            $table->unsignedInteger('cit_usu_id');
            $table->foreign('cit_usu_id', 'fk_cit_usu')->references('usu_id')->on('usuarios')->onDelete('cascade');

            // 2. OPCIONAL: La solicitud asociada (puede ser null para citas independientes)
            // Se mantiene el unique() para que una solicitud formal siga teniendo solo 1 cita
            $table->unsignedInteger('cit_sol_id')->nullable()->unique();
            $table->foreign('cit_sol_id', 'fk_cit_sol')->references('sol_id')->on('solicitudes')->onDelete('cascade');

            // 3. El texto libre para cuando no hay solicitud
            $table->string('cit_motivo')->nullable();

            // 4. El bloque de tiempo (como dura 20 min exactos, basta con la fecha/hora de inicio)
            $table->dateTime('cit_fecha_hora')->nullable();
            
            $table->string('cit_estado', 20)->default('pendiente');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};