@extends('layouts.plantilla_user')

@section('content')

@if(session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert--error">{{ session('error') }}</div>
@endif

<section class="hero">
    <h1>Hola, {{ Auth::user()->usu_primer_nombre }}</h1>
    <p>Gestiona tus solicitudes académicas de forma rápida y sencilla.</p>
</section>

<div class="stats">
    <div class="stat"><div class="stat__label">Total</div><div class="stat__value">{{ $stats['total'] }}</div></div>
    <div class="stat"><div class="stat__label">Pendientes</div><div class="stat__value">{{ $stats['pendiente'] }}</div></div>
    <div class="stat"><div class="stat__label">Aprobadas</div><div class="stat__value">{{ $stats['aprobada'] }}</div></div>
    <div class="stat"><div class="stat__label">Rechazadas</div><div class="stat__value">{{ $stats['rechazada'] }}</div></div>
</div>

{{-- Trámites disponibles para solicitar: esto es lo nuestro, se mantiene igual que antes --}}
<div class="card">
    <h2 class="card__title">Trámites de Control de Estudio</h2>
    <p class="card__sub">Elige el trámite que necesitas solicitar.</p>

    @if($tramites->isEmpty())
        <p>No hay trámites disponibles por ahora. Vuelve a revisar más adelante.</p>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Trámite</th>
                        <th>Disponible hasta</th>
                        <th style="text-align:right;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tramites as $tramite)
                        <tr>
                            <td>
                                <strong>{{ $tramite->tsi_nombre_tipo }}</strong>
                                @if($tramite->tsi_descripcion)
                                    <div style="color:var(--gray-700); font-size:0.85rem;">{{ $tramite->tsi_descripcion }}</div>
                                @endif
                            </td>
                            <td style="color:var(--gray-700); font-size:0.9rem;">
                                {{ $tramite->tsi_fecha_fin?->format('d/m/Y') ?? 'Sin fecha límite' }}
                            </td>
                            <td style="text-align:right;">
                                <a href="{{ route('user.tramites.solicitar', $tramite->tsi_id) }}" class="btn btn--primary btn--sm">
                                    Solicitar
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- Historial propio del estudiante --}}
<div class="card" id="mis-solicitudes">
    <h2 class="card__title">Solicitudes recientes</h2>
    <p class="card__sub">Últimas 5 solicitudes enviadas.</p>

    @if($misSolicitudesRecientes->isEmpty())
        <p>Aún no tienes solicitudes de estudio registradas.</p>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Asunto</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($misSolicitudesRecientes as $s)
                        @php $estado = strtolower($s->estadoActual->eso_nombre_estado); @endphp
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($s->sol_fecha_creacion)->format('d/m/Y') }}</td>
                            <td>{{ $s->tipoSolicitud->tsi_nombre_tipo }}</td>
                            <td style="max-width:280px;">{{ $s->sol_motivo_detallado ?? 'Sin motivo detallado' }}</td>
                            <td><span class="badge badge--{{ $estado }}">{{ $estado }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
