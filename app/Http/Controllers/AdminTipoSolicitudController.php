<?php

namespace App\Http\Controllers;

use App\Models\TipoSolicitud;
use App\Models\Requisito;
use Illuminate\Http\Request;

class AdminTipoSolicitudController extends Controller
{
    /**
     * Listado de todos los trámites: qué tan disponibles están,
     * en qué ventana de fechas, y qué requisitos piden.
     */
    public function index()
    {
        $tipos = TipoSolicitud::with('requisitos')
                    ->orderBy('tsi_nombre_tipo')
                    ->get();

        return view('admin.tipos-solicitud.index', compact('tipos'));
    }

    /**
     * Formulario para crear un trámite nuevo.
     */
    public function create()
    {
        $requisitos = Requisito::orderBy('req_nombre_requisito')->get();

        return view('admin.tipos-solicitud.create', compact('requisitos'));
    }

    /**
     * Guardar el trámite nuevo junto con los requisitos que se le asignaron.
     */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'tsi_nombre_tipo' => 'required|string|max:100',
            'tsi_descripcion' => 'nullable|string',
            'tsi_tiempo_estimado_dias' => 'nullable|integer|min:0',
            'tsi_requiere_aprobacion_especial' => 'nullable|boolean',
            'tsi_fecha_inicio' => 'nullable|date',
            'tsi_fecha_fin' => 'nullable|date|after_or_equal:tsi_fecha_inicio',
            'requisitos' => 'nullable|array',
        ]);

        // El botón "Crear y activar" vs "Guardar como borrador" decide el estado inicial.
        $datos['tsi_estado_tipo'] = $request->input('activar_ahora') ? 'activo' : 'inactivo';
        $datos['tsi_requiere_aprobacion_especial'] = $request->boolean('tsi_requiere_aprobacion_especial');

        $tipo = TipoSolicitud::create($datos);

        $this->sincronizarRequisitos($tipo, $request);

        $mensaje = $tipo->tsi_estado_tipo === 'activo'
            ? '¡Trámite creado y activado! Ya es visible para los estudiantes.'
            : 'Trámite creado como inactivo. Actívalo cuando esté listo.';

        return redirect()->route('admin.tipos-solicitud.index')->with('success', $mensaje);
    }

    /**
     * Formulario para editar un trámite existente.
     */
    public function edit($id)
    {
        $tipo = TipoSolicitud::with('requisitos')->findOrFail($id);
        $requisitos = Requisito::orderBy('req_nombre_requisito')->get();

        // IDs de los requisitos ya asignados, para marcar sus casillas
        $asignados = $tipo->requisitos->pluck('req_id')->map(fn ($v) => (string) $v)->toArray();
        $obligatorios = $tipo->requisitos
            ->filter(fn ($r) => $r->pivot->tsr_es_obligatorio)
            ->pluck('req_id')
            ->map(fn ($v) => (string) $v)
            ->toArray();

        return view('admin.tipos-solicitud.edit', compact('tipo', 'requisitos', 'asignados', 'obligatorios'));
    }

    /**
     * Actualizar un trámite existente.
     */
    public function update(Request $request, $id)
    {
        $tipo = TipoSolicitud::findOrFail($id);

        $datos = $request->validate([
            'tsi_nombre_tipo' => 'required|string|max:100',
            'tsi_descripcion' => 'nullable|string',
            'tsi_tiempo_estimado_dias' => 'nullable|integer|min:0',
            'tsi_requiere_aprobacion_especial' => 'nullable|boolean',
            'tsi_fecha_inicio' => 'nullable|date',
            'tsi_fecha_fin' => 'nullable|date|after_or_equal:tsi_fecha_inicio',
            'requisitos' => 'nullable|array',
        ]);

        $datos['tsi_requiere_aprobacion_especial'] = $request->boolean('tsi_requiere_aprobacion_especial');
        // El estado (activo/inactivo) se maneja aparte, con el botón de encendido
        // del listado, no desde este formulario, para que no se te olvide guardado.

        $tipo->update($datos);

        $this->sincronizarRequisitos($tipo, $request);

        return redirect()->route('admin.tipos-solicitud.index')->with('success', 'Trámite actualizado correctamente.');
    }

    /**
     * Cambiar rápidamente entre activo/inactivo, sin pasar por el formulario completo.
     * Es el "switch" que describiste: habilitar/deshabilitar el trámite de un clic.
     */
    public function alternarEstado($id)
    {
        $tipo = TipoSolicitud::findOrFail($id);
        $tipo->tsi_estado_tipo = $tipo->tsi_estado_tipo === 'activo' ? 'inactivo' : 'activo';
        $tipo->save();

        $mensaje = $tipo->tsi_estado_tipo === 'activo'
            ? "\"{$tipo->tsi_nombre_tipo}\" ahora está ACTIVO y visible para los estudiantes."
            : "\"{$tipo->tsi_nombre_tipo}\" fue DESACTIVADO. Los estudiantes ya no podrán solicitarlo.";

        return redirect()->route('admin.tipos-solicitud.index')->with('success', $mensaje);
    }

    /**
     * Eliminar un trámite. Ojo: si ya tiene solicitudes de estudiantes asociadas,
     * lo normal es NO dejar borrarlo (para no perder el historial), así que
     * mejor lo bloqueamos y sugerimos desactivarlo en su lugar.
     */
    public function destroy($id)
    {
        $tipo = TipoSolicitud::findOrFail($id);

        if ($tipo->solicitudes()->exists()) {
            return redirect()->route('admin.tipos-solicitud.index')
                ->with('error', 'No se puede eliminar: ya existen solicitudes de estudiantes con este trámite. Desactívalo en su lugar.');
        }

        $tipo->delete();

        return redirect()->route('admin.tipos-solicitud.index')->with('success', 'Trámite eliminado correctamente.');
    }

    /**
     * Guarda la relación Muchos a Muchos con los requisitos seleccionados.
     * Mismo patrón que ya usaba AdminRequisitoController, solo que desde este lado.
     */
    private function sincronizarRequisitos(TipoSolicitud $tipo, Request $request)
    {
        $requisitosMarcados = $request->input('requisitos', []);
        $obligatorios = $request->input('obligatorios', []);

        $datosPivote = [];
        foreach (array_keys($requisitosMarcados) as $reqId) {
            $datosPivote[$reqId] = [
                'tsr_es_obligatorio' => isset($obligatorios[$reqId]) ? 1 : 0,
            ];
        }

        $tipo->requisitos()->sync($datosPivote);
    }
}
