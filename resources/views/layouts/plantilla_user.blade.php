<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Solicítalo — Gestión de Solicitudes</title>
  
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="stylesheet" href="{{ asset('style_admin.css') }}" />
</head>
<body>
  <header class="topbar">
    <div class="container topbar__inner">
      <div class="brand">
        <span class="brand__mark">S</span>
        <span class="brand__name">Solicítalo</span>
      </div>
      <nav class="nav">
        <a>Inicio</a>
        <span class="nav__user">{{ Auth::user()->usu_primer_nombre ?? 'Invitado' }}</span>
        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
          @csrf
          <button type="submit" class="btn btn--ghost">Salir</button>
        </form>
      </nav>
    </div>
  </header>

  <main class="main-content">
      @yield('content')
  </main>

</body>
</html>