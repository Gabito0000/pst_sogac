<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  
  <title>Solicítalo — @yield('title', 'Soporte')</title>
<<<<<<< HEAD
  
  @vite(['resources/css/app.css', 'resources/js/app.js'])
=======

>>>>>>> 7d0685b4379ba4764a11a7f976b77bb0be3b5bb1
  <link rel="stylesheet" href="{{ asset('style_admin.css') }}" />
</head>
<body>
  <header class="topbar">
    <div class="container topbar__inner">
      <!-- Marca genérica que recarga la página actual o va al inicio -->
      <a href="{{ url('/') }}" class="brand">
        <span class="brand__mark">S</span>
        <span class="brand__name">Solicítalo <span style="font-size: 0.8rem; background: var(--red); padding: 2px 8px; border-radius: 4px;">SOPORTE</span></span>
      </a>
      
      <nav class="nav">        
        <!-- Bloque seguro: Solo intenta mostrar el nombre si hay alguien logueado -->
        @auth
            <span class="nav__user">{{ Auth::user()->usu_primer_nombre ?? 'Invitado' }}</span>
        @else
            <span class="nav__user">Modo Invitado</span>
        @endauth
        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
          @csrf
          <button type="submit" class="btn btn--ghost">Salir</button>
        </form>
      </nav>
    </div>
  </header>
  
  <main>
      @yield("content")
  </main>
  
  <footer class="footer">
    <div class="container">
      <p>&copy; {{ now()->year }} Solicítalo — Módulo de Soporte</p>
    </div>
  </footer>
</body>
</html>