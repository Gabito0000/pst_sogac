@extends('layouts.plantilla_admin')

@section('title', 'Nueva pregunta frecuente')

@section('content')
    @include('partials.miga', [
        'miga' => [
            ['texto' => 'Atención'],
            ['texto' => 'Preguntas frecuentes', 'ruta' => route('admin.preguntas.index')],
            ['texto' => 'Nueva'],
        ],
    ])

    @include('partials.avisos')

    <div class="pagina-head">
        <div>
            <h1 class="pagina-head__titulo">Nueva pregunta frecuente</h1>
            <p class="pagina-head__desc">
                Se publica de inmediato en el portal de ayuda del estudiante.
            </p>
        </div>
    </div>

    <div class="card">
        @include('admin.preguntas._form', [
            'accion' => route('admin.preguntas.store'),
            'metodo' => 'POST',
            'pregunta' => null,
        ])
    </div>
@endsection
