@extends('layouts.plantilla_user')

@section('title', 'Calendario')

@section('content')
<div class="pagina-head">
  <div>
    <h1 class="pagina-head__titulo">Mi calendario de citas</h1>
    <p class="pagina-head__desc">Fechas asignadas para validar tus trámites en físico.</p>
  </div>
</div>

<div class="card">
  @if ($citas->isEmpty())
    <p class="estado-vacio">
      No tienes citas asignadas por ahora. Cuando un trámite requiera validación
      física, tu fecha aparecerá aquí.
    </p>
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
            @php
              // Solo existen estos estilos de badge, asi que un estado
              // desconocido cae en "pendiente" en vez de quedar sin color.
              $estadosConEstilo = ['pendiente', 'aprobada', 'rechazada', 'asistida', 'cancelada', 'activo'];
              $estado = strtolower($cita->cit_estado ?? '');
              $claseEstado = in_array($estado, $estadosConEstilo, true) ? $estado : 'pendiente';
            @endphp
            <tr>
              <td>
                @if ($cita->cit_fecha_hora)
                  <strong>{{ $cita->cit_fecha_hora->format('d/m/Y') }}</strong>
                  <div class="kpi__hint">{{ $cita->cit_fecha_hora->format('h:i A') }}</div>
                @else
                  <span class="kpi__hint">Fecha por asignar</span>
                @endif
              </td>
              <td>{{ $cita->solicitud->tipoSolicitud->tsi_nombre_tipo ?? '—' }}</td>
              <td>{{ $cita->cit_lugar ?? 'Por definir' }}</td>
              <td><span class="badge badge--{{ $claseEstado }}">{{ ucfirst($estado ?: 'pendiente') }}</span></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</div>
@endsection