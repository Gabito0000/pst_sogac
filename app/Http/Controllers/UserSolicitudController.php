<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\EstadoSolicitud;
use App\Models\LapsoAcademico;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use App\Pnf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UserSolicitudController extends Controller
{
    /**
     * Panel del estudiante. Muestra únicamente los trámites que el admin
     * dejó ACTIVOS y dentro de su ventana de fechas.
     */
    public function index()
    {
        $tramites = TipoSolicitud::with('requisitos')
            ->get()
            ->filter(fn ($t) => $t->estaDisponible())
            ->values();

        // Solicitudes propias del estudiante logueado. Se carga el historial de
        // estados con su responsable para poder mostrar, en la columna "Atendida
        // por", quien la esta trabajando; sin el eager load seria un N+1.
        $misSolicitudesTodas = Solicitud::with(['tipoSolicitud', 'estadoActual', 'historialEstados.responsable'])
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
     *
     * Lleva además los datos de precarga del estudiante: los que ya cargó en
     * su perfil y se adjuntarán a la solicitud. Si le falta alguno
     * obligatorio, o ya vencieron, el aviso se lo recuerda antes de enviar.
     */
    public function create($id)
    {
        $tramite = TipoSolicitud::with('requisitos')->findOrFail($id);

        if (! $tramite->estaDisponible()) {
            return redirect()->route('dashboard')->with('error', 'Este trámite ya no está disponible.');
        }

        $usuario = Auth::user();

        // Datos que identifican al estudiante y se adjuntan a la solicitud.
        //
        // Antes se mostraban los campos de precarga configurados por el
        // administrador (lugar y fecha de nacimiento, direccion, telefono).
        // Para resolver un tramite ninguno de esos sirve: lo que la oficina
        // necesita es saber quien lo pido y de que programa viene. Estos se
        // leen de la ficha del estudiante, no de la configuracion, asi que
        // siempre estan disponibles y no dependen de que el admin habilite
        // un campo.
        $datosIdentificacion = [
            'Nombre' => $usuario->getNombreCompletoAttribute(),
            'Cédula' => $usuario->usu_numero_documento,
            'Carrera (PNF)' => $usuario->usu_pnf ?: 'Sin PNF asignado',
            'Semestre / Trayecto' => Pnf::nombreTrayecto($usuario->usu_trayecto),
            'Teléfono' => $usuario->usu_numero_telefono ?: 'Sin teléfono registrado',
        ];

        $faltanClaves = [];

        foreach ($datosIdentificacion as $etiqueta => $valor) {
            if (str_starts_with($valor, 'Sin ')) {
                $faltanClaves[] = $etiqueta;
            }
        }

        return view('user.solicitar', [
            'tramite' => $tramite,
            'datosIdentificacion' => $datosIdentificacion,
            'faltanClaves' => $faltanClaves,
        ]);
    }

    /**
     * Guarda la solicitud nueva del estudiante.
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
