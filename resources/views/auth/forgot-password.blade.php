@extends('layouts.plantilla_login')
@section('title', 'Recuperar Contraseña')

@section('content')
<div class="auth-card bg-white rounded-lg shadow-lg overflow-hidden">
  <div class="auth-card__head p-6 border-b text-center">
    <h1 class="text-2xl font-bold text-gray-800">Recuperar Contraseña</h1>
    <p class="text-sm text-gray-500 mt-2">Ingresa tu correo y te enviaremos un enlace seguro para restablecerla.</p>
  </div>
  
  <div class="auth-card__body p-6">
    <!-- Mensaje de éxito de Laravel -->
    @if (session('status'))
      <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
        {{ session('status') }}
      </div>
    @endif

    @error('email')
      <div class="text-red-500 text-sm mb-4">{{ $message }}</div>
    @enderror

    <form action="{{ route('password.email') }}" method="post" class="space-y-4">
      @csrf
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus 
               class="mt-1 w-full px-4 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500" />
      </div>
      <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition">
        Enviar enlace de recuperación
      </button>
    </form>
  </div>
  
  <div class="auth-card__foot p-4 bg-gray-50 text-center text-sm border-t">
    ¿Recordaste tu contraseña? <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Inicia sesión aquí</a>
  </div>
</div>
@endsection