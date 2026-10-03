@extends('layouts.plantilla_login')
@section('title', 'Crear Nueva Contraseña')

@section('content')
<div class="auth-card bg-white rounded-lg shadow-lg overflow-hidden">
  <div class="auth-card__head p-6 border-b text-center">
    <h1 class="text-2xl font-bold text-gray-800">Nueva Contraseña</h1>
    <p class="text-sm text-gray-500 mt-2">Crea una nueva contraseña segura para tu cuenta.</p>
  </div>
  
  <div class="auth-card__body p-6">
    @error('email')
      <div class="text-red-500 text-sm mb-2">{{ $message }}</div>
    @enderror
    @error('password')
      <div class="text-red-500 text-sm mb-4">{{ $message }}</div>
    @enderror

    <form action="{{ route('password.update') }}" method="post" class="space-y-4">
      @csrf
      
      <!-- Token oculto requerido por Laravel -->
      <input type="hidden" name="token" value="{{ $token }}">

      <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
        <!-- Pre-llenamos el correo porque viene en la URL por seguridad -->
        <input type="email" id="email" name="email" value="{{ request()->email ?? old('email') }}" required readonly 
               class="mt-1 w-full px-4 py-2 border rounded-md bg-gray-100 text-gray-500" />
      </div>

      <div>
        <label for="password" class="block text-sm font-medium text-gray-700">Nueva Contraseña</label>
        <input type="password" id="password" name="password" required autofocus 
               class="mt-1 w-full px-4 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500" />
      </div>

      <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Contraseña</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required 
               class="mt-1 w-full px-4 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500" />
      </div>

      <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition">
        Guardar y Entrar
      </button>
    </form>
  </div>
</div>
@endsection