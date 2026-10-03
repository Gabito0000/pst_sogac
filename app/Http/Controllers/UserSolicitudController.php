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
<<<<<<< HEAD
     * dejó ACTIVOS y dentro de su ventana de fechas.
=======
     * dejó ACTIVOS y dentro de su ventana de fechas (mismo método
     * estaDisponible() que ya usa el panel admin para el semáforo).
>>>>>>> 7d0685b4379ba4764a11a7f976b77bb0be3b5bb1
     */
    public function index()
    {
        $tramites = TipoSolicitud::with('requisitos')
            ->get()
            ->filter(fn ($t) => $t->estaDisponible())
            ->values();

<<<<<<< HEAD
        // Solicitudes propias del estudiante logueado
=======
        // Solicitudes propias del estudiante logueado (para el resumen y la tabla de abajo)
>>>>>>> 7d0685b4379ba4764a11a7f976b77bb0be3b5bb1
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
<<<<<<< HEAD
     * Listado general de trámites disponibles para el estudiante.
     * Apunta directamente a la vista resources/views/user/tramites.blade.php
     */
    public function listarTramites()
    {
        $tramites = TipoSolicitud::with('requisitos')
            ->get()
            ->filter(fn ($t) => $t->estaDisponible())
            ->values();

        return view('user.tramites', compact('tramites'));
    }

    /**
     * Calendario del estudiante: citas de validación física asignadas.
=======
     * Calendario del estudiante: citas de validacion fisica asignadas
     * a sus solicitudes (fecha, lugar y estado de cada una).
>>>>>>> 7d0685b4379ba4764a11a7f976b77bb0be3b5bb1
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
<<<<<<< HEAD
     * Historial completo de solicitudes del estudiante.
     */
    public function historial()
    {
        $misSolicitudesRecientes = Solicitud::with(['tipoSolicitud', 'estadoActual'])
            ->where('sol_usu_id', Auth::id())
            ->orderByDesc('sol_fecha_creacion')
            ->get();

        return view('user.historial', compact('misSolicitudesRecientes'));
    }

    /**
     * Formulario para solicitar un trámite específico.
=======
     * Formulario para solicitar un trámite específico.
     * Muestra dinámicamente los requisitos que el admin le asignó a ESE trámite.
>>>>>>> 7d0685b4379ba4764a11a7f976b77bb0be3b5bb1
     */
    public function create($id)
    {
        $tramite = TipoSolicitud::with('requisitos')->findOrFail($id);

<<<<<<< HEAD
=======
        // Doble candado: si alguien entra por URL directa a un trámite que
        // ya se desactivó o venció, lo mandamos de vuelta con un aviso.
>>>>>>> 7d0685b4379ba4764a11a7f976b77bb0be3b5bb1
        if (! $tramite->estaDisponible()) {
            return redirect()->route('dashboard')->with('error', 'Este trámite ya no está disponible.');
        }

        return view('user.solicitar', compact('tramite'));
    }

    /**
<<<<<<< HEAD
     * Guarda la solicitud nueva del estudiante.
=======
     * Guarda la solicitud nueva del estudiante. Aparece automáticamente
     * en el dashboard admin porque escribe en la misma tabla "solicitudes".
>>>>>>> 7d0685b4379ba4764a11a7f976b77bb0be3b5bb1
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

<<<<<<< HEAD
=======
        // Toda solicitud queda "amarrada" a un lapso académico activo
        // (semestre/periodo actual). Si nadie configuró uno, avisamos en
        // vez de guardar una solicitud "huérfana".
>>>>>>> 7d0685b4379ba4764a11a7f976b77bb0be3b5bb1
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
<<<<<<< HEAD
}
=======
}
>>>>>>> 7d0685b4379ba4764a11a7f976b77bb0be3b5bb1
