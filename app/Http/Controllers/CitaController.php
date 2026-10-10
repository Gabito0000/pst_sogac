<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Solicitud;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CitaController extends Controller
{
    /**
     * 1. Muestra la pantalla principal con la tabla de citas.
     */
    public function misCitas()
    {
        $usuario = Auth::user();

        $citas = Cita::where('cit_usu_id', $usuario->usu_id)
            ->with(['solicitud.tipoSolicitud'])
            ->orderBy('cit_fecha_hora', 'desc')
            ->get();

        return view('user.citas.index', compact('citas'));
    }

    /**
     * 2. Muestra el formulario para crear una nueva cita.
     */
    public function create()
    {
        $usuario = Auth::user();

        $solicitudesPendientes = Solicitud::where('sol_usu_id', $usuario->usu_id)
            ->whereDoesntHave('cita')
            ->with('tipoSolicitud')
            ->get();

        return view('user.citas.create', compact('solicitudesPendientes'));
    }

    /**
     * 3. API que devuelve los bloques de 20 min disponibles evaluando la capacidad máxima (5).
     */
    public function obtenerBloquesDisponibles(Request $request, string $fecha): JsonResponse
    {
        $date = Carbon::parse($fecha);

        // Validar fines de semana o fechas fuera de rango
        if ($date->isWeekend() || $date->isBefore(Carbon::today()) || $date->isAfter(Carbon::today()->addDays(30))) {
            return response()->json(['message' => 'Fecha no disponible o fuera de rango.'], 400);
        }

        $inicioDia = $date->copy()->setTime(8, 0);
        $finDia = $date->copy()->setTime(22, 0);
        $ahora = Carbon::now();
        $limitePorBloque = 5; // Tu regla de concurrencia

        // Traer todas las citas de ese día para contar la concurrencia
        // (No contamos las rechazadas o inasistencias porque liberan cupo)
        $citasDelDia = Cita::whereDate('cit_fecha_hora', $fecha)
            ->whereIn('cit_estado', ['pendiente', 'activo', 'aprobada'])
            ->get()
            ->groupBy(function($cita) {
                return $cita->cit_fecha_hora->format('H:i:s');
            });

        $bloquesDisponibles = [];
        $tiempoActual = $inicioDia->copy();

        while ($tiempoActual->lt($finDia)) {
            // Si el bloque de tiempo es hoy y ya pasó la hora, lo saltamos
            if ($date->isToday() && $tiempoActual->lte($ahora)) {
                $tiempoActual->addMinutes(20);
                continue;
            }

            $horaString = $tiempoActual->format('H:i:s');
            
            // Contar cuántos estudiantes ya tienen cita a esta hora exacta
            $ocupados = isset($citasDelDia[$horaString]) ? $citasDelDia[$horaString]->count() : 0;

            // Si hay menos de 5, el bloque está disponible
            if ($ocupados < $limitePorBloque) {
                $bloquesDisponibles[] = [
                    'inicio' => $tiempoActual->format('Y-m-d H:i:s'),
                    'cupos_restantes' => $limitePorBloque - $ocupados
                ];
            }

            $tiempoActual->addMinutes(20);
        }

        return response()->json([
            'fecha' => $fecha,
            'bloques_disponibles' => $bloquesDisponibles,
        ]);
    }

    /**
     * 4. Guarda la cita en la base de datos de manera segura.
     */
    public function store(Request $request)
    {
        // 1. Validaciones básicas sin bloqueos raros
        $request->validate([
            'cit_fecha_hora' => 'required|date',
            'cit_motivo' => 'nullable|string|max:255',
        ]);

        $sol_id = $request->input('cit_sol_id');
        $motivo = $request->input('cit_motivo');

        // 2. Validar manualmente que enviara al menos uno
        if (empty($sol_id) && empty($motivo)) {
            return back()->withErrors(['error' => 'Debes seleccionar una solicitud o especificar el motivo de tu cita.']);
        }

        // 3. Forzar NULL real para PostgreSQL si llegan vacíos (El escudo anti-crasheos)
        $sol_id = empty($sol_id) ? null : $sol_id;
        $motivo = empty($motivo) ? null : $motivo;

        // 4. Validar concurrencia
        $ocupados = Cita::where('cit_fecha_hora', $request->cit_fecha_hora)
            ->whereIn('cit_estado', ['pendiente', 'activo', 'aprobada'])
            ->count();

        if ($ocupados >= 5) {
            return back()->withErrors(['error' => 'Este horario acaba de llenarse. Por favor, selecciona otro.']);
        }

        // 5. Guardar la cita de forma segura
        Cita::create([
            'cit_usu_id' => Auth::user()->usu_id,
            'cit_sol_id' => $sol_id,
            'cit_motivo' => $motivo,
            'cit_fecha_hora' => $request->cit_fecha_hora,
            'cit_estado' => 'pendiente',
        ]);

        return redirect()->route('user.citas')->with('success', '¡Tu cita ha sido agendada exitosamente!');
    }
}