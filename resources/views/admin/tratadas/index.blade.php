@extends('layouts.plantilla_admin')

@section('title', 'Solicitudes tratadas')

@section('content')
<div class="container main">

    {{-- Mensaje de éxito --}}
    @if(session('success'))
        <div class="alert alert--success">{{ session('success') }}</div>
    @endif

    {{-- Tarjetas de resumen --}}
    <div class="stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="card" style="padding: 20px; border-left: 5px solid var(--black);">
            <div style="font-size: 0.8rem; text-transform: uppercase; color: var(--gray-400); font-weight: 600;">Total tratadas</div>
            <div style="font-size: 1.6rem; font-weight: 700; color: var(--black); margin-top: 4px;">{{ $resumen['total'] }}</div>
        </div>
        <div class="card" style="padding: 20px; border-left: 5px solid #22a35a;">
            <div style="font-size: 0.8rem; text-transform: uppercase; color: var(--gray-400); font-weight: 600;">Aprobadas</div>
            <div style="font-size: 1.6rem; font-weight: 700; color: var(--black); margin-top: 4px;">{{ $resumen['aprobada'] }}</div>
        </div>
        <div class="card" style="padding: 20px; border-left: 5px solid var(--red);">
            <div style="font-size: 0.8rem; text-transform: uppercase; color: var(--gray-400); font-weight: 600;">Rechazadas</div>
            <div style="font-size: 1.6rem; font-weight: 700; color: var(--black); margin-top: 4px;">{{ $resumen['rechazada'] }}</div>
        </div>
        <div class="card" style="padding: 20px; border-left: 5px solid #d69a00;">
            <div style="font-size: 0.8rem; text-transform: uppercase; color: var(--gray-400); font-weight: 600;">Pendientes con historial</div>
            <div style="font-size: 1.6rem; font-weight: 700; color: var(--black); margin-top: 4px;">{{ $resumen['pendiente'] }}</div>
        </div>
    </div>

    {{-- Aviso de rango invertido --}}
    @if($rangoInvertido)
        <div class="alert alert--warning" style="margin-bottom: 16px;">
            <strong>Rango de fechas invertido:</strong> la fecha «desde» es posterior a la fecha «hasta». El filtro no devolverá resultados hasta corregirlo.
        </div>
    @endif

    <div class="card" style="background: white; border-radius: var(--radius); padding: 28px; box-shadow: var(--shadow-md);">
        <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 4px;">Solicitudes tratadas</h2>
        <p style="color: var(--gray-700); font-size: 0.95rem; margin-bottom: 20px;">
            Historial de todas las solicitudes que han sido movidas de estado al menos una vez.
            Usa los filtros para localizar una resolución concreta.
        </p>

        {{-- Formulario de filtros --}}
        <form method="GET" action="{{ route('admin.tratadas.index') }}" id="form-tratadas" style="margin-bottom: 24px; display: flex; gap: 12px; flex-wrap: wrap; align-items: center;" onsubmit="return false;">
            <div class="buscador-caja" style="position: relative; flex: 1; min-width: 240px;">
                <i class="ti ti-search" aria-hidden="true" style="position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--gray-400); font-size: 15px;"></i>
                <input type="text" name="q" value="{{ $filtros['q'] }}" placeholder="Buscar por código, motivo o estudiante..."
                    style="width: 100%; padding: 10px 14px 10px 38px; border-radius: 10px; border: 1.5px solid var(--gray-200); font-size: 0.95rem;">
            </div>

            <select name="tipo" style="min-width: 180px; padding: 10px 14px; border-radius: 10px; border: 1.5px solid var(--gray-200); font-size: 0.95rem; background-color: white;">
                <option value="">Todos los trámites</option>
                @foreach($tipos as $tipo)
                    <option value="{{ $tipo->tsi_id }}" {{ $filtros['tipo'] == $tipo->tsi_id ? 'selected' : '' }}>
                        {{ $tipo->tsi_nombre_tipo }}
                    </option>
                @endforeach
            </select>

            <select name="estado" style="min-width: 140px; padding: 10px 14px; border-radius: 10px; border: 1.5px solid var(--gray-200); font-size: 0.95rem; background-color: white;">
                <option value="">Todos los estados</option>
                @foreach($estados as $estado)
                    <option value="{{ $estado }}" {{ $filtros['estado'] == $estado ? 'selected' : '' }}>
                        {{ ucfirst($estado) }}
                    </option>
                @endforeach
            </select>

            <select name="responsable" style="min-width: 180px; padding: 10px 14px; border-radius: 10px; border: 1.5px solid var(--gray-200); font-size: 0.95rem; background-color: white;">
                <option value="">Todos los responsables</option>
                @foreach($responsables as $resp)
                    <option value="{{ $resp->usu_id }}" {{ $filtros['responsable'] == $resp->usu_id ? 'selected' : '' }}>
                        {{ $resp->nombre_completo }}
                    </option>
                @endforeach
            </select>

            <input type="date" name="desde" value="{{ $filtros['desde'] }}" style="padding: 10px 14px; border-radius: 10px; border: 1.5px solid var(--gray-200); font-size: 0.95rem; background-color: white;">
            <input type="date" name="hasta" value="{{ $filtros['hasta'] }}" style="padding: 10px 14px; border-radius: 10px; border: 1.5px solid var(--gray-200); font-size: 0.95rem; background-color: white;">

            <select name="orden" style="min-width: 140px; padding: 10px 14px; border-radius: 10px; border: 1.5px solid var(--gray-200); font-size: 0.95rem; background-color: white;">
                <option value="recientes" {{ $filtros['orden'] == 'recientes' ? 'selected' : '' }}>Más recientes</option>
                <option value="antiguas" {{ $filtros['orden'] == 'antiguas' ? 'selected' : '' }}>Más antiguas</option>
            </select>

            <a href="{{ route('admin.tratadas.index') }}" class="btn btn--ghost btn--sm" style="margin-left: auto;">Limpiar</a>
        </form>

        {{-- Tabla de resultados --}}
        <div class="table-wrapper" style="overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 0.88rem;">
                <thead>
                    <tr style="background: var(--gray-100); text-align: left;">
                        <th style="padding: 12px 16px; font-weight: 600; color: var(--gray-700); border-bottom: 2px solid var(--gray-200);">Código</th>
                        <th style="padding: 12px 16px; font-weight: 600; color: var(--gray-700); border-bottom: 2px solid var(--gray-200);">Estudiante</th>
                        <th style="padding: 12px 16px; font-weight: 600; color: var(--gray-700); border-bottom: 2px solid var(--gray-200);">Trámite</th>
                        <th style="padding: 12px 16px; font-weight: 600; color: var(--gray-700); border-bottom: 2px solid var(--gray-200);">Estado</th>
                        <th style="padding: 12px 16px; font-weight: 600; color: var(--gray-700); border-bottom: 2px solid var(--gray-200);">Resuelto por</th>
                        <th style="padding: 12px 16px; font-weight: 600; color: var(--gray-700); border-bottom: 2px solid var(--gray-200);">Fecha resolución</th>
                        <th style="padding: 12px 16px; font-weight: 600; color: var(--gray-700); border-bottom: 2px solid var(--gray-200);">Tiempo</th>
                        <th style="padding: 12px 16px; font-weight: 600; color: var(--gray-700); border-bottom: 2px solid var(--gray-200);">Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tratadas as $s)
                        <tr style="border-bottom: 1px solid var(--gray-200); transition: background 0.15s;">
                            <td style="padding: 12px 16px; font-family: monospace; font-size: 0.82rem; color: var(--gray-700);">
                                {{ $s->sol_id_seguimiento }}
                            </td>
                            <td style="padding: 12px 16px;">
                                <strong>{{ $s->usuario->nombre_completo }}</strong>
                                <br><small style="color: var(--gray-500);">{{ $s->usuario->documento_completo }}</small>
                            </td>
                            <td style="padding: 12px 16px;">{{ $s->tipoSolicitud->tsi_nombre_tipo }}</td>
                            <td style="padding: 12px 16px;">
                                @php
                                    $est = strtolower($s->estadoActual->eso_nombre_estado);
                                    $cls = match($est) {
                                        'aprobada'  => 'badge-punto--aprobada',
                                        'rechazada' => 'badge-punto--rechazada',
                                        default     => 'badge-punto--pendiente',
                                    };
                                @endphp
                                <span class="badge-punto {{ $cls }}">
                                    <span class="badge-punto__dot"></span>
                                    {{ ucfirst($s->estadoActual->eso_nombre_estado) }}
                                </span>
                            </td>
                            <td style="padding: 12px 16px;">
                                @if($s->historialEstados->last()?->responsable)
                                    {{ $s->historialEstados->last()->responsable->nombre_completo }}
                                @else
                                    <span style="color: var(--gray-400);">—</span>
                                @endif
                            </td>
                            <td style="padding: 12px 16px; white-space: nowrap;">
                                @if($s->sol_fecha_resolucion)
                                    {{ $s->sol_fecha_resolucion->format('d/m/Y H:i') }}
                                @else
                                    <span style="color: var(--gray-400);">—</span>
                                @endif
                            </td>
                            <td style="padding: 12px 16px; font-size: 0.8rem; color: var(--gray-600);">
                                @php
                                    // firstWhere() con closure nunca coincide: hay que filtrar con first().
                                    $primera = $s->historialEstados->first(
                                        fn($mov) => in_array($mov->estadoNuevo?->eso_nombre_estado, ['aprobada', 'rechazada'], true)
                                    );
                                    if ($primera && $s->sol_fecha_creacion) {
                                        echo $s->sol_fecha_creacion->diffForHumans($primera->hes_fecha_cambio, true);
                                    } else {
                                        echo '—';
                                    }
                                @endphp
                            </td>
                            <td style="padding: 12px 16px; text-align: right;">
                                <a href="{{ route('admin.tratadas.show', $s) }}" class="btn btn--sm" style="background: var(--red); color: white; padding: 6px 12px; border-radius: 8px;">Ver ficha</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="padding: 40px; text-align: center; color: var(--gray-500);">
                                No hay solicitudes tratadas que coincidan con los filtros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        {{ $tratadas->links('vendor.pagination.custom') }}
    </div>
</div>
@endsection