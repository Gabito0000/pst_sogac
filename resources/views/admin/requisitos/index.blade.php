@extends('layouts.plantilla_admin')

@section('title', 'Gestión de Requisitos')

@section('content')
<div class="container main">
    {{-- Alertas de Laravel (validaciones y éxito) --}}
    @if($errors->any())
        <div class="alert alert--error">
            <ul style="margin:0; padding-left:20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert--success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
            <div>
                <h2 class="card__title">Requisitos de Trámites</h2>
                <p class="card__sub" style="margin-bottom:0;">
                    Configura qué documentos y condiciones se solicitan para cada tipo de trámite académico.
                </p>
            </div>
            <a href="{{ route('admin.requisitos.create') }}" class="btn btn--primary">+ Nuevo Requisito</a>
        </div>

        @if($requisitos->isEmpty())
            <p>
                Aún no hay requisitos configurados.
                <a href="{{ route('admin.requisitos.create') }}">Crea el primero aquí.</a>
            </p>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Requisito</th>
                            <th>Formato esperado</th>
                            <th>Trámites donde aplica</th>
                            <th style="text-align:right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requisitos as $r)
                            <tr>
                                <td>
                                    <strong>{{ $r->req_nombre_requisito }}</strong>
                                    @if($r->req_descripcion)
                                        <div style="font-size:0.85rem; color:var(--gray-700);">{{ $r->req_descripcion }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($r->req_formato_esperado)
                                        <span class="badge" style="background:var(--gray-100); color:var(--gray-900);">{{ $r->req_formato_esperado }}</span>
                                    @else
                                        <span style="color:var(--gray-400);">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($r->tiposSolicitud->isEmpty())
                                        <span style="color:var(--gray-400);">Sin asignar</span>
                                    @else
                                        <div style="display:flex; flex-wrap:wrap; gap:4px;">
                                            @foreach ($r->tiposSolicitud as $tipo)
                                                <span class="badge {{ $tipo->pivot->tsr_es_obligatorio ? 'badge--pendiente' : 'badge--aprobada' }}"
                                                      title="{{ $tipo->pivot->tsr_es_obligatorio ? 'Obligatorio' : 'Opcional' }}">
                                                    {{ $tipo->tsi_nombre_tipo }}
                                                    @if($tipo->pivot->tsr_es_obligatorio)
                                                        *
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td style="text-align:right; white-space:nowrap;">
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
</div>
@endsection
