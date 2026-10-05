<?php

namespace App\Http\Controllers;

use App\Http\Requests\PreguntasFrecuentes\CreatePostRequest;
use App\Http\Requests\PreguntasFrecuentes\UpdatePostRequest;
use App\Models\PreguntasFrecuentes;
use App\Services\PreguntasFrecuentesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Gestión de las preguntas frecuentes por parte del administrador.
 *
 * Sustituye al viejo PreguntasFrecuentesController, que tenía tres defectos:
 * create() devolvía una vista inexistente, edit() no devolvía nada y los
 * tres redirects apuntaban a route('soporte.index'), que era la ruta del
 * estudiante, así que tras crear una pregunta el admin caía en la vista de
 * solo lectura.
 *
 * El acceso ya lo garantiza el middleware 'admin' del grupo /admin, por eso
 * aquí no se repite la comprobación de rol.
 */
class AdminPreguntaFrecuenteController extends Controller
{
    public function __construct(protected PreguntasFrecuentesService $service) {}

    /**
     * Listado de preguntas frecuentes.
     */
    public function index(): View
    {
        $busqueda = request()->string('q')->trim()->value();

        return view('admin.preguntas.index', [
            'preguntas' => $this->service->paginar($busqueda === '' ? null : $busqueda),
            'busqueda' => $busqueda,
        ]);
    }

    /**
     * Formulario para crear una pregunta.
     */
    public function create(): View
    {
        return view('admin.preguntas.create');
    }

    /**
     * Guardar una pregunta nueva.
     */
    public function store(CreatePostRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()
            ->route('admin.preguntas.index')
            ->with('success', 'Pregunta frecuente creada correctamente.');
    }

    /**
     * Formulario para editar una pregunta existente.
     */
    public function edit(PreguntasFrecuentes $pregunta): View
    {
        return view('admin.preguntas.edit', compact('pregunta'));
    }

    /**
     * Actualizar una pregunta existente.
     */
    public function update(UpdatePostRequest $request, PreguntasFrecuentes $pregunta): RedirectResponse
    {
        $this->service->update($pregunta->id, $request->validated());

        return redirect()
            ->route('admin.preguntas.index')
            ->with('success', 'Pregunta frecuente actualizada correctamente.');
    }

    /**
     * Eliminar una pregunta.
     */
    public function destroy(PreguntasFrecuentes $pregunta): RedirectResponse
    {
        $this->service->delete($pregunta->id);

        return redirect()
            ->route('admin.preguntas.index')
            ->with('success', 'Pregunta frecuente eliminada correctamente.');
    }
}
