<?php

namespace App\Http\Controllers;

use App\Models\Requisito;
use App\Models\TipoSolicitud;
use Illuminate\Http\Request;

class AdminRequisitoController extends Controller
{
    /**
     * Listar todos los requisitos configurados en el sistema.
     */
    public function index()
    {
        // Cargamos cada requisito junto con los tipos de trámite a los que está asignado
        $requisitos = Requisito::with('tiposSolicitud')
                               ->orderBy('req_nombre_requisito', 'ASC')
                               ->get();

        return view('admin.requisitos.index', compact('requisitos'));
    }

    /**
     * Mostrar el formulario para crear un nuevo requisito.
     */
    public function create()
    {
        // Traemos los tipos de solicitud disponibles para poder asignarlos en el formulario
        $tiposSolicitud = TipoSolicitud::orderBy('tsi_nombre_tipo')->get();

        return view('admin.requisitos.create', compact('tiposSolicitud'));
    }

    /**
     * Guardar un requisito nuevo y su asignación a tipos de trámite (tabla pivote).
     */
    public function store(Request $request)
    {
        $request->validate([
            'req_nombre_requisito' => 'required|string|max:100',
            'req_descripcion'      => 'nullable|string',
            'req_formato_esperado' => 'nullable|string|max:50',
            'tipos'                => 'nullable|array',
        ]);

        $requisito = Requisito::create([
            'req_nombre_requisito' => $request->req_nombre_requisito,
            'req_descripcion'      => $request->req_descripcion,
            'req_formato_esperado' => $request->req_formato_esperado,
        ]);

        // Sincronizamos la tabla pivote con los tipos de trámite elegidos.
        // 'tipos' es un array [tsi_id => 'on'], 'obligatorios' es [tsi_id => '1'|'0'].
        $this->sincronizarTipos($requisito, $request);

        return redirect()->route('admin.requisitos.index')
                         ->with('success', 'Requisito creado y asignado a los trámites seleccionados.');
    }

    /**
     * Mostrar el formulario para editar un requisito existente.
     */
    public function edit($id)
    {
        $requisito = Requisito::with('tiposSolicitud')->findOrFail($id);
        $tiposSolicitud = TipoSolicitud::orderBy('tsi_nombre_tipo')->get();

        // Guardamos en un array los tsi_id ya asignados para marcar las casillas
        $asignados = $requisito->tiposSolicitud->pluck('tsi_id')->map(fn ($v) => (string) $v)->toArray();

        // Guardamos qué trámites exigen este requisito de forma obligatoria
        $obligatorios = $requisito->tiposSolicitud
            ->filter(fn ($tipo) => $tipo->pivot->tsr_es_obligatorio)
            ->pluck('tsi_id')
            ->map(fn ($v) => (string) $v)
            ->toArray();

        return view('admin.requisitos.edit', compact('requisito', 'tiposSolicitud', 'asignados', 'obligatorios'));
    }

    /**
     * Actualizar un requisito existente y sus asignaciones a tipos de trámite.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'req_nombre_requisito' => 'required|string|max:100',
            'req_descripcion'      => 'nullable|string',
            'req_formato_esperado' => 'nullable|string|max:50',
            'tipos'                => 'nullable|array',
        ]);

        $requisito = Requisito::findOrFail($id);
        $requisito->update([
            'req_nombre_requisito' => $request->req_nombre_requisito,
            'req_descripcion'      => $request->req_descripcion,
            'req_formato_esperado' => $request->req_formato_esperado,
        ]);

        $this->sincronizarTipos($requisito, $request);

        return redirect()->route('admin.requisitos.index')
                         ->with('success', 'Requisito actualizado correctamente.');
    }

    /**
     * Eliminar un requisito. La tabla pivote se limpia sola gracias a onDelete('cascade').
     */
    public function destroy($id)
    {
        $requisito = Requisito::findOrFail($id);
        $requisito->delete();

        return redirect()->route('admin.requisitos.index')
                         ->with('success', 'Requisito eliminado correctamente.');
    }

    /**
     * Guarda la relación Muchos a Muchos en tipo_solicitud_requisitos.
     * Recibe el array 'tipos' (tsi_id => 'on') y 'obligatorios' (tsi_id => '1'|'0').
     */
    private function sincronizarTipos(Requisito $requisito, Request $request)
    {
        $tipos = $request->input('tipos', []);
        $obligatorios = $request->input('obligatorios', []);

        $datosPivote = [];
        foreach (array_keys($tipos) as $tsiId) {
            $datosPivote[$tsiId] = [
                'tsr_es_obligatorio' => isset($obligatorios[$tsiId]) ? 1 : 0,
            ];
        }

        // sync() reemplaza las asignaciones: agrega las nuevas y quita las que ya no están
        $requisito->tiposSolicitud()->sync($datosPivote);
    }
}
