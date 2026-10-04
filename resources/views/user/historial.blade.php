@extends('layouts.plantilla_user')

@section('content')
<main class="main">
    <div class="container">

        <div class="card" style="padding: 24px; background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow-md);">
            <h2 class="card__title" style="font-size: 1.25rem; margin-bottom: 4px; color: var(--black);">Historial de Solicitudes</h2>
            <p class="card__sub" style="color: var(--gray-700); font-size: 0.95rem; margin-bottom: 20px;">Registro general de todas tus solicitudes enviadas (aprobadas, rechazadas y pendientes).</p>

            @if($misSolicitudesRecientes->isEmpty())
                <p style="color: var(--gray-700); padding: 10px 0;">Aún no tienes un historial de solicitudes registradas.</p>
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