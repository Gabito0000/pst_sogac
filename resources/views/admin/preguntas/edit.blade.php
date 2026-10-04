@extends('layouts.plantilla_admin')

@section('title', 'Editar pregunta frecuente')

@section('content')
    @include('partials.miga', [
        'miga' => [
            ['texto' => 'Atención'],
            ['texto' => 'Preguntas frecuentes', 'ruta' => route('admin.preguntas.index')],
            ['texto' => 'Editar'],
        ],
    ])

    @include('partials.avisos')

    <div class="pagina-head">
        <div>
            <h1 class="pagina-head__titulo">Editar pregunta frecuente</h1>
            <p class="pagina-head__desc">
                Los cambios se reflejan de inmediato en el portal del estudiante.
            </p>
        </div>
    </div>

    <div class="card">
        @include('admin.preguntas._form', [
            'accion' => route('admin.preguntas.update', $pregunta),
            'metodo' => 'PUT',
            'pregunta' => $pregunta,
        ])
    </div>

    <div class="card" style="margin-top: 20px; border-left: 4px solid var(--red);">
        <h2 class="card__title" style="font-size: 1.05rem;">Zona de riesgo</h2>
        <p class="card__sub" style="margin-bottom: 16px;">
            Eliminar la pregunta la quita del portal del estudiante. Esta acción no se puede deshacer.
        </p>

        <form action="{{ route('admin.preguntas.destroy', $pregunta) }}"
              method="POST"
              onsubmit="return confirm('¿Eliminar «{{ addslashes($pregunta->pregunta) }}»? Dejará de aparecer en el portal del estudiante.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn--danger">Eliminar pregunta</button>
        </form>
    </div>
@endsection
