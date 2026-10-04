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
  @include('partials.cabecera')

  <header class="topbar">
    <div class="container topbar__inner">
      <a href="{{ route('admin.dashboard') }}" class="brand" style="text-decoration:none;">
        <span class="brand__mark">S</span>
        <span class="brand__name">Solicítalo <span style="font-size: 0.8rem; background: var(--red); padding: 2px 8px; border-radius: 4px;">ADMIN</span></span>
      </a>
      
      <nav class="nav">
        {{-- Sector 1: configuracion del catalogo academico. --}}
        <div class="nav__group">
          <a href="{{ route('admin.dashboard') }}" @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif>Inicio</a>
          <a href="{{ route('admin.tipos-solicitud.index') }}" @if (request()->routeIs('admin.tipos-solicitud*')) aria-current="page" @endif>Trámites</a>
          <a href="{{ route('admin.requisitos.index') }}" @if (request()->routeIs('admin.requisitos*')) aria-current="page" @endif>Requisitos</a>
        </div>

        <span class="nav__sep" aria-hidden="true"></span>

        {{-- Sector 2: medicion del proceso y trazabilidad.
             "Estadísticas" responde cuánto va el proceso de solicitudes y
             "Historial de cambios" responde quién tocó qué y con qué valores:
             son dos preguntas distintas y por eso dos entradas. --}}
        <div class="nav__group">
          <a href="{{ route('admin.estadisticas') }}" @if (request()->routeIs('admin.estadisticas*')) aria-current="page" @endif>Estadísticas</a>
          <a href="{{ route('admin.cambios.index') }}" @if (request()->routeIs('admin.cambios.*')) aria-current="page" @endif>Historial de cambios</a>
        </div>

        <span class="nav__sep" aria-hidden="true"></span>

        {{-- Sector 3: atencion al estudiante. Antes este sector era un unico
             enlace "Soporte" que abria el chat, y la gestion de las preguntas
             frecuentes no era alcanzable desde la interfaz. --}}
        <div class="nav__group">
          <a href="{{ route('admin.preguntas.index') }}" @if (request()->routeIs('admin.preguntas*')) aria-current="page" @endif>Preguntas frecuentes</a>
          <a href="{{ route('admin.chat.index') }}" @if (request()->routeIs('admin.chat*')) aria-current="page" @endif>Chats</a>
        </div>

        <span class="nav__sep" aria-hidden="true"></span>

        {{-- Vuelta al homepage, que es la pagina principal del sitio. --}}
        <a href="{{ route('portada') }}" class="nav__sitio">
          <i class="bi bi-house-door"></i> Ir al sitio
        </a>

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
      <p>&copy; {{ now()->year }} Sistema de Solicitudes Estudiantiles — UPTP "Juan de Jesús Montilla"</p>
    </div>
  </footer>

  @stack('scripts')
  @include('partials.modal')
</body>
</html>
