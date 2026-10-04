<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Solicítalo — @yield('title', 'Mis solicitudes')</title>
  
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="stylesheet" href="{{ asset('style_admin.css') }}" />
  
  <style>
    /* Estilo y animación uniforme para el botón de salir en la barra de navegación */
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
      color: var(--red, #ef4444); /* O el color hover que usen tus enlaces */
      opacity: 0.85;
    }
  </style>
</head>
<body>
  <header class="topbar">
    <div class="container topbar__inner">
      <a href="{{ route('dashboard') }}" class="brand" style="text-decoration:none;">
        <span class="brand__mark">S</span>
        <span class="brand__name">Solicítalo</span>
      </a>
      
      <nav class="nav">
        {{-- Sector 1: tramites. Antes "Mis Solicitudes" apuntaba a un ancla
             (#mis-solicitudes) del dashboard y "Historial" a otra pagina que
             ya listaba las mismas solicitudes: dos entradas para lo mismo. --}}
        <div class="nav__group">
          <a href="{{ route('dashboard') }}" @if (request()->routeIs('dashboard')) aria-current="page" @endif>Inicio</a>
          <a href="{{ route('user.tramites.index') }}" @if (request()->routeIs('user.tramites.*')) aria-current="page" @endif>Trámites</a>
          <a href="{{ route('user.historial.index') }}" @if (request()->routeIs('user.historial.*')) aria-current="page" @endif>Mis solicitudes</a>
          <a href="{{ route('user.citas') }}" @if (request()->routeIs('user.citas*')) aria-current="page" @endif>Calendario</a>
        </div>

        <span class="nav__sep" aria-hidden="true"></span>

        {{-- Sector 2: ayuda. Preguntas frecuentes y chat son dos entradas
             distintas: antes el enlace de ayuda iba al chat y las FAQ eran
             inaccesibles desde la interfaz. --}}
        <div class="nav__group">
          <a href="{{ route('user.ayuda.preguntas') }}" @if (request()->routeIs('user.ayuda.preguntas')) aria-current="page" @endif>Preguntas frecuentes</a>
          <a href="{{ route('user.ayuda.chat.index') }}" @if (request()->routeIs('user.ayuda.chat.*')) aria-current="page" @endif>Chat</a>
        </div>

        <span class="nav__user">{{ Auth::user()->usu_primer_nombre ?? 'Invitado' }}</span>
        
        <form action="{{ route('logout') }}" method="POST" style="margin:0; display:inline;">
          @csrf
          <button type="submit" class="logout-btn">Salir</button>
        </form>
      </nav>
    </div>
  </header>

  <main class="main container">
      @yield('content')
  </main>

  <footer class="footer">
    <div class="container">
      <p>&copy; {{ date('Y') }} Sistema de Solicitudes Estudiantiles</p>
    </div>
  </footer>

  @stack('scripts')
  @include('partials.modal')

</body>
</html>
