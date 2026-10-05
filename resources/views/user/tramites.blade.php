@extends('layouts.plantilla_user')

@section('title', 'Trámites')

@section('content')
<div class="pagina-head">
  <div>
    <h1 class="pagina-head__titulo">Realizar nuevos trámites</h1>
    <p class="pagina-head__desc">Selecciona el trámite de control de estudio que deseas iniciar.</p>
  </div>
</div>

<div class="card">
  @if ($tramites->isEmpty())
    <p class="estado-vacio">
      No hay trámites disponibles por ahora. Vuelve a revisar más adelante.
    </p>
  @else
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>Trámite</th>
            <th>Disponible hasta</th>
            <th class="table__acciones">Acción</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($tramites as $tramite)
            <tr>
              <td>
                <strong>{{ $tramite->tsi_nombre_tipo }}</strong>
                @if ($tramite->tsi_descripcion)
                  <div class="kpi__hint">{{ $tramite->tsi_descripcion }}</div>
                @endif
              </td>
              <td>{{ $tramite->tsi_fecha_fin?->format('d/m/Y') ?? 'Sin fecha límite' }}</td>
              <td class="table__acciones">
                <a href="{{ route('user.tramites.solicitar', $tramite->tsi_id) }}" class="btn btn--primary btn--sm">Solicitar</a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</div>
@endsection