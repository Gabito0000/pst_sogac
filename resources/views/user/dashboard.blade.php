@extends('layouts.plantilla_user')

@section('content')
<<<<<<< HEAD
<main class="main">
    <div class="container">

        {{-- Alertas de éxito o error --}}
        @if(session('success'))
            <div class="alert alert--success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert--error">{{ session('error') }}</div>
        @endif

        {{-- Encabezado de Bienvenida Limpio --}}
        <section class="hero" style="margin-bottom: 24px;">
            <h1 style="font-size: 1.8rem; margin-bottom: 4px; color: var(--black);">Hola, {{ Auth::user()->usu_primer_nombre }}</h1>
            <p style="color: var(--gray-700);">Gestiona tus solicitudes académicas de forma rápida y sencilla desde el menú superior.</p>
        </section>

        {{-- Tarjetas de Estadísticas --}}
        <div class="stats" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 32px;">
            <div class="card" style="padding: 20px; text-align: center; margin-bottom: 0; background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow-md);">
                <div class="stat__label" style="font-size: 0.85rem; color: var(--gray-700); text-transform: uppercase; font-weight: 600;">Total</div>
                <div class="stat__value" style="font-size: 1.8rem; font-weight: 700; color: var(--black); margin-top: 4px;">{{ $stats['total'] }}</div>
            </div>
            <div class="card" style="padding: 20px; text-align: center; margin-bottom: 0; background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow-md);">
                <div class="stat__label" style="font-size: 0.85rem; color: var(--gray-700); text-transform: uppercase; font-weight: 600;">Pendientes</div>
                <div class="stat__value" style="font-size: 1.8rem; font-weight: 700; color: #d97706; margin-top: 4px;">{{ $stats['pendiente'] }}</div>
            </div>
            <div class="card" style="padding: 20px; text-align: center; margin-bottom: 0; background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow-md);">
                <div class="stat__label" style="font-size: 0.85rem; color: var(--gray-700); text-transform: uppercase; font-weight: 600;">Aprobadas</div>
                <div class="stat__value" style="font-size: 1.8rem; font-weight: 700; color: #15803d; margin-top: 4px;">{{ $stats['aprobada'] }}</div>
            </div>
            <div class="card" style="padding: 20px; text-align: center; margin-bottom: 0; background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow-md);">
                <div class="stat__label" style="font-size: 0.85rem; color: var(--gray-700); text-transform: uppercase; font-weight: 600;">Rechazadas</div>
                <div class="stat__value" style="font-size: 1.8rem; font-weight: 700; color: var(--red); margin-top: 4px;">{{ $stats['rechazada'] }}</div>
            </div>
        </div>

        {{-- SECCIÓN PRINCIPAL: Solicitudes Pendientes Activas --}}
        <div class="card" id="mis-solicitudes" style="margin-bottom: 32px; padding: 24px; background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow-md);">
            <h2 class="card__title" style="font-size: 1.25rem; margin-bottom: 4px; color: var(--black);">Solicitudes Pendientes</h2>
            <p class="card__sub" style="color: var(--gray-700); font-size: 0.95rem; margin-bottom: 20px;">Tus trámites actuales que se encuentran en proceso de revisión.</p>

            @php
                $pendientesList = $misSolicitudesRecientes->filter(function($s) {
                    return strtolower($s->estadoActual->eso_nombre_estado) === 'pendiente';
                });
            @endphp

            @if($pendientesList->isEmpty())
                <p style="color: var(--gray-700); padding: 10px 0;">No tienes solicitudes pendientes en este momento.</p>
            @else
                <div class="table-wrap" style="overflow-x: auto;">
                    <table class="table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--gray-200); text-align: left;">
                                <th style="padding: 12px; font-size: 0.85rem; text-transform: uppercase; color: var(--gray-900);">Fecha</th>
                                <th style="padding: 12px; font-size: 0.85rem; text-transform: uppercase; color: var(--gray-900);">Tipo</th>
                                <th style="padding: 12px; font-size: 0.85rem; text-transform: uppercase; color: var(--gray-900);">Asunto</th>
                                <th style="padding: 12px; font-size: 0.85rem; text-transform: uppercase; color: var(--gray-900);">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pendientesList as $s)
                                @php $estado = strtolower($s->estadoActual->eso_nombre_estado); @endphp
                                <tr style="border-bottom: 1px solid var(--gray-200);">
                                    <td style="padding: 14px 12px; font-size: 0.9rem; color: var(--gray-700);">{{ \Carbon\Carbon::parse($s->sol_fecha_creacion)->format('d/m/Y') }}</td>
                                    <td style="padding: 14px 12px; font-weight: 500; color: var(--black);">{{ $s->tipoSolicitud->tsi_nombre_tipo }}</td>
                                    <td style="padding: 14px 12px; max-width: 280px; color: var(--gray-700); font-size: 0.9rem;">{{ $s->sol_motivo_detallado ?? 'Sin motivo detallado' }}</td>
                                    <td style="padding: 14px 12px;"><span class="badge badge--{{ $estado }}" style="text-transform: capitalize;">{{ $estado }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>
</main>
@endsection
=======

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
>>>>>>> 7d0685b4379ba4764a11a7f976b77bb0be3b5bb1
