@extends('layouts.plantilla_user')
@section('title', 'Mis Citas')

@section('content')
<!-- Encabezado con Botón de Acción a la derecha -->
<div class="pagina-head mb-6" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
    <div>
        <h1 class="pagina-head__titulo text-2xl font-bold text-gray-800" style="margin-bottom: 0;">Mis Citas</h1>
        <p class="pagina-head__desc text-gray-600" style="margin-top: 5px;">Consulta tu historial de citas presenciales.</p>
    </div>
    
    <!-- BOTÓN A LA DERECHA -->
    <a href="{{ route('user.citas.create') }}" style="background-color: #b91c1c; color: white; padding: 10px 20px; border-radius: 8px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: background-color 0.2s;">
        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        Agendar nueva cita
    </a>
</div>

<!-- Alertas -->
@if(session('success'))
<div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 24px;">
    <strong>¡Éxito!</strong> {{ session('success') }}
</div>
@endif

<!-- Tabla de Citas -->
<div class="card bg-white shadow-md rounded-lg overflow-hidden" style="border: 1px solid #f3f4f6;">
    @if ($citas->isEmpty())
        <div style="padding: 40px 20px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <svg width="64" height="64" style="color: #9ca3af; margin-bottom: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <h3 style="font-size: 1.125rem; font-weight: 600; color: #1f2937; margin-bottom: 5px;">No tienes citas programadas</h3>
            <p style="color: #6b7280; font-size: 0.875rem;">Cuando agendes una cita, aparecerá en este listado.</p>
        </div>
    @else
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead style="background-color: #f9fafb; border-bottom: 1px solid #f3f4f6;">
                    <tr>
                        <th style="padding: 15px 20px; color: #4b5563; font-weight: 600; font-size: 0.875rem; text-transform: uppercase;">Fecha y Hora</th>
                        <th style="padding: 15px 20px; color: #4b5563; font-weight: 600; font-size: 0.875rem; text-transform: uppercase;">Detalle del Trámite</th>
                        <th style="padding: 15px 20px; color: #4b5563; font-weight: 600; font-size: 0.875rem; text-transform: uppercase;">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($citas as $cita)
                        <tr style="border-bottom: 1px solid #f3f4f6; transition: background-color 0.15s;" onmouseover="this.style.backgroundColor='#f9fafb'" onmouseout="this.style.backgroundColor='transparent'">
                            <td style="padding: 15px 20px; white-space: nowrap;">
                                <div style="font-weight: 600; color: #1f2937;">{{ $cita->cit_fecha_hora->format('d / m / Y') }}</div>
                                <div style="font-size: 0.875rem; color: #6b7280; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ $cita->cit_fecha_hora->format('h:i A') }}
                                </div>
                            </td>
                            <td style="padding: 15px 20px;">
                                @if($cita->cit_sol_id)
                                    <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; background-color: #dbeafe; color: #1e40af; margin-bottom: 4px;">Vinculado a Solicitud</span>
                                    <div style="color: #374151; font-weight: 500;">{{ $cita->solicitud->tipoSolicitud->tsi_nombre_tipo ?? 'N/A' }}</div>
                                @else
                                    <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; background-color: #f3f4f6; color: #1f2937; margin-bottom: 4px;">Trámite Libre</span>
                                    <div style="color: #374151; font-weight: 500;">{{ $cita->cit_motivo }}</div>
                                @endif
                            </td>
                            <td style="padding: 15px 20px; white-space: nowrap;">
                                @php
                                    $estiloEstado = match(strtolower($cita->cit_estado)) {
                                        'atendida' => 'background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0;',
                                        'inasistencia' => 'background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca;',
                                        'pendiente' => 'background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a;',
                                        default => 'background-color: #f3f4f6; color: #1f2937; border: 1px solid #e5e7eb;',
                                    };
                                @endphp
                                <span style="display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; {{ $estiloEstado }}">
                                    {{ ucfirst($cita->cit_estado) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection