@extends('layouts.plantilla_admin')

@section('title', 'Editar trámite')

@section('content')
    <nav class="miga" aria-label="Ruta de navegación">
        <a href="{{ route('admin.tipos-solicitud.index') }}" class="miga__actual">Trámites</a>
        <span class="miga__sep" aria-hidden="true">/</span>
        <span class="miga__actual" aria-current="page">Editar trámite</span>
    </nav>

    <div class="pagina-head">
        <div>
            <h1 class="pagina-head__titulo">Editar trámite</h1>
            <p class="pagina-head__desc">{{ $tipo->tsi_nombre_tipo }}</p>
        </div>
        <div class="pagina-head__acciones">
            @if ($tipo->tsi_estado_tipo === 'activo')
                <span class="chip chip--ok">● Activo</span>
            @else
                <span class="chip chip--neutro">○ Inactivo</span>
            @endif
        </div>
    </div>

    <p class="nota-datos">Para activar o desactivar el trámite usa el botón del listado.</p>

    <form action="{{ route('admin.tipos-solicitud.update', $tipo->tsi_id) }}" method="POST" class="card">
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

        <label class="field--checkbox" for="tsi_requiere_aprobacion_especial"
               style="display:flex; align-items:center; gap:8px; margin-bottom: 18px; cursor:pointer;">
            <input type="checkbox" name="tsi_requiere_aprobacion_especial" id="tsi_requiere_aprobacion_especial"
                   value="1" {{ old('tsi_requiere_aprobacion_especial', $tipo->tsi_requiere_aprobacion_especial) ? 'checked' : '' }} />
            <span style="margin:0;">Requiere aprobación especial</span>
        </label>

        <fieldset class="form__section">
            <legend class="panel__title">Ventana de disponibilidad</legend>
            <p class="field__hint" style="margin-bottom: 14px;">
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
        </fieldset>

        <fieldset class="form__section">
            <legend class="panel__title">Requisitos que debe entregar el estudiante</legend>
            <p class="field__hint" style="margin-bottom: 14px;">
                Marca qué documentos se piden para este trámite. Activa <strong>Obligatorio</strong> si es indispensable.
            </p>

            @if ($requisitos->isEmpty())
                <p class="estado-vacio">
                    Aún no hay requisitos en el catálogo.
                    <br>
                    <a href="{{ route('admin.requisitos.create') }}">Crea uno aquí</a> (se abre en otra pestaña).
                </p>
            @else
                {{-- Cada fila es una tarjeta con sus propias casillas: antes el
                     checkbox de "Obligatorio" estaba dentro del <label> del otro
                     y al pulsaba se activaban las dos casillas a la vez. --}}
                <div class="panel-grid">
                    @foreach ($requisitos as $req)
                        @php
                            $marcado = in_array((string) $req->req_id, $asignados);
                            $obligatorio = in_array((string) $req->req_id, $obligatorios);
                        @endphp
                        <div class="panel">
                            <label class="field--checkbox" for="req_{{ $req->req_id }}"
                                   style="display:flex; gap:10px; align-items:center; cursor:pointer;">
                                <input type="checkbox" name="requisitos[{{ $req->req_id }}]" value="1"
                                       id="req_{{ $req->req_id }}"
                                       {{ old("requisitos.{$req->req_id}", $marcado) ? 'checked' : '' }} />
                                <strong style="font-size:0.92rem;">{{ $req->req_nombre_requisito }}</strong>
                            </label>

                            <label class="field--checkbox" for="obligatorio_{{ $req->req_id }}"
                                   style="display:flex; gap:8px; align-items:center; margin-top: 10px; cursor:pointer;">
                                <input type="checkbox" name="obligatorios[{{ $req->req_id }}]" value="1"
                                       id="obligatorio_{{ $req->req_id }}"
                                       {{ old("obligatorios.{$req->req_id}", $obligatorio) ? 'checked' : '' }} />
                                Obligatorio
                            </label>
                        </div>
                    @endforeach
                </div>
            @endif
        </fieldset>

        <div class="actions">
            <button type="submit" class="btn btn--primary">Guardar cambios</button>
            <a href="{{ route('admin.tipos-solicitud.index') }}" class="btn btn--ghost">Cancelar</a>
        </div>
    </form>
@endsection