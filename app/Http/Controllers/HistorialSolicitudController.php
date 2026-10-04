<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use App\Services\HistorialSolicitudesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Historial de solicitudes del estudiante.
 *
 * Antes esta pantalla vivia en UserSolicitudController::historial y era una
 * tabla de cuatro columnas sin filtros ni paginacion. Aqui vive aparte porque
 * ya no es un pageable mas del dashboard: es un modulo con ficha de detalle,
 * linea de tiempo de estados y bitacora de cambios, y mezcla datos de varias
 * relaciones que no tienen nada que ver con el resto de la gestion del alumno.
 *
 * El listado se limita SIEMPRE al usuario autenticado. El detalle pasa por la
 * politica SolicitudPolicy, que es la que decide si quien mira es el dueno del
 * tramite o un administrador.
 */
class HistorialSolicitudController extends Controller
{
    public function __construct(protected HistorialSolicitudesService $historial) {}

    /**
     * Listado con filtros por estado, tramite, texto y rango de fechas.
     */
    public function index(Request $request): View
    {
        $filtros = [
            'q' => mb_substr(trim((string) $request->input('q', '')), 0, 100),
            'estado' => trim((string) $request->input('estado', '')),
            'tipo' => $request->input('tipo') !== null && $request->input('tipo') !== ''
                ? (int) $request->input('tipo')
                : null,
            'desde' => HistorialSolicitudesService::sanearFecha($request->input('desde')),
            'hasta' => HistorialSolicitudesService::sanearFecha($request->input('hasta')),
            'orden' => $request->input('orden') === 'antiguas' ? 'antiguas' : 'recientes',
        ];

        $opciones = $this->historial->opciones();

        // Un estado que no existe en el catalogo no debe dejar el listado vacio
        // sin explicacion: se descarta y el resto de filtros siguen mandando.
        if ($filtros['estado'] !== '' && ! $opciones['estados']->contains($filtros['estado'])) {
            $filtros['estado'] = '';
        }

        if ($filtros['tipo'] !== null && ! $opciones['tipos']->contains('tsi_id', $filtros['tipo'])) {
            $filtros['tipo'] = null;
        }

        return view('user.historial.index', [
            'solicitudes' => $this->historial->paginar(Auth::id(), $filtros),
            'resumen' => $this->historial->resumen(Auth::id()),
            'filtros' => $filtros,
            'estados' => $opciones['estados'],
            'tipos' => $opciones['tipos'],
        ]);
    }

    /**
     * Ficha de una solicitud: datos, linea de tiempo de estados, adjuntos,
     * cita de validacion y bitacora de cambios.
     */
    public function show(Solicitud $solicitud): View
    {
        $this->authorize('view', $solicitud);

        return view('user.historial.show', $this->historial->ficha($solicitud, Auth::user()));
    }
}
