<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use Illuminate\Http\Request;

/**
 * Cola de solicitudes para el personal que procesa tramites
 * (taquillero, analista y administrador).
 *
 * Reutiliza la MISMA vista del panel administrativo; lo unico que
 * cambia es que las tarjetas de estadisticas se ocultan
 * ($mostrarEstadisticas = false), porque el taquillero no accede a ellas.
 */
class AdminSolicitudController extends Controller
{
    public function index(Request $request)
    {
        $query = Solicitud::with(['usuario', 'tipoSolicitud', 'estadoActual']);

        // Buscar por cedula o nombre del estudiante
        if ($request->filled('busqueda')) {
            $termino = $request->input('busqueda');
            $query->whereHas('usuario', function ($q) use ($termino) {
                $q->where('usu_numero_documento', 'like', '%'.$termino.'%')
                    ->orWhere('usu_primer_nombre', 'like', '%'.$termino.'%')
                    ->orWhere('usu_primer_apellido', 'like', '%'.$termino.'%');
            });
        }

        // Filtrar por tipo de tramite
        if ($request->filled('tipo_solicitud')) {
            $query->where('sol_tsi_id', $request->input('tipo_solicitud'));
        }

        // Filtrar por estado
        if ($request->filled('estado')) {
            $query->whereHas('estadoActual', function ($q) use ($request) {
                $q->where('eso_nombre_estado', $request->input('estado'));
            });
        }

        // Las pendientes primero y, entre ellas, la mas antigua primero (FIFO).
        $solicitudes = $query
            ->join('estado_solicitudes', 'estado_solicitudes.eso_id', '=', 'solicitudes.sol_eso_id')
            ->select('solicitudes.*')
            ->orderByRaw("CASE WHEN estado_solicitudes.eso_nombre_estado = 'pendiente' THEN 0 ELSE 1 END")
            ->orderBy('solicitudes.sol_fecha_creacion', 'ASC')
            ->paginate(20)
            ->withQueryString();

        $tiposSolicitud = TipoSolicitud::orderBy('tsi_nombre_tipo')->get();

        $datos = [
            'solicitudes' => $solicitudes,
            'tiposSolicitud' => $tiposSolicitud,
            // Sin tarjetas de estadisticas: el taquillero no las necesita.
            'mostrarEstadisticas' => false,
            'urlBase' => route('admin.solicitudes.index'),
        ];

        // Cuando el buscador o las pestañas piden solo la tabla (ajax),
        // se devuelve unicamente el parcial: si no, se insertaria la
        // pagina completa dentro de la tabla y se duplicaria el menu.
        if ($request->ajax()) {
            return view('admin.partials.resultados', compact('solicitudes'));
        }

        return view('admin.dashboard', $datos);
    }
}
