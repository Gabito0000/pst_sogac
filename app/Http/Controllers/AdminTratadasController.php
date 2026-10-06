<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use App\Services\AdminTratadasService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Historial de solicitudes tratadas (admin).
 *
 * Aquí viven todas las solicitudes que tienen al menos un registro en
 * historial_estado_solicitudes: aprobadas, rechazadas, y pendientes que
 * pasaron por algún cambio de estado. La bandeja de entrada (dashboard)
 * solo muestra las que siguen en "pendiente" y SIN historial de resolución.
 */
class AdminTratadasController extends Controller
{
    public function __construct(protected AdminTratadasService $tratadas) {}

    /**
     * Listado con filtros.
     */
    public function index(Request $request): View
    {
        $filtros = [
            'q' => mb_substr(trim((string) $request->input('q', '')), 0, 100),
            'estado' => trim((string) $request->input('estado', '')),
            'tipo' => $request->input('tipo') !== null && $request->input('tipo') !== ''
                ? (int) $request->input('tipo')
                : null,
            'responsable' => $request->input('responsable') !== null && $request->input('responsable') !== ''
                ? (int) $request->input('responsable')
                : null,
            'desde' => AdminTratadasService::sanearFecha($request->input('desde')),
            'hasta' => AdminTratadasService::sanearFecha($request->input('hasta')),
            'orden' => $request->input('orden') === 'antiguas' ? 'antiguas' : 'recientes',
        ];

        $opciones = $this->tratadas->opciones();

        // Estado inexistente en catálogo -> se descarta para no vaciar sin explicación
        if ($filtros['estado'] !== '' && ! $opciones['estados']->contains($filtros['estado'])) {
            $filtros['estado'] = '';
        }

        if ($filtros['tipo'] !== null && ! $opciones['tipos']->contains('tsi_id', $filtros['tipo'])) {
            $filtros['tipo'] = null;
        }

        if ($filtros['responsable'] !== null && ! $opciones['responsables']->contains('usu_id', $filtros['responsable'])) {
            $filtros['responsable'] = null;
        }

        // Rango invertido: aviso en la vista, no error
        $rangoInvertido = false;
        if (! empty($filtros['desde']) && ! empty($filtros['hasta'])) {
            $rangoInvertido = Carbon::parse($filtros['desde'])->greaterThan(Carbon::parse($filtros['hasta']));
        }

        return view('admin.tratadas.index', [
            'tratadas' => $this->tratadas->paginar($filtros),
            'resumen' => $this->tratadas->resumen(),
            'filtros' => $filtros,
            'estados' => $opciones['estados'],
            'tipos' => $opciones['tipos'],
            'responsables' => $opciones['responsables'],
            'rangoInvertido' => $rangoInvertido,
        ]);
    }

    /**
     * Ficha de una solicitud tratada: detalle completo, línea de tiempo, observaciones.
     */
    public function show(Solicitud $solicitud): View
    {
        return view('admin.tratadas.show', $this->tratadas->ficha($solicitud));
    }
}
