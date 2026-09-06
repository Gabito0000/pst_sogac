<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Solicítalo — Gestión de Solicitudes</title>
  
  <link rel="stylesheet" href="{{ asset('style_admin.css') }}" />
</head>
<body>
  <header class="topbar">
    <div class="container topbar__inner">
      <a href="{{ route('user.dashboard') }}" class="brand">
        <span class="brand__mark">S</span>
        <span class="brand__name">Solicítalo</span>
      </a>
      
      <nav class="nav">
        <a href="{{ route('user.dashboard') }}">Inicio</a>
        
        <span class="nav__user">{{ Auth::user()->nombre ?? 'Invitado' }}</span>

        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn--ghost" style="border: none; background: none; cursor: pointer;">
                Salir
            </button>
        </form>
      </nav>
    </div>
  </header>

  <main class="main-content">
      @yield('content')
  </main>

</body>
</html>