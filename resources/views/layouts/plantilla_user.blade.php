<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Solicítalo — Gestión de Solicitudes</title>
  
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
        <a href="{{ route('dashboard') }}">Inicio</a>
        <a href="{{ route('dashboard') }}#mis-solicitudes">Mis Solicitudes</a>
        <a href="{{ route('user.citas') }}">Calendario</a>
        <a href="{{ route('user.chat.index') }}">Ayuda</a>
        <a href="{{ route('user.tramites.index') }}">+ Trámite</a>
        <a href="{{ route('user.solicitudes.historial') }}">Historial</a>

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

</body>
</html>
