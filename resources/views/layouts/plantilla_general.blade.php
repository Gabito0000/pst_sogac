<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Solicítalo — @yield('title', 'Gestión de Solicitudes')</title>
  
  <link rel="stylesheet" href="{{ asset('style_admin.css') }}" />
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- ========================================== -->
  <!-- 1. SCRIPT ANTI-PARPADEO Y LÓGICA RESPONSIVA -->
  <!-- ========================================== -->
  <script>
    // Lee la memoria, pero SOLO lo abre si es una pantalla de computadora (>= 768px)
    // Así en teléfonos móviles siempre inicia cerrado y no tapa la pantalla.
    if (localStorage.getItem('estadoMenu') === 'abierto' && window.innerWidth >= 768) {
      document.documentElement.classList.add('menu-abierto');
    }
  </script>

  <style>
    /* ESTADOS DEL MENÚ Y CONTENIDO */
    html.menu-abierto #sidebar-menu {
      transform: translateX(0);
    }
    
    @media (min-width: 768px) {
      html.menu-abierto #main-content {
        margin-left: 16rem; /* Empuja el contenido solo en PC */
      }
    }

    /* CAMBIO DE ICONO DEL BOTÓN: Hamburguesa a X */
    #icon-cerrar { display: none; }
    html.menu-abierto #icon-hamburguesa { display: none; }
    html.menu-abierto #icon-cerrar { display: block; }

    /* ANIMACIONES LISAS */
    body.loaded #sidebar-menu,
    body.loaded #main-content {
      transition: transform 0.3s ease-in-out, margin-left 0.3s ease-in-out;
    }
  </style>
</head>

<body class="bg-gray-50 text-gray-800">

  <!-- ========================================== -->
  <!-- HEADER FIJO -->
  <!-- ========================================== -->
  <header class="topbar h-16 bg-white shadow flex items-center justify-between px-6 fixed top-0 left-0 right-0 z-50">
    <div class="flex items-center gap-4">
      
      <!-- BOTÓN TOGGLE (Hamburguesa / X) -->
      <button id="btn-toggle-menu" class="text-gray-700 hover:text-red-600 transition-colors focus:outline-none">
        
        <!-- Icono Hamburguesa -->
        <svg id="icon-hamburguesa" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
        
        <!-- Icono X -->
        <svg id="icon-cerrar" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>

      </button>

      <!-- LOGO -->
      <a href="{{ url('/') }}" class="brand flex items-center gap-2 text-xl font-bold" style="text-decoration:none;">
        <span class="brand__mark">S</span>
        <span class="brand__name">Solicítalo 
          
          <!-- Muestra el rol solo cuando el usuario es del personal administrativo -->
          @auth
            @if(Auth::user()->esAdministrativo())
              <span style="font-size: 0.8rem; background: var(--red, #dc2626); padding: 2px 8px; border-radius: 4px; color: white;">{{ strtoupper(Auth::user()->usu_rol) }}</span>
            @endif
          @endauth

        </span>
      </a>
    </div>
      
    <!-- DATOS DEL USUARIO -->
    <div class="flex items-center gap-4">
      @auth
          <span class="nav__user font-medium text-gray-700">{{ Auth::user()->usu_primer_nombre }}</span>
          <form action="{{ route('logout') }}" method="POST" style="display: inline; margin: 0;">
            @csrf
            <button type="submit" class="text-red-600 hover:text-red-800 font-semibold btn btn--ghost btn--sm">Salir</button>
          </form>
      @else
          <span class="nav__user font-medium text-gray-700">Invitado</span>
      @endauth
    </div>
  </header>

  <!-- ========================================== -->
  <!-- MENÚ LATERAL (Sin cabecera interna) -->
  <!-- ========================================== -->
  <aside id="sidebar-menu" class="fixed top-16 left-0 h-[calc(100vh-4rem)] w-64 bg-[#121212] text-white z-40 transform -translate-x-full overflow-y-auto shadow-2xl pt-4">
    
    <!-- ENLACES DE NAVEGACIÓN -->
    <nav class="flex flex-col px-4 space-y-2">
      @auth
        @php $usuario = Auth::user(); @endphp

        @if($usuario->esEstudiante())
          <!-- ==================== ESTUDIANTE ==================== -->
          <a href="{{ route('dashboard') }}" class="px-4 py-3 rounded-md hover:bg-gray-800 hover:text-blue-400 transition-colors">Inicio</a>
          <a href="{{ route('user.citas') }}" class="px-4 py-3 rounded-md hover:bg-gray-800 hover:text-blue-400 transition-colors">Calendario</a>
          <a href="{{ route('user.ayuda.preguntas') }}" class="px-4 py-3 rounded-md hover:bg-gray-800 hover:text-blue-400 transition-colors">Ayuda</a>
          <a href="{{ route('user.ayuda.chat.index') }}" class="px-4 py-3 rounded-md hover:bg-gray-800 hover:text-blue-400 transition-colors">Chat de Soporte</a>

        @else
          {{-- ============ PERSONAL ADMINISTRATIVO ============ --}}
          {{-- Cada enlace se muestra solo si el rol lo permite --}}

          {{-- Solicitudes: los tres roles. Es la pantalla de trabajo del taquillero. --}}
          <a href="{{ route('admin.solicitudes.index') }}" class="px-4 py-3 rounded-md hover:bg-gray-800 hover:text-blue-400 transition-colors">Solicitudes</a>
          <a href="{{ route('admin.chat.index') }}" class="px-4 py-3 rounded-md hover:bg-gray-800 hover:text-blue-400 transition-colors">Bandeja de Soporte</a>

          @if($usuario->esAdministrador() || $usuario->esAnalista())
            <a href="{{ route('admin.dashboard') }}" class="px-4 py-3 rounded-md hover:bg-gray-800 hover:text-blue-400 transition-colors">Gestión de Solicitudes</a>
            <a href="{{ route('admin.tipos-solicitud.index') }}" class="px-4 py-3 rounded-md hover:bg-gray-800 hover:text-blue-400 transition-colors">Gestionar Trámites</a>
            <a href="{{ route('admin.requisitos.index') }}" class="px-4 py-3 rounded-md hover:bg-gray-800 hover:text-blue-400 transition-colors">Catálogo de Requisitos</a>
            <a href="{{ route('admin.preguntas.index') }}" class="px-4 py-3 rounded-md hover:bg-gray-800 hover:text-blue-400 transition-colors">Preguntas Frecuentes</a>
          @endif

          @if($usuario->esAdministrador())
            {{-- El panel estadístico y la bitácora son solo del administrador --}}
            <a href="{{ route('admin.estadisticas') }}" class="px-4 py-3 rounded-md hover:bg-gray-800 hover:text-blue-400 transition-colors">Estadísticas</a>
            <a href="{{ route('admin.cambios.index') }}" class="px-4 py-3 rounded-md hover:bg-gray-800 hover:text-blue-400 transition-colors">Bitácora de Cambios</a>
            <a href="{{ route('admin.usuarios.index') }}" class="px-4 py-3 rounded-md hover:bg-gray-800 hover:text-blue-400 transition-colors">Gestión de Usuarios</a>
          @endif
        @endif
      @else
        <!-- INVITADO -->
        <a href="{{ url('/') }}" class="px-4 py-3 rounded-md hover:bg-gray-800 hover:text-blue-400 transition-colors">Página Principal</a>
      @endauth
    </nav>
  </aside>

  <!-- ========================================== -->
  <!-- CONTENIDO PRINCIPAL -->
  <!-- ========================================== -->
  <div id="main-content" class="pt-16 flex flex-col min-h-screen">
    <main class="main container p-6 mx-auto flex-1">
        @yield("content")
    </main>
    
    <footer class="footer mt-auto p-4 text-center text-gray-500">
      <div class="container mx-auto">
        <p>&copy; {{ date('Y') }} Sistema de Solicitudes Estudiantiles — Solicítalo</p>
      </div>
    </footer>
  </div>

  <!-- ========================================== -->
  <!-- SCRIPT PARA BOTÓN TOGGLE Y SWIPE MÓVIL -->
  <!-- ========================================== -->
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const btnToggle = document.getElementById('btn-toggle-menu');
      const htmlClassList = document.documentElement.classList;

      // Retraso para activar animaciones y evitar parpadeo
      setTimeout(() => { document.body.classList.add('loaded'); }, 50);

      // 1. Lógica del Botón Único (Toggle)
      function toggleMenu() {
        if (htmlClassList.contains('menu-abierto')) {
          htmlClassList.remove('menu-abierto');
          localStorage.setItem('estadoMenu', 'cerrado');
        } else {
          htmlClassList.add('menu-abierto');
          localStorage.setItem('estadoMenu', 'abierto');
        }
      }

      if (btnToggle) {
        btnToggle.addEventListener('click', toggleMenu);
      }

      // 2. Lógica para Deslizar (Swipe) en Teléfonos
      let touchStartX = 0;
      let touchEndX = 0;

      document.addEventListener('touchstart', e => {
        touchStartX = e.changedTouches[0].screenX;
      }, { passive: true });

      document.addEventListener('touchend', e => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
      }, { passive: true });

      function handleSwipe() {
        const isMobile = window.innerWidth < 768;
        if (!isMobile) return; // Solo funciona en móvil

        const swipeDistance = touchEndX - touchStartX;

        // Si desliza a la derecha (distancia > 50px) y empezó cerca del borde izquierdo (< 50px)
        if (swipeDistance > 50 && touchStartX < 50 && !htmlClassList.contains('menu-abierto')) {
          toggleMenu();
        }
        
        // Si desliza a la izquierda (distancia < -50px) y el menú está abierto
        if (swipeDistance < -50 && htmlClassList.contains('menu-abierto')) {
          toggleMenu();
        }
      }
    });
  </script>

</body>
</html>