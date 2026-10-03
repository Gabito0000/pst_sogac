<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Solicítalo — @yield('title', 'Panel Admin')</title>
  
<<<<<<< HEAD
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="stylesheet" href="{{ asset('style_admin.css') }}" />
  
  <style>
    /* Estilo idéntico al menú de usuario para el botón de salir en la barra de navegación */
    .nav form button.logout-btn {
      background: transparent;
      border: none;
      font-family: inherit;
      color: inherit;
      font-weight: 500;
      font-size: 0.9rem;
      padding: 0;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      transition: color 0.2s ease, opacity 0.2s ease;
    }
    .nav form button.logout-btn:hover {
      color: var(--red, #ef4444);
      opacity: 0.85;
    }
  </style>
=======
  <link rel="stylesheet" href="{{ asset('style_admin.css') }}" />
>>>>>>> 7d0685b4379ba4764a11a7f976b77bb0be3b5bb1
</head>
<body>
  <header class="topbar">
    <div class="container topbar__inner">
<<<<<<< HEAD
      <a href="{{ route('admin.dashboard') }}" class="brand" style="text-decoration:none;">
=======
      <a href="{{ route('admin.dashboard') }}" class="brand">
>>>>>>> 7d0685b4379ba4764a11a7f976b77bb0be3b5bb1
        <span class="brand__mark">S</span>
        <span class="brand__name">Solicítalo <span style="font-size: 0.8rem; background: var(--red); padding: 2px 8px; border-radius: 4px;">ADMIN</span></span>
      </a>
      
      <nav class="nav">
<<<<<<< HEAD
        <a href="{{ route('admin.dashboard') }}">Inicio</a>
        <a href="{{ route('admin.tipos-solicitud.index') }}">Trámites</a>
        <a href="{{ route('admin.requisitos.index') }}">Requisitos</a>
        <a href="{{ route('admin.chat.index') }}">Soporte</a>
=======
        <a href="{{ route('admin.tipos-solicitud.index') }}" class="btn btn--primary btn--sm">Gestionar Trámites</a>
        <a href="{{ route('admin.requisitos.index') }}" class="btn btn--primary btn--sm">Catálogo de Requisitos</a>
        <a href="{{ route('admin.chat.index') }}" class="btn btn--primary btn--sm">Bandeja de Soporte</a>
>>>>>>> 7d0685b4379ba4764a11a7f976b77bb0be3b5bb1
        
        @auth
            <span class="nav__user">{{ Auth::user()->usu_primer_nombre ?? 'Invitado' }}</span>
        @else
            <span class="nav__user">Modo Invitado</span>
        @endauth
        
<<<<<<< HEAD
        <form action="{{ route('logout') }}" method="POST" style="margin:0; display:inline;">
          @csrf
          <button type="submit" class="logout-btn">Salir</button>
=======
        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
          @csrf
          <button type="submit" class="btn btn--ghost">Salir</button>
>>>>>>> 7d0685b4379ba4764a11a7f976b77bb0be3b5bb1
        </form>
      </nav>
    </div>
  </header>
  
<<<<<<< HEAD
  <main class="main container">
=======
  <main>
>>>>>>> 7d0685b4379ba4764a11a7f976b77bb0be3b5bb1
      @yield("content")
  </main>
  
  <footer class="footer">
    <div class="container">
      <p>&copy; {{ now()->year }} Solicítalo — Administrador</p>
    </div>
  </footer>
</body>
</html>