@extends('layouts.plantilla_login')
@section('title', 'Iniciar Sesión — Solicítalo')
@section('content')
    <div class="auth-wrap">
      <div class="auth-card">
        <div class="auth-card__head">
          <h1>Bienvenido</h1>
          <p>Ingresa a tu cuenta en el sistema</p>
        </div>
        
        <div class="auth-card__body">
          
          <!-- Manejo de errores -->
          @error('identificador')
            <div class="alert alert--error">{{ $message }}</div>
          @enderror

          <form action="{{ route('login.post') }}" method="post" class="form">
            @csrf
            
            <div class="field">
              <label for="identificador">Correo electrónico o Número de Documento</label>
              <!-- Cambiamos name="email" a name="identificador" -->
              <input type="text" id="identificador" name="identificador" value="{{ old('identificador') }}" required autofocus />
            </div>
            
            <div class="field">
              <div style="display:flex; justify-content:space-between; align-items:center;">
                  <label for="password">Contraseña</label>
                  <!-- Enlace para recuperar contraseña -->
                  <!-- NOTA: Si aún no tienes la ruta creada, deja href="#" para que no de error -->
                  <a href="{{ route('password.request') ?? '#' }}" style="font-size: 0.85rem; color: var(--blue-600); text-decoration: none;">¿Olvidaste tu contraseña?</a>
              </div>
              <input type="password" id="password" name="password" required />
            </div>

            <label style="display:flex;align-items:center;gap:8px;font-size:0.9rem;color:var(--gray-700);cursor:pointer;margin-bottom:1rem;">
              <input type="checkbox" name="remember" value="1" style="width:16px;height:16px;accent-color:var(--red);" />
              Mantener sesión iniciada
            </label>
            
            <button type="submit" class="btn btn--primary btn--block">Entrar</button>
          </form>
        </div>
        
        <div class="auth-card__foot">
          ¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate aquí</a>
        </div>
      </div>
    </div>
@endsection