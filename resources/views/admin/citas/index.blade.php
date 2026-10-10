@extends('layouts.plantilla_admin')
@section('title', 'Gestión de Citas')

@section('content')
<style>
    .admin-header { display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 15px; margin-bottom: 25px; }
    .admin-title { font-size: 24px; font-weight: 700; color: #1f2937; margin: 0 0 5px 0; }
    .admin-desc { color: #6b7280; margin: 0; font-size: 15px; }
    
    /* Tarjetas de Estadísticas */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px; }
    .stat-card { background: white; border: 1px solid #e5e7eb; border-radius: 10px; padding: 20px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .stat-title { font-size: 13px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
    .stat-value { font-size: 28px; font-weight: 700; color: #1f2937; line-height: 1; }
    .stat-blue { border-left: 4px solid #3b82f6; }
    .stat-yellow { border-left: 4px solid #f59e0b; }
    .stat-green { border-left: 4px solid #10b981; }
    .stat-red { border-left: 4px solid #ef4444; }

    /* Filtro de Fecha */
    .filter-bar { background: white; padding: 15px 20px; border-radius: 10px; border: 1px solid #e5e7eb; margin-bottom: 25px; display: flex; align-items: center; gap: 15px; }
    .filter-label { font-weight: 600; color: #374151; font-size: 14px; }
    .filter-input { padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; outline: none; font-size: 14px; }
    .filter-input:focus { border-color: #b91c1c; }
    .filter-btn { background: #b91c1c; color: white; border: none; padding: 9px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; transition: background 0.2s; font-size: 14px; }
    .filter-btn:hover { background: #991b1b; }

    /* Tabla */
    .table-container { background: white; border-radius: 10px; border: 1px solid #e5e7eb; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .data-table { width: 100%; border-collapse: collapse; text-align: left; }
    .data-table th { background: #f9fafb; padding: 12px 20px; font-size: 12px; font-weight: 600; color: #4b5563; text-transform: uppercase; border-bottom: 1px solid #e5e7eb; }
    .data-table td { padding: 15px 20px; border-bottom: 1px solid #e5e7eb; vertical-align: middle; }
    .data-table tr:last-child td { border-bottom: none; }
    .data-table tr:hover { background: #f9fafb; }
    
    /* Botones de Acción */
    .action-group { display: flex; gap: 8px; }
    .btn-action { padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; border: none; transition: opacity 0.2s; }
    .btn-action:hover { opacity: 0.8; }
    .btn-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .btn-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .btn-disabled { background: #f3f4f6; color: #9ca3af; border: 1px solid #e5e7eb; cursor: not-allowed; }
</style>

<div class="admin-header">
    <div>
        <h1 class="admin-title">Monitor de Citas de Taquilla</h1>
        <p class="admin-desc">Control de acceso y validación de trámites presenciales.</p>
    </div>
</div>

@if(session('success'))
<div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 24px; font-weight: 500;">
    {{ session('success') }}
</div>
@endif

<!-- Indicadores Rápidos -->
<div class="stats-grid">
    <div class="stat-card stat-blue">
        <div class="stat-title">Total Agendadas</div>
        <div class="stat-value">{{ $totalDia }}</div>
    </div>
    <div class="stat-card stat-yellow">
        <div class="stat-title">Pendientes (Por Atender)</div>
        <div class="stat-value">{{ $pendientes }}</div>
    </div>
    <div class="stat-card stat-green">
        <div class="stat-title">Estudiantes Atendidos</div>
        <div class="stat-value">{{ $atendidas }}</div>
    </div>
    <div class="stat-card stat-red">
        <div class="stat-title">Inasistencias</div>
        <div class="stat-value">{{ $inasistencias }}</div>
    </div>
</div>

<!-- Filtro de Búsqueda -->
<form action="{{ route('admin.citas.index') }}" method="GET" class="filter-bar">
    <label for="fecha" class="filter-label">Mostrar citas del día:</label>
    <input type="date" name="fecha" id="fecha" class="filter-input" value="{{ $fechaFiltro }}" required>
    <button type="submit" class="filter-btn">Filtrar Calendario</button>
    
    @if($fechaFiltro != date('Y-m-d'))
        <a href="{{ route('admin.citas.index') }}" style="color: #4b5563; font-size: 14px; text-decoration: underline; margin-left: auto;">Volver a hoy</a>
    @endif
</form>

<!-- Tabla Principal -->
<div class="table-container">
    @if($citas->isEmpty())
        <div style="padding: 40px; text-align: center; color: #6b7280;">
            <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin: 0 auto 10px auto;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            <p style="font-size: 16px; font-weight: 500;">No hay citas agendadas para este día.</p>
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Bloque de Hora</th>
                    <th>Estudiante</th>
                    <th>Motivo / Trámite</th>
                    <th>Estado</th>
                    <th>Acciones en Taquilla</th>
                </tr>
            </thead>
            <tbody>
                @foreach($citas as $cita)
                <tr>
                    <td style="font-weight: 700; color: #111827;">
                        {{ $cita->cit_fecha_hora->format('h:i A') }}
                    </td>
                    <td>
                        <div style="font-weight: 600; color: #1f2937;">{{ $cita->usuario->usu_nombre ?? 'Estudiante' }}</div>
                        <div style="font-size: 12px; color: #6b7280;">C.I: {{ $cita->usuario->usu_cedula ?? 'N/A' }}</div>
                    </td>
                    <td>
                        @if($cita->cit_sol_id)
                            <span style="background: #dbeafe; color: #1e40af; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: 700; margin-bottom: 4px; display: inline-block;">SOLICITUD</span>
                            <div style="font-size: 14px; color: #374151;">{{ $cita->solicitud->tipoSolicitud->tsi_nombre_tipo ?? 'N/A' }}</div>
                        @else
                            <span style="background: #f3f4f6; color: #4b5563; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: 700; margin-bottom: 4px; display: inline-block;">TRÁMITE LIBRE</span>
                            <div style="font-size: 14px; color: #374151;">{{ $cita->cit_motivo }}</div>
                        @endif
                    </td>
                    <td>
                        @php
                            $estiloBadge = match(strtolower($cita->cit_estado)) {
                                'atendida' => 'background: #dcfce7; color: #166534;',
                                'inasistencia' => 'background: #fee2e2; color: #991b1b;',
                                'pendiente' => 'background: #fef3c7; color: #92400e;',
                                default => 'background: #f3f4f6; color: #1f2937;',
                            };
                        @endphp
                        <span style="padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; {{ $estiloBadge }}">
                            {{ $cita->cit_estado }}
                        </span>
                    </td>
                    <td>
                        @if(strtolower($cita->cit_estado) === 'pendiente')
                            <div class="action-group">
                                <!-- Botón Atender -->
                                <form action="{{ route('admin.citas.atendida', $cita->cit_id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn-action btn-success" onclick="return confirm('¿Confirmar que el estudiante fue atendido en taquilla?')">✔ Atendido</button>
                                </form>

                                <!-- Botón Inasistencia -->
                                <form action="{{ route('admin.citas.inasistencia', $cita->cit_id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn-action btn-danger" onclick="return confirm('¿Registrar inasistencia para este estudiante?')">✖ No llegó</button>
                                </form>
                            </div>
                        @else
                            <button class="btn-action btn-disabled" disabled>Procesada</button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection