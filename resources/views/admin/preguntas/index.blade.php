@extends('layouts.plantilla_admin')

@section('title', 'Preguntas frecuentes')

@section('content')
    @include('partials.miga', ['miga' => [['texto' => 'Atención'], ['texto' => 'Preguntas frecuentes']]])

    @include('partials.avisos')

    <div class="pagina-head">
        <div>
            <h1 class="pagina-head__titulo">Preguntas frecuentes</h1>
            <p class="pagina-head__desc">
                Contenido que se publica en el portal de ayuda del estudiante. Lo que borres aquí desaparece
                de su pantalla de inmediato.
            </p>
        </div>

        <div class="pagina-head__acciones">
            <a href="{{ route('admin.chat.index') }}" class="btn btn--dark">Ver chats</a>
            <a href="{{ route('admin.preguntas.create') }}" class="btn btn--primary">+ Nueva pregunta</a>
        </div>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('admin.preguntas.index') }}" class="toolbar">
            <input type="text"
                   name="q"
                   value="{{ $busqueda }}"
                   placeholder="Buscar por texto de la pregunta o de la respuesta…"
                   aria-label="Buscar preguntas frecuentes"
                   style="flex: 1; min-width: 240px;">

            <div class="toolbar__grupo">
                <button type="submit" class="btn btn--primary btn--sm">Buscar</button>

                @if ($busqueda !== '')
                    <a href="{{ route('admin.preguntas.index') }}" class="btn btn--ghost btn--sm">Limpiar</a>
                @endif
            </div>
        </form>

        @if ($preguntas->isEmpty())
            @if ($busqueda !== '')
                <p class="estado-vacio">
                    Ninguna pregunta coincide con «{{ $busqueda }}».
                </p>
            @else
                <p class="estado-vacio">
                    Todavía no hay preguntas frecuentes publicadas.
                    <br>
                    <a href="{{ route('admin.preguntas.create') }}">Crea la primera</a> para que el estudiante
                    encuentre la respuesta sin abrir un chat.
                </p>
            @endif
        @else
            @if ($busqueda !== '')
                <p style="color: var(--gray-700); font-size: 0.9rem; margin-bottom: 14px;">
                    {{ $preguntas->total() }}
                    {{ $preguntas->total() === 1 ? 'coincidencia' : 'coincidencias' }}
                    para «{{ $busqueda }}».
                </p>
            @endif

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 90px;">#</th>
                            <th>Pregunta y respuesta</th>
                            <th style="width: 130px;">Actualizada</th>
                            <th class="table__acciones" style="width: 190px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($preguntas as $pregunta)
                            <tr>
                                <td style="color: var(--gray-400);">{{ $pregunta->id }}</td>

                                <td>
                                    <strong style="display: block; margin-bottom: 4px;">{{ $pregunta->pregunta }}</strong>
                                    <span style="color: var(--gray-700); font-size: 0.88rem;">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($pregunta->respuesta), 120) }}
                                    </span>
                                </td>

                                <td style="color: var(--gray-700); font-size: 0.85rem; white-space: nowrap;">
                                    {{ $pregunta->updated_at->format('d/m/Y') }}
                                </td>

                                <td class="table__acciones">
                                    <div>
                                        <a href="{{ route('admin.preguntas.edit', $pregunta) }}"
                                           class="btn btn--dark btn--sm">Editar</a>

                                        <form action="{{ route('admin.preguntas.destroy', $pregunta) }}"
                                              method="POST"
                                              onsubmit="return confirm('¿Eliminar «{{ addslashes($pregunta->pregunta) }}»? Dejará de aparecer en el portal del estudiante.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn--danger btn--sm">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 20px;">
                {{ $preguntas->links('vendor.pagination.custom', ['etiqueta' => 'preguntas']) }}
            </div>
        @endif
    </div>
@endsection
