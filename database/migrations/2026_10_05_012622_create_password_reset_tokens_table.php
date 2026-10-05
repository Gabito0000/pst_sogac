<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Almacena los tokens de recuperacion de contrasena.
 *
 * La tabla ya existia en la base de datos de desarrollo (venia del script
 * de carga), pero no habia migracion: en una instalacion nueva el broker de
 * Laravel fallaria al guardar el token. El nombre de la columna es "email"
 * porque es el que espera DatabaseTokenRepository de Laravel, aunque para
 * este proyecto el correo real es usuarios.usu_correo_electronico.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('password_reset_tokens')) {
            return;
        }

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
    }
};
