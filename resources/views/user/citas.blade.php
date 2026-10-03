@extends('layouts.plantilla_general')

@section('content')

<div class="card">
    <h2 class="card__title">Mi calendario de citas</h2>
    <p class="card__sub">Fechas asignadas para validar tus trámites en físico.</p>

    @if($citas->isEmpty())
        <p>No tienes citas asignadas por ahora. Cuando un trámite requiera validación física, tu fecha aparecerá aquí.</p>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Fecha y hora</th>
                        <th>Trámite</th>
                        <th>Lugar</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($citas as $cita)
                        <tr>
                            <td>
                                @if($cita->cit_fecha_hora)
                                    <strong>{{ $cita->cit_fecha_hora->format('d/m/Y') }}</strong>
                                    <div style="color:var(--gray-700); font-size:0.85rem;">{{ $cita->cit_fecha_hora->format('h:i A') }}</div>
                                @else
                                    <span style="color:var(--gray-700);">Fecha por asignar</span>
                                @endif
                            </td>
                            <td>{{ $cita->solicitud->tipoSolicitud->tsi_nombre_tipo ?? '—' }}</td>
                            <td>{{ $cita->cit_lugar ?? 'Por definir' }}</td>
                            <td><span class="badge badge--{{ strtolower($cita->cit_estado) }}">{{ $cita->cit_estado }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
