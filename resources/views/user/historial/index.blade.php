@extends('layouts.plantilla_user')

@section('title', 'Mis solicitudes')

@section('content')
    @php
        use Illuminate\Support\Str;
    @endphp

    @include('partials.avisos')

    <div class="pagina-head">
        <div>
            <h1 class="pagina-head__titulo">Historial de solicitudes</h1>
            <p class="pagina-head__desc">
                Todo lo que has enviado, con el estado en el que está cada trámite y la fecha en que se
                resolvió. Abre cualquiera de ellas para ver su recorrido completo.
            </p>
        </div>

        <div class="pagina-head__acciones">
            <a href="{{ route('dashboard') }}" class="btn btn--ghost">Ir al inicio</a>
            <a href="{{ route('user.tramites.index') }}" class="btn btn--primary">Ver trámites</a>
        </div>
    </div>

    {{-- Resumen: siempre sobre todas las solicitudes, no sobre el filtro, para
         que la respuesta a "¿cómo voy?" no cambie al buscar. --}}
    <div class="mini-stats" style="margin-bottom: 24px;">
        <div class="mini-stat">
            <div class="mini-stat__label">En total</div>
            <div class="mini-stat__value">{{ $resumen['total'] }}</div>
        </div>
        <div class="mini-stat">
            <div class="mini-stat__label">Pendientes</div>
            <div class="mini-stat__value" style="color: #8a5a00;">{{ $resumen['pendiente'] }}</div>
        </div>
        <div class="mini-stat">
            <div class="mini-stat__label">Aprobadas</div>
            <div class="mini-stat__value" style="color: #14683a;">{{ $resumen['aprobada'] }}</div>
        </div>
        <div class="mini-stat">
            <div class="mini-stat__label">Rechazadas</div>
            <div class="mini-stat__value" style="color: var(--red-dark);">{{ $resumen['rechazada'] }}</div>
        </div>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('user.historial.index') }}" class="toolbar">
            <div class="filtros">
                <input type="text"
                       name="q"
                       value="{{ $filtros['q'] }}"
                       placeholder="Buscar por código o por el motivo que escribiste…"
                       aria-label="Buscar en mis solicitudes">

                <select name="estado" aria-label="Filtrar por estado">
                    <option value="">Todos los estados</option>
                    @foreach ($estados as $estado)
                        <option value="{{ $estado }}" @selected($filtros['estado'] === $estado)>
                            {{ ucfirst($estado) }}
                        </option>
                    @endforeach
                </select>

                <select name="tipo" aria-label="Filtrar por trámite">
                    <option value="">Todos los trámites</option>
                    @foreach ($tipos as $tipo)
                        <option value="{{ $tipo->tsi_id }}" @selected($filtros['tipo'] === $tipo->tsi_id)>
                            {{ $tipo->tsi_nombre_tipo }}
                        </option>
                    @endforeach
                </select>

                <label class="filtros__rango">
                    <span>Desde</span>
                    <input type="date" name="desde" value="{{ $filtros['desde'] }}">
                </label>

                <label class="filtros__rango">
                    <span>Hasta</span>
                    <input type="date" name="hasta" value="{{ $filtros['hasta'] }}">
                </label>

                <select name="orden" aria-label="Ordenar el listado">
                    <option value="recientes" @selected($filtros['orden'] !== 'antiguas')>Más recientes primero</option>
                    <option value="antiguas" @selected($filtros['orden'] === 'antiguas')>Más antiguas primero</option>
                </select>
            </div>

            <div class="toolbar__grupo">
                <button type="submit" class="btn btn--primary btn--sm">Filtrar</button>

                @if ($filtros['q'] !== '' || $filtros['estado'] !== '' || $filtros['tipo'] || $filtros['desde'] || $filtros['hasta'] || $filtros['orden'] === 'antiguas')
                    <a href="{{ route('user.historial.index') }}" class="btn btn--ghost btn--sm">Limpiar</a>
                @endif
            </div>
        </form>

        @if ($solicitudes->isEmpty())
            @if ($filtros['q'] !== '' || $filtros['estado'] !== '' || $filtros['tipo'])
                <p class="estado-vacio">
                    Ninguna solicitud coincide con el filtro.
                    <br>
                    <a href="{{ route('user.historial.index') }}">Quitar los filtros</a> para verlas todas.
                </p>
            @else
                <p class="estado-vacio">
                    Todavía no has enviado ninguna solicitud.
                    <br>
                    <a href="{{ route('user.tramites.index') }}">Revisa los trámites disponibles</a> para empezar.
                </p>
            @endif
        @else
            @if ($solicitudes->total() !== $resumen['total'])
                <p class="lista__conteo">
                    Mostrando {{ $solicitudes->total() }} de {{ $resumen['total'] }} solicitudes.
                </p>
            @endif

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 150px;">Fecha</th>
                            <th style="width: 130px;">Código</th>
                            <th>Trámite y motivo</th>
                            <th style="width: 130px;">Estado</th>
                            <th style="width: 110px;">Antigüedad</th>
                            <th class="table__acciones" style="width: 110px;">Detalle</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($solicitudes as $solicitud)
                            @php
                                $estado = strtolower($solicitud->estadoActual->eso_nombre_estado);

                                // Carbon 3 devuelve un float en diffInDays(), y sin
                                // redondear el texto sale "Hace 1.1373440287847 días".
                                $dias = (int) floor($solicitud->sol_fecha_creacion->diffInDays(now()));
                            @endphp

                            <tr>
                                <td style="white-space: nowrap; color: var(--gray-700); font-size: 0.85rem;">
                                    {{ $solicitud->sol_fecha_creacion->format('d/m/Y') }}
                                    <span style="display: block; color: var(--gray-400); font-size: 0.78rem;">
                                        {{ $solicitud->sol_fecha_creacion->format('H:i') }}
                                    </span>
                                </td>

                                <td style="font-family: ui-monospace, monospace; font-size: 0.82rem; color: var(--gray-700);">
                                    {{ $solicitud->sol_id_seguimiento }}
                                </td>

                                <td>
                                    <strong style="display: block; margin-bottom: 4px;">
                                        {{ $solicitud->tipoSolicitud->tsi_nombre_tipo }}
                                    </strong>
                                    <span style="color: var(--gray-700); font-size: 0.88rem;">
                                        {{ Str::limit($solicitud->sol_motivo_detallado ?? 'Sin motivo detallado', 110) }}
                                    </span>
                                </td>

                                <td>
                                    <span class="badge badge--{{ $estado }}">{{ $estado }}</span>
                                    @if ($solicitud->sol_fecha_resolucion)
                                        <span style="display: block; color: var(--gray-400); font-size: 0.78rem; margin-top: 4px;">
                                            {{ $solicitud->sol_fecha_resolucion->format('d/m/Y') }}
                                        </span>
                                    @endif
                                </td>

                                <td style="color: var(--gray-700); font-size: 0.85rem; white-space: nowrap;">
                                    @if ($dias === 0)
                                        Hoy
                                    @else
                                       Hace {{ $dias }} {{ $dias === 1 ? 'día' : 'días' }}
                                    @endif
                                </td>

                                <td class="table__acciones">
                                    <a href="{{ route('user.historial.show', $solicitud) }}" class="btn btn--dark btn--sm">
                                        Ver ficha
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 20px;">
                {{ $solicitudes->links('vendor.pagination.custom', ['etiqueta' => 'solicitudes']) }}
            </div>
        @endif
    </div>
@endsection