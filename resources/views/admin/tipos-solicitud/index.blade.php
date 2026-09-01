@extends('layouts.plantilla_admin')

@section('title', 'Gestión de Trámites')

@section('content')
<div class="container main">
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

    @if(session('error'))
        <div class="alert alert--error">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
            <div>
                <h2 class="card__title">Trámites disponibles</h2>
                <p class="card__sub" style="margin-bottom:0;">
                    Aquí creas cada trámite (Cambio de Carrera, Constancia, etc.), defines por cuánto tiempo estará
                    disponible para los estudiantes, y qué documentos deben entregar.
                </p>
            </div>
            <a href="{{ route('admin.tipos-solicitud.create') }}" class="btn btn--primary">+ Nuevo Trámite</a>
        </div>

        @if($tipos->isEmpty())
            <p>
                Aún no hay ningún trámite creado.
                <a href="{{ route('admin.tipos-solicitud.create') }}">Crea el primero aquí.</a>
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
                            <th style="text-align:right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tipos as $tipo)
                            <tr>
                                <td>
                                    <strong>{{ $tipo->tsi_nombre_tipo }}</strong>
                                    @if($tipo->tsi_descripcion)
                                        <div style="font-size:0.85rem; color:var(--gray-700);">{{ $tipo->tsi_descripcion }}</div>
                                    @endif
                                </td>
                                <td style="font-size:0.85rem; white-space:nowrap;">
                                    @if($tipo->tsi_fecha_inicio || $tipo->tsi_fecha_fin)
                                        {{ $tipo->tsi_fecha_inicio?->format('d/m/Y') ?? 'Sin inicio' }}
                                        &rarr;
                                        {{ $tipo->tsi_fecha_fin?->format('d/m/Y') ?? 'Sin cierre' }}
                                        <br>
                                        @if($tipo->estaDisponible())
                                            <span style="color:#14683a; font-weight:600;">● Dentro del rango</span>
                                        @else
                                            <span style="color:var(--gray-400);">● Fuera del rango</span>
                                        @endif
                                    @else
                                        <span style="color:var(--gray-400);">Sin límite de fechas</span>
                                    @endif
                                </td>
                                <td>
                                    @if($tipo->requisitos->isEmpty())
                                        <span style="color:var(--gray-400);">Sin requisitos</span>
                                    @else
                                        <div style="display:flex; flex-wrap:wrap; gap:4px;">
                                            @foreach ($tipo->requisitos as $req)
                                                <span class="badge {{ $req->pivot->tsr_es_obligatorio ? 'badge--pendiente' : 'badge--aprobada' }}"
                                                      title="{{ $req->pivot->tsr_es_obligatorio ? 'Obligatorio' : 'Opcional' }}">
                                                    {{ $req->req_nombre_requisito }}{{ $req->pivot->tsr_es_obligatorio ? ' *' : '' }}
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
                                        @if($tipo->tsi_estado_tipo === 'activo')
                                            <button type="submit" class="btn btn--sm" style="background:#22a35a; color:white;">● Activo</button>
                                        @else
                                            <button type="submit" class="btn btn--sm" style="background:var(--gray-200); color:var(--gray-700);">○ Inactivo</button>
                                        @endif
                                    </form>
                                </td>
                                <td style="text-align:right; white-space:nowrap;">
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
</div>
@endsection
