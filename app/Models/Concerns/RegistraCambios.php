<?php

namespace App\Models\Concerns;

use App\Services\RegistroCambios;

/**
 * Conecta un modelo con la bitacora de cambios.
 *
 * Se engancha a los eventos de Eloquent en vez de a los controllers porque
 * asi no importa quien escriba en la base: si alguien aprueba una solicitud
 * desde el panel, corrige un requisito o edita una pregunta, el cambio queda
 * registrado sin que ningun metodo tenga que acordarse de hacerlo.
 *
 * No se aplica al propio HistorialCambio: registrar los cambios de la bitacora
 * dentro de la bitacora es una recursion infinita.
 */
trait RegistraCambios
{
    public static function bootRegistraCambios(): void
    {
        static::created(function ($modelo): void {
            RegistroCambios::creacion($modelo);
        });

        static::updated(function ($modelo): void {
            RegistroCambios::actualizacion($modelo);
        });

        static::deleted(function ($modelo): void {
            RegistroCambios::eliminacion($modelo);
        });
    }
}
