@extends('layouts.plantilla_login')
@section('title', 'Registro de Cuenta — Solicítalo')
@section('ancho-tarjeta', 'max-w-2xl')
@section('content')
    <div class="auth-wrap">
      <div class="auth-card" style="max-width: 600px;"> <!-- Se amplía un poco para acomodar las columnas -->
        <div class="auth-card__head">
          <h1>Crear Cuenta</h1>
          <p>Regístrate en el sistema estudiantil</p>
        </div>
        
        <div class="auth-card__body">
          
          <!-- Errores de validación agrupados -->
          @if ($errors->any())
            <div class="alert alert--error">
              <ul style="margin:0; padding-left:1.5rem;">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <form action="{{ route('register.post') }}" method="post" class="form">
            @csrf
            
            <!-- Fila: Nombres -->
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="field">
                  <label for="nombre">Primer Nombre *</label>
                  <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" required autofocus />
                </div>
                <div class="field">
                  <label for="segundo_nombre">Segundo Nombre</label>
                  <input type="text" id="segundo_nombre" name="segundo_nombre" value="{{ old('segundo_nombre') }}" />
                </div>
            </div>

            <!-- Fila: Apellidos -->
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="field">
                  <label for="apellido">Primer Apellido *</label>
                  <input type="text" id="apellido" name="apellido" value="{{ old('apellido') }}" required />
                </div>
                <div class="field">
                  <label for="segundo_apellido">Segundo Apellido</label>
                  <input type="text" id="segundo_apellido" name="segundo_apellido" value="{{ old('segundo_apellido') }}" />
                </div>
            </div>

            <!-- Fila: Documento -->
            <div style="display:grid; grid-template-columns: 1fr 2fr; gap: 1rem;">
                <div class="field">
                  <label for="tipo_documento">Tipo *</label>
                  <!-- Select dinámico traído desde la base de datos -->
                  <select id="tipo_documento" name="tipo_documento" required style="width:100%; padding:0.5rem; border:1px solid #ccc; border-radius:4px;">
                      <option value="">Seleccione...</option>
                      @if(isset($tiposDocumentos))
                          @foreach($tiposDocumentos as $tipo)
                              <option value="{{ $tipo->tdo_id }}" {{ old('tipo_documento') == $tipo->tdo_id ? 'selected' : '' }}>
                                  {{ $tipo->tdo_abreviatura }} - {{ $tipo->tdo_nombre_documento }}
                              </option>
                          @endforeach
                      @endif
                  </select>
                </div>
                <div class="field">
                  <label for="documento">Número de Documento *</label>
                  <input type="text" id="documento" name="documento" value="{{ old('documento') }}" required />
                </div>
            </div>

            <!-- Fila: Contacto -->
            <div class="field">
              <label for="telefono">Número de Teléfono</label>
              <input type="text" id="telefono" name="telefono" value="{{ old('telefono') }}" placeholder="Ej: 04121234567" />
            </div>

            <div class="field">
              <label for="email">Correo Electrónico *</label>
              <input type="email" id="email" name="email" value="{{ old('email') }}" required />
            </div>
            
            <!-- Fila: Contraseñas -->
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="field">
                  <label for="password">Contraseña *</label>
                  <input type="password" id="password" name="password" required />
                </div>
                <div class="field">
                  <!-- El name debe ser "password_confirmation" para que Laravel valide con la regla "confirmed" -->
                  <label for="password_confirmation">Confirmar Contraseña *</label>
                  <input type="password" id="password_confirmation" name="password_confirmation" required />
                </div>
            </div>
            
            <button type="submit" class="btn btn--primary btn--block" style="margin-top: 1rem;">Registrarse</button>
          </form>
        </div>
        
        <div class="auth-card__foot">
          ¿Ya tienes una cuenta? <a href="{{ route('login') }}">Inicia sesión aquí</a>
        </div>
      </div>
    </div>
@endsection