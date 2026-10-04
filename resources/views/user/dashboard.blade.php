@extends('layouts.plantilla_general')

@section('content')
<main class="main">
    <div class="container">

        {{-- Alertas de éxito o error --}}
        @if(session('success'))
            <div class="alert alert--success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert--error">{{ session('error') }}</div>
        @endif

        {{-- Encabezado de Bienvenida Limpio (Tu diseño) --}}
        <section class="hero" style="margin-bottom: 24px;">
            <h1 style="font-size: 1.8rem; margin-bottom: 4px; color: var(--black);">Hola, {{ Auth::user()->usu_primer_nombre }}</h1>
            <p style="color: var(--gray-700);">Gestiona tus solicitudes académicas de forma rápida y sencilla desde el menú superior o en la lista a continuación.</p>
        </section>

        {{-- Tarjetas de Estadísticas (Tu diseño) --}}
        <div class="stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 32px;">
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

        {{-- SECCIÓN 1: Trámites Disponibles (Lógica del equipo + Tu diseño) --}}
        <div class="card" style="margin-bottom: 32px; padding: 24px; background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow-md);">
            <h2 class="card__title" style="font-size: 1.25rem; margin-bottom: 4px; color: var(--black);">Trámites de Control de Estudio</h2>
            <p class="card__sub" style="color: var(--gray-700); font-size: 0.95rem; margin-bottom: 20px;">Elige el trámite que necesitas solicitar.</p>

            @if($tramites->isEmpty())
                <p style="color: var(--gray-700); padding: 10px 0;">No hay trámites disponibles por ahora. Vuelve a revisar más adelante.</p>
            @else
                <div class="table-wrap" style="overflow-x: auto;">
                    <table class="table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--gray-200); text-align: left;">
                                <th style="padding: 12px; font-size: 0.85rem; text-transform: uppercase; color: var(--gray-900);">Trámite</th>
                                <th style="padding: 12px; font-size: 0.85rem; text-transform: uppercase; color: var(--gray-900);">Disponible hasta</th>
                                <th style="padding: 12px; font-size: 0.85rem; text-transform: uppercase; color: var(--gray-900); text-align:right;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tramites as $tramite)
                                <tr style="border-bottom: 1px solid var(--gray-200);">
                                    <td style="padding: 14px 12px;">
                                        <strong style="color: var(--black); font-weight: 500;">{{ $tramite->tsi_nombre_tipo }}</strong>
                                        @if($tramite->tsi_descripcion)
                                            <div style="color:var(--gray-700); font-size:0.85rem; margin-top: 4px;">{{ $tramite->tsi_descripcion }}</div>
                                        @endif
                                    </td>
                                    <td style="padding: 14px 12px; color:var(--gray-700); font-size:0.9rem;">
                                        {{ $tramite->tsi_fecha_fin?->format('d/m/Y') ?? 'Sin fecha límite' }}
                                    </td>
                                    <td style="padding: 14px 12px; text-align:right;">
                                        <a href="{{ route('user.tramites.solicitar', $tramite->tsi_id) }}" class="btn btn--primary btn--sm" style="background: var(--red); color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none;">
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

        {{-- SECCIÓN 2: Historial Reciente (Lógica del equipo + Tu diseño) --}}
        <div class="card" id="mis-solicitudes" style="margin-bottom: 32px; padding: 24px; background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow-md);">
            <h2 class="card__title" style="font-size: 1.25rem; margin-bottom: 4px; color: var(--black);">Solicitudes Recientes</h2>
            <p class="card__sub" style="color: var(--gray-700); font-size: 0.95rem; margin-bottom: 20px;">Tus últimas peticiones y su estado actual.</p>

            @if($misSolicitudesRecientes->isEmpty())
                <p style="color: var(--gray-700); padding: 10px 0;">Aún no tienes solicitudes de estudio registradas.</p>
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
                            @foreach ($misSolicitudesRecientes as $s)
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