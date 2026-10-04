@extends('layouts.plantilla_user')

@section('title', 'Preguntas frecuentes')

@section('content')
    @include('partials.avisos')

    <div class="pagina-head">
        <div>
            <h1 class="pagina-head__titulo">Preguntas frecuentes</h1>
            <p class="pagina-head__desc">
                Resuelve aquí las dudas más comunes sobre los trámites. Si no encuentras lo que buscas,
                escríbenos por el chat y lo revisamos contigo.
            </p>
        </div>

        <div class="pagina-head__acciones">
            <a href="{{ route('user.ayuda.chat.index') }}" class="btn btn--dark">Ir al chat</a>
        </div>
    </div>

    {{-- Buscador: evita recorrer un listado largo cuando la duda ya se resolvió
         antes y lo que se busca es la respuesta concreta. --}}
    <form method="GET" action="{{ route('user.ayuda.preguntas') }}" class="buscador-caja">
        <input type="text"
               name="q"
               value="{{ $busqueda }}"
               placeholder="Buscar una duda…"
               aria-label="Buscar en las preguntas frecuentes">
        <button type="submit" class="btn btn--primary">Buscar</button>

        @if ($busqueda !== '')
            <a href="{{ route('user.ayuda.preguntas') }}" class="btn btn--ghost">Limpiar</a>
        @endif
    </form>

    @if ($preguntas->isEmpty())
        <div class="card">
            @if ($busqueda !== '')
                <p class="estado-vacio">
                    Ninguna pregunta coincide con «{{ $busqueda }}».
                    <br>
                    Prueba con otras palabras o <a href="{{ route('user.ayuda.chat.index') }}">pregunta por chat</a>.
                </p>
            @else
                <p class="estado-vacio">
                    Todavía no hay preguntas frecuentes publicadas.
                </p>
            @endif
        </div>
    @else
        @if ($busqueda !== '')
            <p style="color: var(--gray-700); font-size: 0.9rem; margin-bottom: 14px;">
                {{ $preguntas->total() }}
                {{ $preguntas->total() === 1 ? 'coincidencia' : 'coincidencias' }}
                para «{{ $busqueda }}».
            </p>
        @endif

        <div class="faq">
            @foreach ($preguntas as $pregunta)
                <details class="faq__item">
                    <summary class="faq__pregunta">
                        <span>{{ $pregunta->pregunta }}</span>

                        <svg class="icono-flecha" width="20" height="20" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                             aria-hidden="true">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </summary>

                    <div class="faq__respuesta">
                        {!! nl2br(e($pregunta->respuesta)) !!}

                        <div class="faq-meta">
                            <span>Actualizada el {{ $pregunta->updated_at->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </details>
            @endforeach
        </div>

        <div style="margin-top: 28px;">
            {{ $preguntas->links('vendor.pagination.custom', ['etiqueta' => 'preguntas']) }}
        </div>
    @endif
@endsection
