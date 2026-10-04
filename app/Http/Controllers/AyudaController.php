<?php

namespace App\Http\Controllers;

use App\Services\PreguntasFrecuentesService;

/**
 * Portal de ayuda del estudiante.
 *
 * Solo lectura a propósito: las preguntas frecuentes se administran desde el
 * panel del administrador (AdminPreguntaFrecuenteController). Antes esta
 * lectura y la gestión compartían la misma vista, que se bifurcaba en
 * tiempo de render según el rol, y por eso el mismo controlador servía
 * para los dos públicos.
 */
class AyudaController extends Controller
{
    public function __construct(protected PreguntasFrecuentesService $preguntas) {}

    /**
     * Listado de preguntas frecuentes con buscador.
     */
    public function preguntas()
    {
        $busqueda = request()->string('q')->trim()->value();

        return view('ayuda.preguntas', [
            'preguntas' => $this->preguntas->paginar($busqueda === '' ? null : $busqueda),
            'busqueda' => $busqueda,
        ]);
    }
}
