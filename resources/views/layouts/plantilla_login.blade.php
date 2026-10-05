<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title', 'Autenticación') — Solicítalo</title>
  
  <link rel="stylesheet" href="{{ asset('style_admin.css') }}" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">
  
  <header class="topbar h-16 bg-white shadow flex items-center px-6 fixed top-0 left-0 right-0 z-50">
    <div class="container mx-auto">
      <a href="{{ url('/login') }}" class="brand flex items-center gap-2 text-xl font-bold" style="text-decoration:none;">
        <span class="brand__mark">S</span>
        <span class="brand__name">Solicítalo</span>
      </a>
    </div>
  </header>
  
  <main class="main flex-1 pt-24 pb-12 flex items-center justify-center">
    <!-- El max-w-md es el ancho para el Login, pero el yield permite cambiarlo en el Registro -->
    <div class="auth-wrap w-full @yield('ancho-tarjeta', 'max-w-md') px-4">
      @yield('content')
    </div>
  </main>

  <footer class="footer mt-auto p-4 text-center text-gray-500 bg-white border-t">
    <div class="container mx-auto">
      <p>&copy; {{ date('Y') }} Sistema de Solicitudes Estudiantiles</p>
    </div>
  </footer>
</body>
</html>