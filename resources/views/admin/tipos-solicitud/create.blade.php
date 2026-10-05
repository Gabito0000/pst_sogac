@extends('layouts.plantilla_admin')

@section('title', 'Nuevo trámite')

@section('content')
    <nav class="miga" aria-label="Ruta de navegación">
        <a href="{{ route('admin.tipos-solicitud.index') }}" class="miga__actual">Trámites</a>
        <span class="miga__sep" aria-hidden="true">/</span>
        <span class="miga__actual" aria-current="page">Nuevo trámite</span>
    </nav>

    <div class="pagina-head">
        <div>
            <h1 class="pagina-head__titulo">Nuevo trámite</h1>
            <p class="pagina-head__desc">
                Define el trámite, cuándo estará disponible para los estudiantes, y qué deben entregar.
            </p>
        </div>
    </div>

    <form action="{{ route('admin.tipos-solicitud.store') }}" method="POST" class="card">
        @csrf

        <div class="field">
            <label for="tsi_nombre_tipo">Nombre del trámite</label>
            <input type="text" name="tsi_nombre_tipo" id="tsi_nombre_tipo"
                   placeholder="Ej: Cambio de Carrera"
                   value="{{ old('tsi_nombre_tipo') }}" required />
        </div>

        <div class="field">
            <label for="tsi_descripcion">Descripción</label>
            <textarea name="tsi_descripcion" id="tsi_descripcion"
                      placeholder="Explica brevemente en qué consiste este trámite (opcional)">{{ old('tsi_descripcion') }}</textarea>
        </div>

        <div class="field">
            <label for="tsi_tiempo_estimado_dias">Tiempo estimado de respuesta (días)</label>
            <input type="number" min="0" name="tsi_tiempo_estimado_dias" id="tsi_tiempo_estimado_dias"
                   placeholder="Ej: 7" value="{{ old('tsi_tiempo_estimado_dias') }}" />
        </div>

        <label class="field--checkbox" for="tsi_requiere_aprobacion_especial"
               style="display:flex; align-items:center; gap:8px; margin-bottom: 18px; cursor:pointer;">
            <input type="checkbox" name="tsi_requiere_aprobacion_especial" id="tsi_requiere_aprobacion_especial"
                   value="1" {{ old('tsi_requiere_aprobacion_especial') ? 'checked' : '' }} />
            <span style="margin:0;">Requiere aprobación especial (más allá del admin normal)</span>
        </label>

        {{-- Ventana de fechas en la que el trámite esta disponible para los estudiantes --}}
        <fieldset class="form__section">
            <legend class="panel__title">Ventana de disponibilidad</legend>
            <p class="field__hint" style="margin-bottom: 14px;">
                Deja los campos vacíos si el trámite estará disponible indefinidamente (sin fecha de cierre).
            </p>
            <div class="form__row">
                <div class="field">
                    <label for="tsi_fecha_inicio">Disponible desde</label>
                    <input type="date" name="tsi_fecha_inicio" id="tsi_fecha_inicio" value="{{ old('tsi_fecha_inicio') }}" />
                </div>
                <div class="field">
                    <label for="tsi_fecha_fin">Disponible hasta</label>
                    <input type="date" name="tsi_fecha_fin" id="tsi_fecha_fin" value="{{ old('tsi_fecha_fin') }}" />
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
                        <div class="panel">
                            <label class="field--checkbox" for="req_{{ $req->req_id }}"
                                   style="display:flex; gap:10px; align-items:center; cursor:pointer;">
                                <input type="checkbox" name="requisitos[{{ $req->req_id }}]" value="1"
                                       id="req_{{ $req->req_id }}"
                                       {{ old("requisitos.{$req->req_id}") ? 'checked' : '' }} />
                                <strong style="font-size:0.92rem;">{{ $req->req_nombre_requisito }}</strong>
                            </label>

                            <label class="field--checkbox" for="obligatorio_{{ $req->req_id }}"
                                   style="display:flex; gap:8px; align-items:center; margin-top: 10px; cursor:pointer;">
                                <input type="checkbox" name="obligatorios[{{ $req->req_id }}]" value="1"
                                       id="obligatorio_{{ $req->req_id }}"
                                       {{ old("obligatorios.{$req->req_id}") ? 'checked' : '' }} />
                                Obligatorio
                            </label>
                        </div>
                    @endforeach
                </div>
            @endif
        </fieldset>

        {{-- Elegir si se crea ya activo (visible para estudiantes) o como borrador --}}
        <label class="field--checkbox" for="activar_ahora"
               style="display:flex; align-items:center; gap:8px; margin: 20px 0; cursor:pointer;">
            <input type="checkbox" name="activar_ahora" id="activar_ahora" value="1" {{ old('activar_ahora') ? 'checked' : '' }} />
            <span style="margin:0;">Activar de inmediato (apenas lo vean los estudiantes lo pueden solicitar)</span>
        </label>

        <div class="actions">
            <button type="submit" class="btn btn--primary">Crear trámite</button>
            <a href="{{ route('admin.tipos-solicitud.index') }}" class="btn btn--ghost">Cancelar</a>
        </div>
    </form>
@endsection