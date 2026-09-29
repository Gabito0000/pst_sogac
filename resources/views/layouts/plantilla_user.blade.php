<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Solicítalo — Gestión de Solicitudes</title>
  
  <link rel="stylesheet" href="{{ asset('style_admin.css') }}" />
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
        <span class="nav__user">{{ Auth::user()->usu_primer_nombre ?? 'Invitado' }}</span>
        <form action="{{ route('logout') }}" method="POST" style="margin:0;">
          @csrf
          <button type="submit" class="btn btn--ghost btn--sm">Salir</button>
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