@extends('layouts.plantilla_admin')

@section('title', 'Requisitos')

@section('content')
    @include('partials.avisos')

    <div class="pagina-head">
        <div>
            <h1 class="pagina-head__titulo">Requisitos de trámites</h1>
            <p class="pagina-head__desc">
                Configura qué documentos y condiciones se solicitan para cada tipo de trámite académico.
            </p>
        </div>
        <div class="pagina-head__acciones">
            <a href="{{ route('admin.requisitos.create') }}" class="btn btn--primary">+ Nuevo requisito</a>
        </div>
    </div>

    <div class="card">
        @if ($requisitos->isEmpty())
            <p class="estado-vacio">
                Aún no hay requisitos configurados.
                <br>
                <a href="{{ route('admin.requisitos.create') }}">Crea el primero aquí</a>.
            </p>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Requisito</th>
                            <th>Formato esperado</th>
                            <th>Trámites donde aplica</th>
                            <th class="table__acciones">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requisitos as $r)
                            <tr>
                                <td>
                                    <strong>{{ $r->req_nombre_requisito }}</strong>
                                    @if ($r->req_descripcion)
                                        <div class="kpi__hint">{{ $r->req_descripcion }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if ($r->req_formato_esperado)
                                        <span class="chip chip--neutro">{{ $r->req_formato_esperado }}</span>
                                    @else
                                        <span class="kpi__hint">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($r->tiposSolicitud->isEmpty())
                                        <span class="kpi__hint">Sin asignar</span>
                                    @else
                                        <div style="display:flex; flex-wrap:wrap; gap:4px;">
                                            @foreach ($r->tiposSolicitud as $tipo)
                                                <span class="chip {{ $tipo->pivot->tsr_es_obligatorio ? 'chip--warn' : 'chip--ok' }}"
                                                      title="{{ $tipo->pivot->tsr_es_obligatorio ? 'Obligatorio' : 'Opcional' }}">
                                                    {{ $tipo->tsi_nombre_tipo }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="table__acciones">
                                    <div style="display:inline-flex; gap:6px; justify-content:flex-end;">
                                        <a href="{{ route('admin.requisitos.edit', $r->req_id) }}" class="btn btn--dark btn--sm">Editar</a>
                                        <form action="{{ route('admin.requisitos.destroy', $r->req_id) }}" method="POST"
                                              onsubmit="return confirm('¿Seguro que deseas eliminar este requisito?');">
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