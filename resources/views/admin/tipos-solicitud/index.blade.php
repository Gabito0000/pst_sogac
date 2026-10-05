@extends('layouts.plantilla_admin')

@section('title', 'Trámites')

@section('content')
    @include('partials.avisos')

    <div class="pagina-head">
        <div>
            <h1 class="pagina-head__titulo">Trámites disponibles</h1>
            <p class="pagina-head__desc">
                Aquí creas cada trámite (Cambio de Carrera, Constancia, etc.), defines por cuánto
                tiempo estará disponible para los estudiantes, y qué documentos deben entregar.
            </p>
        </div>
        <div class="pagina-head__acciones">
            <a href="{{ route('admin.tipos-solicitud.create') }}" class="btn btn--primary">+ Nuevo trámite</a>
        </div>
    </div>

    <div class="card">
        @if ($tipos->isEmpty())
            <p class="estado-vacio">
                Aún no hay ningún trámite creado.
                <br>
                <a href="{{ route('admin.tipos-solicitud.create') }}">Crea el primero aquí</a>.
            </p>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Trámite</th>
                            <th>Disponibilidad</th>
                            <th>Requisitos</th>
                            <th>Estado</th>
                            <th class="table__acciones">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tipos as $tipo)
                            <tr>
                                <td>
                                    <strong>{{ $tipo->tsi_nombre_tipo }}</strong>
                                    @if ($tipo->tsi_descripcion)
                                        <div class="kpi__hint">{{ $tipo->tsi_descripcion }}</div>
                                    @endif
                                </td>
                                <td style="white-space:nowrap;">
                                    @if ($tipo->tsi_fecha_inicio || $tipo->tsi_fecha_fin)
                                        {{ $tipo->tsi_fecha_inicio?->format('d/m/Y') ?? 'Sin inicio' }}
                                        &rarr;
                                        {{ $tipo->tsi_fecha_fin?->format('d/m/Y') ?? 'Sin cierre' }}
                                        <div>
                                            @if ($tipo->estaDisponible())
                                                <span class="chip chip--ok">● Dentro del rango</span>
                                            @else
                                                <span class="chip chip--neutro">● Fuera del rango</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="kpi__hint">Sin límite de fechas</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($tipo->requisitos->isEmpty())
                                        <span class="kpi__hint">Sin requisitos</span>
                                    @else
                                        <div style="display:flex; flex-wrap:wrap; gap:4px;">
                                            @foreach ($tipo->requisitos as $req)
                                                <span class="chip {{ $req->pivot->tsr_es_obligatorio ? 'chip--warn' : 'chip--ok' }}"
                                                      title="{{ $req->pivot->tsr_es_obligatorio ? 'Obligatorio' : 'Opcional' }}">
                                                    {{ $req->req_nombre_requisito }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    {{-- El "switch": un formulario chiquito que solo cambia el estado, sin ir al formulario completo --}}
                                    <form action="{{ route('admin.tipos-solicitud.alternar-estado', $tipo->tsi_id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        @if ($tipo->tsi_estado_tipo === 'activo')
                                            <button type="submit" class="chip chip--ok"
                                                    title="Pulsa para desactivar este trámite"
                                                    style="border:none; cursor:pointer;">● Activo</button>
                                        @else
                                            <button type="submit" class="chip chip--neutro"
                                                    title="Pulsa para activar este trámite"
                                                    style="border:none; cursor:pointer;">○ Inactivo</button>
                                        @endif
                                    </form>
                                </td>
                                <td class="table__acciones">
                                    <div style="display:inline-flex; gap:6px; justify-content:flex-end;">
                                        <a href="{{ route('admin.tipos-solicitud.edit', $tipo->tsi_id) }}" class="btn btn--dark btn--sm">Editar</a>
                                        <form action="{{ route('admin.tipos-solicitud.destroy', $tipo->tsi_id) }}" method="POST"
                                              onsubmit="return confirm('¿Seguro que deseas eliminar este trámite?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn--danger btn--sm">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection