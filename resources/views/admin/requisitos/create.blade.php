@extends('layouts.plantilla_admin')

@section('title', 'Nuevo Requisito')

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
        <h2 class="card__title">Nuevo Requisito</h2>
        <p class="card__sub">Define el documento/condición y en qué trámites debe solicitarse.</p>

        <form action="{{ route('admin.requisitos.store') }}" method="POST" class="form">
            @csrf

            <div class="field">
                <label for="req_nombre_requisito">Nombre del Requisito</label>
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

            <div style="margin-top:8px;">
                <label style="font-size:0.85rem; font-weight:600; color:var(--gray-900); text-transform:uppercase; letter-spacing:0.5px;">
                    Asignar a trámites
                </label>
                <p style="color:var(--gray-700); font-size:0.88rem; margin-bottom:12px;">
                    Marca en qué tipos de solicitud se pedirá este documento. Marca la casilla "Obligatorio" si es indispensable.
                </p>

                @if($tiposSolicitud->isEmpty())
                    <p style="color:var(--gray-400);">No hay tipos de trámite registrados aún.</p>
                @else
                    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:12px;">
                        @foreach ($tiposSolicitud as $tipo)
                            <label style="display:flex; gap:10px; align-items:center; padding:12px 14px; border:1.5px solid var(--gray-200); border-radius:var(--radius-sm); cursor:pointer;">
                                <input type="checkbox" name="tipos[{{ $tipo->tsi_id }}]"
                                       value="1"
                                       {{ old("tipos.{$tipo->tsi_id}") ? 'checked' : '' }} />
                                <span style="flex:1; font-size:0.92rem;">{{ $tipo->tsi_nombre_tipo }}</span>
                                <span style="display:flex; gap:6px; align-items:center; font-size:0.8rem; color:var(--gray-700);">
                                    <input type="checkbox" name="obligatorios[{{ $tipo->tsi_id }}]" value="1"
                                           {{ old("obligatorios.{$tipo->tsi_id}") ? 'checked' : '' }} />
                                    Obligatorio
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="actions">
                <button type="submit" class="btn btn--primary">Crear Requisito</button>
                <a href="{{ route('admin.requisitos.index') }}" class="btn btn--dark">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
