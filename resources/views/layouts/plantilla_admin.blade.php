<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Solicítalo — @yield('title', 'Panel Admin')</title>
  
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
</head>
<body>
  <header class="topbar">
    <div class="container topbar__inner">
      <a href="{{ route('admin.dashboard') }}" class="brand" style="text-decoration:none;">
        <span class="brand__mark">S</span>
        <span class="brand__name">Solicítalo <span style="font-size: 0.8rem; background: var(--red); padding: 2px 8px; border-radius: 4px;">ADMIN</span></span>
      </a>
      
      <nav class="nav">
        <a href="{{ route('admin.dashboard') }}">Inicio</a>
        <a href="{{ route('admin.tipos-solicitud.index') }}">Trámites</a>
        <a href="{{ route('admin.requisitos.index') }}">Requisitos</a>
        <a href="{{ route('admin.chat.index') }}">Soporte</a>
        
        @auth
            <span class="nav__user">{{ Auth::user()->usu_primer_nombre ?? 'Invitado' }}</span>
        @else
            <span class="nav__user">Modo Invitado</span>
        @endauth
        
        <form action="{{ route('logout') }}" method="POST" style="margin:0; display:inline;">
          @csrf
          <button type="submit" class="logout-btn">Salir</button>
        </form>
      </nav>
    </div>
  </header>
  
  <main class="main container">
      @yield("content")
  </main>
  
  <footer class="footer">
    <div class="container">
      <p>&copy; {{ now()->year }} Solicítalo — Administrador</p>
    </div>
  </footer>
</body>
</html>