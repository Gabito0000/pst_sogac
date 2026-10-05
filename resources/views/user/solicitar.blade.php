@extends('layouts.plantilla_user')

@section('title', $tramite->tsi_nombre_tipo)

@section('content')
<nav class="miga" aria-label="Ruta de navegación">
  <a href="{{ route('user.tramites.index') }}" class="miga__actual">Trámites</a>
  <span class="miga__sep" aria-hidden="true">/</span>
  <span class="miga__actual" aria-current="page">{{ $tramite->tsi_nombre_tipo }}</span>
</nav>

<div class="pagina-head">
  <div>
    <h1 class="pagina-head__titulo">{{ $tramite->tsi_nombre_tipo }}</h1>
    @if ($tramite->tsi_descripcion)
      <p class="pagina-head__desc">{{ $tramite->tsi_descripcion }}</p>
    @endif
  </div>
</div>

@if ($errors->any())
  <div class="alert alert--error">
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

{{-- Requisitos que el administrador le asigno a ESTE tramite --}}
<div class="card">
  <h2 class="card__title">Documentos que debes tener listos</h2>

  @if ($tramite->requisitos->isEmpty())
    <p class="card__sub" style="margin-top: 10px;">Este trámite no pide requisitos adicionales.</p>
  @else
    <ul class="lista-simple" style="margin-top: 14px;">
      @foreach ($tramite->requisitos as $req)
        <li class="lista-simple__detalle">
          {{ $req->req_nombre_requisito }}
          @if ($req->pivot->tsr_es_obligatorio)
            <span class="badge badge--rechazada">Obligatorio</span>
          @else
            <span class="badge badge--activo">Opcional</span>
          @endif
        </li>
      @endforeach
    </ul>
  @endif
</div>

<form action="{{ route('user.tramites.store', $tramite->tsi_id) }}" method="POST" class="card" style="margin-top: 20px;">
  @csrf

  <h2 class="card__title">Cuéntanos qué necesitas</h2>

  <div class="field" style="margin-top: 16px;">
    <label for="motivo">Motivo de la solicitud</label>
    <textarea name="motivo" id="motivo" rows="5" placeholder="Describe tu solicitud con el mayor detalle posible..." required>{{ old('motivo') }}</textarea>
    <span class="field__hint">Mientras más claro sea el motivo, más rápido podrás resolver el trámite.</span>
  </div>

  <div class="actions">
    <button type="submit" class="btn btn--primary">Enviar solicitud</button>
    <a href="{{ route('user.tramites.index') }}" class="btn btn--ghost">Cancelar</a>
  </div>
</form>
@endsection