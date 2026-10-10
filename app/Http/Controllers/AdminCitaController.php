<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminCitaController extends Controller
{
    /**
     * 1. Muestra el panel principal de citas para Control de Estudios.
     */
    public function index(Request $request)
    {
        // Si el admin usa el filtro de fecha, tomamos esa fecha. Si no, usamos HOY por defecto.
        $fechaFiltro = $request->input('fecha', Carbon::today()->toDateString());

        // Buscamos las citas de ese día, cargando los datos del estudiante (usuario) y la solicitud
        $citas = Cita::with(['usuario', 'solicitud.tipoSolicitud'])
            ->whereDate('cit_fecha_hora', $fechaFiltro)
            ->orderBy('cit_fecha_hora', 'asc') // Ordenar cronológicamente (las más tempranas primero)
            ->get();

        // Calculamos unas estadísticas rápidas para la interfaz
        $totalDia = $citas->count();
        $atendidas = $citas->where('cit_estado', 'atendida')->count();
        $pendientes = $citas->where('cit_estado', 'pendiente')->count();
        $inasistencias = $citas->where('cit_estado', 'inasistencia')->count();

        return view('admin.citas.index', compact(
            'citas', 'fechaFiltro', 'totalDia', 'atendidas', 'pendientes', 'inasistencias'
        ));
    }

    /**
     * 2. Acción: Marca una cita como "atendida" (El estudiante vino a taquilla)
     */
    public function marcarAtendida($id)
    {
        $cita = Cita::findOrFail($id);
        
        $cita->update([
            'cit_estado' => 'atendida'
        ]);

        return back()->with('success', '¡Cita marcada como ATENDIDA exitosamente!');
    }

    /**
     * 3. Acción: Marca una cita como "inasistencia" (El estudiante no llegó a su hora)
     */
    public function marcarInasistencia($id)
    {
        $cita = Cita::findOrFail($id);
        
        $cita->update([
            'cit_estado' => 'inasistencia'
        ]);

        return back()->with('success', 'Se registró la INASISTENCIA del estudiante.');
    }
}