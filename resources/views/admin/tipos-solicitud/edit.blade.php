@extends('layouts.plantilla_admin')

@section('title', 'Editar Trámite')

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

    <div class="card">
        <h2 class="card__title">Editar Trámite: {{ $tipo->tsi_nombre_tipo }}</h2>
        <p class="card__sub">
            Estado actual:
            @if($tipo->tsi_estado_tipo === 'activo')
                <span style="color:#14683a; font-weight:600;">● Activo</span>
            @else
                <span style="color:var(--gray-700); font-weight:600;">○ Inactivo</span>
            @endif
            — para cambiarlo, usa el botón en el listado.
        </p>

        <form action="{{ route('admin.tipos-solicitud.update', $tipo->tsi_id) }}" method="POST" class="form">
            @csrf
            @method('PUT')

            <div class="field">
                <label for="tsi_nombre_tipo">Nombre del trámite</label>
                <input type="text" name="tsi_nombre_tipo" id="tsi_nombre_tipo"
                       value="{{ old('tsi_nombre_tipo', $tipo->tsi_nombre_tipo) }}" required />
            </div>

            <div class="field">
                <label for="tsi_descripcion">Descripción</label>
                <textarea name="tsi_descripcion" id="tsi_descripcion">{{ old('tsi_descripcion', $tipo->tsi_descripcion) }}</textarea>
            </div>

            <div class="field">
                <label for="tsi_tiempo_estimado_dias">Tiempo estimado de respuesta (días)</label>
                <input type="number" min="0" name="tsi_tiempo_estimado_dias" id="tsi_tiempo_estimado_dias"
                       value="{{ old('tsi_tiempo_estimado_dias', $tipo->tsi_tiempo_estimado_dias) }}" />
            </div>

            <div style="display:flex; align-items:center; gap:8px; margin: 8px 0 16px;">
                <input type="checkbox" name="tsi_requiere_aprobacion_especial" id="tsi_requiere_aprobacion_especial"
                       value="1" {{ old('tsi_requiere_aprobacion_especial', $tipo->tsi_requiere_aprobacion_especial) ? 'checked' : '' }} />
                <label for="tsi_requiere_aprobacion_especial" style="margin:0;">Requiere aprobación especial</label>
            </div>

            <div style="margin-top:8px;">
                <label style="font-size:0.85rem; font-weight:600; color:var(--gray-900); text-transform:uppercase; letter-spacing:0.5px;">
                    Ventana de disponibilidad
                </label>
                <p style="color:var(--gray-700); font-size:0.88rem; margin-bottom:12px;">
                    Deja los campos vacíos si el trámite estará disponible indefinidamente.
                </p>
                <div class="form__row">
                    <div class="field">
                        <label for="tsi_fecha_inicio">Disponible desde</label>
                        <input type="date" name="tsi_fecha_inicio" id="tsi_fecha_inicio"
                               value="{{ old('tsi_fecha_inicio', optional($tipo->tsi_fecha_inicio)->format('Y-m-d')) }}" />
                    </div>
                    <div class="field">
                        <label for="tsi_fecha_fin">Disponible hasta</label>
                        <input type="date" name="tsi_fecha_fin" id="tsi_fecha_fin"
                               value="{{ old('tsi_fecha_fin', optional($tipo->tsi_fecha_fin)->format('Y-m-d')) }}" />
                    </div>
                </div>
            </div>

            <div style="margin-top:8px;">
                <label style="font-size:0.85rem; font-weight:600; color:var(--gray-900); text-transform:uppercase; letter-spacing:0.5px;">
                    Requisitos que debe entregar el estudiante
                </label>
                <p style="color:var(--gray-700); font-size:0.88rem; margin-bottom:12px;">
                    Marca qué documentos se piden para este trámite. Marca "Obligatorio" si es indispensable.
                </p>

                @if($requisitos->isEmpty())
                    <p style="color:var(--gray-400);">
                        Aún no hay requisitos en el catálogo.
                        <a href="{{ route('admin.requisitos.create') }}" target="_blank">Crea uno aquí</a>.
                    </p>
                @else
                    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:12px;">
                        @foreach ($requisitos as $req)
                            @php $marcado = in_array((string) $req->req_id, $asignados); @endphp
                            <label style="display:flex; gap:10px; align-items:center; padding:12px 14px; border:1.5px solid var(--gray-200); border-radius:var(--radius-sm); cursor:pointer;">
                                <input type="checkbox" name="requisitos[{{ $req->req_id }}]"
                                       value="1" {{ old("requisitos.{$req->req_id}", $marcado) ? 'checked' : '' }} />
                                <span style="flex:1; font-size:0.92rem;">{{ $req->req_nombre_requisito }}</span>
                                <span style="display:flex; gap:6px; align-items:center; font-size:0.8rem; color:var(--gray-700);">
                                    @php $obligatorio = in_array((string) $req->req_id, $obligatorios); @endphp
                                    <input type="checkbox" name="obligatorios[{{ $req->req_id }}]" value="1"
                                           {{ old("obligatorios.{$req->req_id}", $obligatorio) ? 'checked' : '' }} />
                                    Obligatorio
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="actions">
                <button type="submit" class="btn btn--primary">Guardar Cambios</button>
                <a href="{{ route('admin.tipos-solicitud.index') }}" class="btn btn--dark">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
