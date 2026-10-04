@extends('layouts.plantilla_user')

@section('title', 'Chat de soporte')

@section('content')
    @include('partials.miga', [
        'miga' => [
            ['texto' => 'Ayuda'],
            ['texto' => 'Chat de soporte'],
        ],
    ])

    @include('partials.avisos')

    <div class="pagina-head">
        <div>
            <h1 class="pagina-head__titulo">Chat de soporte</h1>
            <p class="pagina-head__desc">
                Escribe a un administrador y sigue la conversación aquí. Solo puedes tener una consulta
                abierta a la vez, así que resuelve la anterior antes de abrir otra.
            </p>
        </div>

        <div class="pagina-head__acciones">
            <a href="{{ route('user.ayuda.preguntas') }}" class="btn btn--ghost">Ver preguntas frecuentes</a>

            @if (! $hiloActivo)
                <button type="button" class="btn btn--primary" onclick="abrirModal('modalNuevoTicket')">
                    + Nueva consulta
                </button>
            @endif
        </div>
    </div>

    {{-- Consulta en curso --}}
    @if ($hiloActivo)
        <div class="card" style="margin-bottom: 22px;">
            <div class="card__head">
                <div>
                    <h2 class="card__title">
                        Consulta #{{ $hiloActivo->hch_id }}
                        <span class="chip {{ $hiloActivo->estado_chip }}">{{ $hiloActivo->estado_etiqueta }}</span>
                    </h2>
                    <p class="card__sub">
                        Abierta el {{ $hiloActivo->created_at->format('d/m/Y') }}.
                        @if ($hiloActivo->admin)
                            La atiende {{ $hiloActivo->admin->usu_primer_nombre }}.
                        @endif
                    </p>
                </div>

                <a href="{{ route('user.ayuda.chat.mostrar', $hiloActivo->hch_id) }}" class="btn btn--primary">
                    Abrir conversación
                </a>
            </div>

            @if ($hiloActivo->hch_estado === 'pendiente_cierre')
                <div class="alert alert--warning" style="margin-bottom: 0;">
                    El administrador propuso cerrar esta consulta. Entra para confirmar si tu duda quedó
                    resuelta o rechazarla para seguir hablando.
                </div>
            @endif
        </div>
    @else
        <div class="card" style="margin-bottom: 22px;">
            <p class="estado-vacio">
                No tienes ninguna consulta abierta.
                <br>
                Antes de escribir, mira si tu duda ya está resuelta en las
                <a href="{{ route('user.ayuda.preguntas') }}">preguntas frecuentes</a>.
            </p>
        </div>
    @endif

    {{-- Historial --}}
    @if ($hilos->isEmpty())
        <div class="card">
            <p class="estado-vacio">Todavía no tienes consultas cerradas.</p>
        </div>
    @else
        <details class="faq__item">
            <summary class="faq__pregunta">
                <span>Historial de consultas ({{ $hilos->total() }})</span>

                <svg class="icono-flecha" width="20" height="20" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                     aria-hidden="true">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </summary>

            <div style="padding: 18px 20px 22px;">
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 90px;">#</th>
                                <th>Tema</th>
                                <th>Atendida por</th>
                                <th style="width: 130px;">Cerrada</th>
                                <th class="table__acciones" style="width: 120px;">Conversación</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($hilos as $hilo)
                                <tr>
                                    <td style="color: var(--gray-400);">#{{ $hilo->hch_id }}</td>

                                    <td>
                                        @if ($hilo->hch_etiqueta_tema)
                                            <span class="chip chip--neutro">{{ $hilo->hch_etiqueta_tema }}</span>
                                        @else
                                            <span style="color: var(--gray-400);">Sin etiqueta</span>
                                        @endif
                                    </td>

                                    <td>{{ $hilo->admin->usu_primer_nombre ?? 'Sistema' }}</td>

                                    <td style="color: var(--gray-700); font-size: 0.85rem; white-space: nowrap;">
                                        {{ $hilo->updated_at->format('d/m/Y') }}
                                    </td>

                                    <td class="table__acciones">
                                        <a href="{{ route('user.ayuda.chat.mostrar', $hilo->hch_id) }}"
                                           class="btn btn--dark btn--sm">Ver</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 18px;">
                    {{ $hilos->links('vendor.pagination.custom', ['etiqueta' => 'consultas']) }}
                </div>
            </div>
        </details>
    @endif

    {{-- Modal de alta. El botón solo existe si no hay una consulta abierta, así
         que no hace falta la comprobación de "409" duplicada en la vista. --}}
    @unless ($hiloActivo)
        <div id="modalNuevoTicket" class="modal-overlay" data-abierto="0"
             role="dialog" aria-modal="true" aria-labelledby="tituloNuevoTicket">
            <div class="modal">
                <h2 class="card__title" id="tituloNuevoTicket">Nueva consulta de soporte</h2>
                <p class="card__sub" style="margin-bottom: 20px;">
                    Describe tu problema con detalle. Un administrador lo leerá y te responderá aquí.
                </p>

                <form action="{{ route('user.ayuda.chat.iniciar') }}" method="POST" class="form"
                      enctype="multipart/form-data">
                    @csrf

                    <div class="field">
                        <label for="mch_cuerpo">Mensaje <span class="req">*</span></label>
                        <textarea name="mch_cuerpo" id="mch_cuerpo" rows="5"
                                  placeholder="Ej: No puedo cargar la constancia de estudio que solicité.">{{ old('mch_cuerpo') }}</textarea>

                        @error('mch_cuerpo')
                            <span class="field__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="imagen">Adjuntar evidencia</label>
                        <input type="file" name="imagen" id="imagen" accept="image/*">
                        <span class="field__hint">Opcional. JPG o PNG, hasta 2 MB.</span>
                    </div>

                    <div class="actions">
                        <button type="submit" class="btn btn--primary">Enviar consulta</button>
                        <button type="button" class="btn btn--ghost" onclick="cerrarModal('modalNuevoTicket')">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    @endunless
@endsection
