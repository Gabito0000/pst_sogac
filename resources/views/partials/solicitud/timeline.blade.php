{{--
    Línea de tiempo de una solicitud: el alta y cada cambio de estado.

    Antes esto se pintaba dentro de la tabla de resultados del panel del
    administrador, como un acordeón por solicitud; aquí es un componente aparte
    porque lo consume la ficha del estudiante y la idea es la misma: contar el
    recorrido del trámite en orden.

    Espera: $solicitud (con tipoSolicitud y estadoActual) y $historial, la lista
    de App\Models\HistorialEstadoSolicitud con responsable y estados cargados.
--}}
@php
    use Illuminate\Support\Carbon;

    // El primer registro del historial es el alta: su "estado nuevo" es el estado
    // en el que nació la solicitud, así que de ahí sale la línea inicial.
    $alta = $historial->last();
    $estadoInicial = $alta?->estadoNuevo ?? $solicitud->estadoActual;

    $chips = [
        'pendiente' => 'chip--warn',
        'aprobada' => 'chip--ok',
        'rechazada' => 'chip--error',
        'cancelada' => 'chip--error',
        'asistida' => 'chip--ok',
    ];
@endphp

<ol class="timeline">
    <li class="timeline__item">
        <span class="timeline__marcador {{ $chips[strtolower($estadoInicial?->eso_nombre_estado ?? '')] ?? 'chip--neutro' }}"
              aria-hidden="true"></span>

        <div class="timeline__cuerpo">
            <p class="timeline__titulo">
                Solicitud registrada
                @if ($estadoInicial)
                    <span class="chip {{ $chips[strtolower($estadoInicial->eso_nombre_estado)] ?? 'chip--neutro' }}">
                        {{ ucfirst($estadoInicial->eso_nombre_estado) }}
                    </span>
                @endif
            </p>
            <p class="timeline__meta">
                {{ Carbon::parse($solicitud->sol_fecha_creacion)->format('d/m/Y \a \l\a\s H:i') }}
                &middot;
                {{ $solicitud->tipoSolicitud?->tsi_nombre_tipo ?? 'Trámite eliminado' }}
                &middot;
                Código {{ $solicitud->sol_id_seguimiento }}
            </p>
        </div>
    </li>

    @foreach ($historial as $movimiento)
        @php
            $estadoNuevo = $movimiento->estadoNuevo;
            $estadoAnterior = $movimiento->estadoAnterior;
            $nombreNuevo = strtolower($estadoNuevo?->eso_nombre_estado ?? '');
            $nombreAnterior = strtolower($estadoAnterior?->eso_nombre_estado ?? '');
        @endphp

        <li class="timeline__item">
            <span class="timeline__marcador {{ $chips[$nombreNuevo] ?? 'chip--neutro' }}" aria-hidden="true"></span>

            <div class="timeline__cuerpo">
                <p class="timeline__titulo">
                    @if ($estadoAnterior)
                        {{ ucfirst($estadoAnterior->eso_nombre_estado) }}
                        <span class="timeline__flecha" aria-hidden="true">&rarr;</span>
                    @else
                        Cambio de estado
                        <span class="timeline__flecha" aria-hidden="true">&rarr;</span>
                    @endif

                    <span class="chip {{ $chips[$nombreNuevo] ?? 'chip--neutro' }}">
                        {{ ucfirst($estadoNuevo?->eso_nombre_estado ?? 'Desconocido') }}
                    </span>
                </p>

                <p class="timeline__meta">
                    {{ Carbon::parse($movimiento->hes_fecha_cambio)->format('d/m/Y \a \l\a\s H:i') }}
                    @if ($movimiento->responsable)
                        &middot; {{ $movimiento->responsable->nombre_completo }}
                    @endif
                </p>

                @if ($movimiento->hes_observaciones_comentarios)
                    <p class="timeline__nota">{{ $movimiento->hes_observaciones_comentarios }}</p>
                @endif
            </div>
        </li>
    @endforeach
</ol>