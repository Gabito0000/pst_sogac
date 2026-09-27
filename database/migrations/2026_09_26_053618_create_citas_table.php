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
        Schema::create('citas', function (Blueprint $table) {
            $table->increments('cit_id');
            // Una cita pertenece a una solicitud (una por solicitud).
            $table->unsignedInteger('cit_sol_id')->unique();
            $table->foreign('cit_sol_id', 'fk_cit_sol')->references('sol_id')->on('solicitudes')->onDelete('cascade');
            // Fecha/hora nullable: puede quedar "por asignar".
            $table->dateTime('cit_fecha_hora')->nullable();
            $table->string('cit_lugar', 150)->nullable();
            $table->string('cit_estado', 20)->default('pendiente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
