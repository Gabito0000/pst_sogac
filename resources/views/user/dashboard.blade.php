@extends('layouts.plantilla_user')

@section('title', 'Mi Panel')

@section('content')
    {{-- Alertas de éxito o error --}}
    @if(session('success'))
        <div class="alert alert--success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert--error">{{ session('error') }}</div>
    @endif

    {{-- Encabezado de bienvenida: mismo bloque que usa el resto del sistema --}}
    <div class="pagina-head">
        <div>
            <h1 class="pagina-head__titulo">Hola, {{ Auth::user()->usu_primer_nombre }}</h1>
            <p class="pagina-head__desc">
                Gestiona tus solicitudes académicas de forma rápida y sencilla desde el menú lateral.
            </p>
        </div>
        <div class="pagina-head__acciones">
            <a href="{{ route('user.tramites.index') }}" class="btn btn--primary">Iniciar un trámite</a>
        </div>
    </div>

    {{-- Tarjetas de resumen --}}
    <div class="kpi-grid">
        <div class="kpi">
            <div class="kpi__label">Total</div>
            <div class="kpi__value">{{ $stats['total'] }}</div>
        </div>
        <div class="kpi kpi--pendiente">
            <div class="kpi__label">Pendientes</div>
            <div class="kpi__value">{{ $stats['pendiente'] }}</div>
        </div>
        <div class="kpi kpi--aprobada">
            <div class="kpi__label">Aprobadas</div>
            <div class="kpi__value">{{ $stats['aprobada'] }}</div>
        </div>
        <div class="kpi kpi--rechazada">
            <div class="kpi__label">Rechazadas</div>
            <div class="kpi__value">{{ $stats['rechazada'] }}</div>
        </div>
    </div>

    {{-- Solicitudes pendientes --}}
    @php
        $pendientesList = $misSolicitudesRecientes->filter(function ($s) {
            return strtolower($s->estadoActual->eso_nombre_estado) === 'pendiente';
        });
    @endphp

    <div class="card">
        <h2 class="card__title">Solicitudes pendientes</h2>
        <p class="card__sub" style="margin-bottom: 20px;">
            Tus trámites actuales que se encuentran en proceso de revisión.
        </p>

        @if($pendientesList->isEmpty())
            <p class="estado-vacio">
                No tienes solicitudes pendientes en este momento.
                <br>
                <a href="{{ route('user.tramites.index') }}">Revisa los trámites disponibles</a> para empezar.
            </p>
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
                        @foreach ($pendientesList as $s)
                            @php $estado = strtolower($s->estadoActual->eso_nombre_estado); @endphp
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($s->sol_fecha_creacion)->format('d/m/Y') }}</td>
                                <td>{{ $s->tipoSolicitud->tsi_nombre_tipo }}</td>
                                <td>{{ $s->sol_motivo_detallado ?? 'Sin motivo detallado' }}</td>
                                <td><span class="badge badge--{{ $estado }}">{{ $estado }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection