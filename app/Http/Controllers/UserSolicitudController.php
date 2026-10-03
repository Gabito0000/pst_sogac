<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\EstadoSolicitud;
use App\Models\LapsoAcademico;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UserSolicitudController extends Controller
{
    /**
     * Panel del estudiante. Muestra únicamente los trámites que el admin
     * dejó ACTIVOS y dentro de su ventana de fechas (mismo método
     * estaDisponible() que ya usa el panel admin para el semáforo).
     */
    public function index()
    {
        $tramites = TipoSolicitud::with('requisitos')
            ->get()
            ->filter(fn ($t) => $t->estaDisponible())
            ->values();

        // Solicitudes propias del estudiante logueado (para el resumen y la tabla de abajo)
        $misSolicitudesTodas = Solicitud::with(['tipoSolicitud', 'estadoActual'])
            ->where('sol_usu_id', Auth::id())
            ->get();

        $stats = [
            'total' => $misSolicitudesTodas->count(),
            'pendiente' => $misSolicitudesTodas->where('estadoActual.eso_nombre_estado', 'pendiente')->count(),
            'aprobada' => $misSolicitudesTodas->where('estadoActual.eso_nombre_estado', 'aprobada')->count(),
            'rechazada' => $misSolicitudesTodas->where('estadoActual.eso_nombre_estado', 'rechazada')->count(),
        ];

        $misSolicitudesRecientes = $misSolicitudesTodas
            ->sortByDesc('sol_fecha_creacion')
            ->take(5);

        return view('user.dashboard', compact('tramites', 'stats', 'misSolicitudesRecientes'));
    }

    /**
     * Calendario del estudiante: citas de validacion fisica asignadas
     * a sus solicitudes (fecha, lugar y estado de cada una).
     */
    public function misCitas()
    {
        $citas = Cita::with(['solicitud.tipoSolicitud'])
            ->whereHas('solicitud', fn ($q) => $q->where('sol_usu_id', Auth::id()))
            ->orderBy('cit_fecha_hora')
            ->get();

        return view('user.citas', compact('citas'));
    }

    /**
     * Formulario para solicitar un trámite específico.
     * Muestra dinámicamente los requisitos que el admin le asignó a ESE trámite.
     */
    public function create($id)
    {
        $tramite = TipoSolicitud::with('requisitos')->findOrFail($id);

        // Doble candado: si alguien entra por URL directa a un trámite que
        // ya se desactivó o venció, lo mandamos de vuelta con un aviso.
        if (! $tramite->estaDisponible()) {
            return redirect()->route('dashboard')->with('error', 'Este trámite ya no está disponible.');
        }

        return view('user.solicitar', compact('tramite'));
    }

    /**
     * Guarda la solicitud nueva del estudiante. Aparece automáticamente
     * en el dashboard admin porque escribe en la misma tabla "solicitudes".
     */
    public function store(Request $request, $id)
    {
        $tramite = TipoSolicitud::findOrFail($id);

        if (! $tramite->estaDisponible()) {
            return redirect()->route('dashboard')->with('error', 'Este trámite ya no está disponible.');
        }

        $request->validate([
            'motivo' => 'required|string|max:2000',
        ]);

        // Toda solicitud queda "amarrada" a un lapso académico activo
        // (semestre/periodo actual). Si nadie configuró uno, avisamos en
        // vez de guardar una solicitud "huérfana".
        $lapso = LapsoAcademico::where('lac_estado_lapso', 'activo')->first();
        if (! $lapso) {
            return back()
                ->withErrors(['motivo' => 'No hay un lapso académico activo configurado. Contacta a control de estudios.'])
                ->withInput();
        }

        $estadoPendiente = EstadoSolicitud::where('eso_nombre_estado', 'pendiente')->firstOrFail();

        Solicitud::create([
            'sol_usu_id' => Auth::id(),
            'sol_tsi_id' => $tramite->tsi_id,
            'sol_lac_id' => $lapso->lac_id,
            'sol_eso_id' => $estadoPendiente->eso_id,
            'sol_id_seguimiento' => 'SOL-'.strtoupper(Str::random(8)),
            'sol_motivo_detallado' => $request->input('motivo'),
            'sol_prioridad' => 'normal',
            'sol_fecha_creacion' => now(),
            'sol_fecha_ultima_actualizacion' => now(),
        ]);

        return redirect()->route('dashboard')->with('success', '¡Solicitud enviada correctamente! El administrador la revisará pronto.');
    }
}