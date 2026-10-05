@extends('layouts.plantilla_admin')

@section('title', 'Nuevo requisito')

@section('content')
    <nav class="miga" aria-label="Ruta de navegación">
        <a href="{{ route('admin.requisitos.index') }}" class="miga__actual">Requisitos</a>
        <span class="miga__sep" aria-hidden="true">/</span>
        <span class="miga__actual" aria-current="page">Nuevo requisito</span>
    </nav>

    <div class="pagina-head">
        <div>
            <h1 class="pagina-head__titulo">Nuevo requisito</h1>
            <p class="pagina-head__desc">Define el documento o condición y en qué trámites debe solicitarse.</p>
        </div>
    </div>

    <form action="{{ route('admin.requisitos.store') }}" method="POST" class="card">
        @csrf

        <div class="field">
            <label for="req_nombre_requisito">Nombre del requisito</label>
            <input type="text" name="req_nombre_requisito" id="req_nombre_requisito"
                   placeholder="Ej: Planilla de solicitud firmada"
                   value="{{ old('req_nombre_requisito') }}" required />
        </div>

        <div class="field">
            <label for="req_descripcion">Descripción</label>
            <textarea name="req_descripcion" id="req_descripcion"
                      placeholder="Detalle del requisito (opcional)">{{ old('req_descripcion') }}</textarea>
        </div>

        <div class="field">
            <label for="req_formato_esperado">Formato esperado</label>
            <input type="text" name="req_formato_esperado" id="req_formato_esperado"
                   placeholder="Ej: PDF, Mín. 150dpi, Copia legible"
                   value="{{ old('req_formato_esperado') }}" />
        </div>

        <fieldset class="form__section">
            <legend class="panel__title">Asignar a trámites</legend>
            <p class="field__hint" style="margin-bottom: 14px;">
                Marca en qué tipos de solicitud se pedirá este documento. Activa
                <strong>Obligatorio</strong> cuando sea indispensable.
            </p>

            @if ($tiposSolicitud->isEmpty())
                <p class="estado-vacio">No hay tipos de trámite registrados aún.</p>
            @else
                {{-- Cada fila es una tarjeta con sus propias casillas: antes el
                     checkbox de "Obligatorio" estaba dentro del <label> del otro
                     y al pulsaba se activaban las dos casillas a la vez. --}}
                <div class="panel-grid">
                    @foreach ($tiposSolicitud as $tipo)
                        <div class="panel">
                            <label class="field--checkbox" for="tipo_{{ $tipo->tsi_id }}"
                                   style="display:flex; gap:10px; align-items:center; cursor:pointer;">
                                <input type="checkbox" name="tipos[{{ $tipo->tsi_id }}]" value="1"
                                       id="tipo_{{ $tipo->tsi_id }}"
                                       {{ old("tipos.{$tipo->tsi_id}") ? 'checked' : '' }} />
                                <strong style="font-size:0.92rem;">{{ $tipo->tsi_nombre_tipo }}</strong>
                            </label>

                            <label class="field--checkbox" for="obligatorio_{{ $tipo->tsi_id }}"
                                   style="display:flex; gap:8px; align-items:center; margin-top: 10px; cursor:pointer;">
                                <input type="checkbox" name="obligatorios[{{ $tipo->tsi_id }}]" value="1"
                                       id="obligatorio_{{ $tipo->tsi_id }}"
                                       {{ old("obligatorios.{$tipo->tsi_id}") ? 'checked' : '' }} />
                                Obligatorio
                            </label>
                        </div>
                    @endforeach
                </div>
            @endif
        </fieldset>

        <div class="actions">
            <button type="submit" class="btn btn--primary">Crear requisito</button>
            <a href="{{ route('admin.requisitos.index') }}" class="btn btn--ghost">Cancelar</a>
        </div>
    </form>
@endsection