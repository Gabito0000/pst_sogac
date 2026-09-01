@extends('layouts.plantilla_admin')

@section('title', 'Panel de Administración')

@section('content')
<div class="container main">
    <section class="hero" style="background: linear-gradient(135deg, #111 0%, #222 100%); border-left: 6px solid var(--red);">
      <h1>Panel de Control Administrativo</h1>
      <p>Revisa la documentación adjunta, aprueba o rechaza los trámites académicos en tiempo real.</p>
    </section>

    {{-- Manejo de mensajes de éxito enviados desde el Controlador --}}
    @if(session('success'))
        <div class="alert alert--success">{{ session('success') }}</div>
    @endif

    <div class="stats">
      {{-- Cada tarjeta es un link que filtra directamente por ese estado. El "Total" limpia el filtro de estado. --}}
      <a href="{{ route('admin.dashboard') }}" class="stat" style="text-decoration:none; color:inherit; {{ !request('estado') ? 'outline: 2px solid var(--red);' : '' }}">
        <div class="stat__label">Total Solicitudes</div><div class="stat__value">{{ $stats['total'] }}</div>
      </a>
      <a href="{{ route('admin.dashboard', ['estado' => 'pendiente']) }}" class="stat" style="text-decoration:none; color:inherit; border-left-color: #ffd6d6; {{ request('estado') === 'pendiente' ? 'outline: 2px solid var(--red);' : '' }}">
        <div class="stat__label">Pendientes</div><div class="stat__value">{{ $stats['pendiente'] }}</div>
      </a>
      <a href="{{ route('admin.dashboard', ['estado' => 'aprobada']) }}" class="stat" style="text-decoration:none; color:inherit; border-left-color: #d6f5e3; {{ request('estado') === 'aprobada' ? 'outline: 2px solid var(--red);' : '' }}">
        <div class="stat__label">Aprobadas</div><div class="stat__value">{{ $stats['aprobada'] }}</div>
      </a>
      <a href="{{ route('admin.dashboard', ['estado' => 'rechazada']) }}" class="stat" style="text-decoration:none; color:inherit; border-left-color: var(--red); {{ request('estado') === 'rechazada' ? 'outline: 2px solid var(--red);' : '' }}">
        <div class="stat__label">Rechazadas</div><div class="stat__value">{{ $stats['rechazada'] }}</div>
      </a>
    </div>

    <div class="card">
      <h2 class="card__title">Listado de Solicitudes Estudiantiles</h2>
      <p class="card__sub">Administra las peticiones ingresadas al sistema por los estudiantes.</p>

      {{-- Estilos propios de esta barra: tabs tipo píldora y badges con punto de color,
           inspirados en paneles tipo Stripe/Vercel. Se quedan aquí (no en style_admin.css)
           para no afectar otras páginas del proyecto. --}}
      <style>
        .buscador-caja { position: relative; flex: 1; min-width: 240px; }
        .buscador-caja input {
          width: 100%; padding: 10px 14px 10px 38px; border-radius: 10px;
          border: 1.5px solid var(--gray-200); font-size: 0.95rem;
        }
        .buscador-caja input:focus { border-color: var(--red); outline: none; }
        .buscador-caja i {
          position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
          color: var(--gray-400); font-size: 15px;
        }
        .tabs-estado { display: flex; background: var(--gray-100); border-radius: 10px; padding: 4px; gap: 2px; }
        .tab-pill {
          text-decoration: none; padding: 7px 16px; border-radius: 8px; font-size: 0.85rem;
          font-weight: 600; color: var(--gray-700); transition: all 0.15s ease;
        }
        .tab-pill.activa { background: white; color: var(--black); box-shadow: 0 1px 3px rgba(0,0,0,0.12); }
        .badge-punto {
          display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px;
          border-radius: 999px; font-size: 0.78rem; font-weight: 600;
        }
        .badge-punto__dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; }
        .badge-punto--pendiente { background: #fff3d6; color: #8a5a00; }
        .badge-punto--pendiente .badge-punto__dot { background: #d69a00; }
        .badge-punto--aprobada { background: #d6f5e3; color: #14683a; }
        .badge-punto--aprobada .badge-punto__dot { background: #22a35a; }
        .badge-punto--rechazada { background: #fdd6d6; color: var(--red-dark); }
        .badge-punto--rechazada .badge-punto__dot { background: var(--red); }
      </style>

      {{-- Barra de búsqueda. Ya NO se envía como formulario tradicional:
           el JS de más abajo intercepta cada acción (escribir, cambiar el select,
           hacer clic en una pestaña o en la paginación) y pide los datos con fetch(),
           sin recargar la página. --}}
      <form method="GET" action="{{ route('admin.dashboard') }}" id="form-busqueda" style="margin-bottom: 24px; display: flex; gap: 12px; flex-wrap: wrap; align-items: center;" onsubmit="return false;">
        <div class="buscador-caja">
          <i class="ti ti-search" aria-hidden="true"></i>
          <input
            type="text"
            id="busqueda"
            name="busqueda"
            placeholder="Buscar por cédula o nombre de estudiante..."
            value="{{ request('busqueda') }}"
          >
        </div>

        <select id="tipo_solicitud" name="tipo_solicitud" style="min-width: 180px;">
          <option value="">Todos los trámites</option>
          @foreach ($tiposSolicitud as $tipo)
            <option value="{{ $tipo->tsi_id }}" @selected(request('tipo_solicitud') == $tipo->tsi_id)>
              {{ $tipo->tsi_nombre_tipo }}
            </option>
          @endforeach
        </select>

        <div class="tabs-estado">
          @php
            $estadoActivo = request('estado');
            $pestanasEstado = ['' => 'Todas', 'pendiente' => 'Pendientes', 'aprobada' => 'Aprobadas', 'rechazada' => 'Rechazadas'];
          @endphp
          @foreach ($pestanasEstado as $valor => $etiqueta)
            <a
              href="#"
              class="tab-pill tab-estado {{ $estadoActivo == $valor ? 'activa' : '' }}"
              data-estado="{{ $valor }}"
            >{{ $etiqueta }}</a>
          @endforeach
        </div>

        <a href="#" id="limpiar-filtros" class="btn btn--sm" style="background: var(--gray-200); color: var(--black); {{ (request('busqueda') || request('tipo_solicitud') || request('estado')) ? '' : 'display:none;' }}">Limpiar filtros</a>
      </form>

      {{-- Este div es lo único que se reemplaza cuando se busca/filtra/pagina --}}
      <div id="resultados-wrapper">
        @include('admin.partials.resultados')
      </div>

      <script>
        (function () {
          const inputBusqueda = document.getElementById('busqueda');
          const selectTipo = document.getElementById('tipo_solicitud');
          const wrapper = document.getElementById('resultados-wrapper');
          const tabs = document.querySelectorAll('.tab-estado');
          const limpiar = document.getElementById('limpiar-filtros');
          const urlBase = "{{ route('admin.dashboard') }}";

          // Estado actual de los filtros (arranca con lo que ya viene en la URL)
          let estadoActual = new URLSearchParams(window.location.search).get('estado') || '';

          // Pide al servidor solo el pedazo de la tabla (fetch = "pedido en segundo plano",
          // no navega a otra página, por eso no hay recarga ni parpadeo).
          function buscar(url) {
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
              .then(function (respuesta) { return respuesta.text(); })
              .then(function (html) {
                wrapper.innerHTML = html;
                history.pushState(null, '', url); // Actualiza la URL sin recargar (para poder compartir el link o recargar F5 y mantener el filtro)
              });
          }

          function construirUrlYBuscar() {
            const params = new URLSearchParams();
            if (inputBusqueda.value) params.set('busqueda', inputBusqueda.value);
            if (selectTipo.value) params.set('tipo_solicitud', selectTipo.value);
            if (estadoActual) params.set('estado', estadoActual);
            const query = params.toString();
            limpiar.style.display = query ? 'inline-block' : 'none';
            buscar(urlBase + (query ? '?' + query : ''));
          }

          // Búsqueda en vivo: espera 400ms después de que el usuario deja de escribir
          let temporizador;
          inputBusqueda.addEventListener('input', function () {
            clearTimeout(temporizador);
            temporizador = setTimeout(construirUrlYBuscar, 400);
          });

          selectTipo.addEventListener('change', construirUrlYBuscar);

          tabs.forEach(function (tab) {
            tab.addEventListener('click', function (e) {
              e.preventDefault();
              estadoActual = this.dataset.estado;
              tabs.forEach(function (t) { t.classList.remove('activa'); });
              this.classList.add('activa');
              construirUrlYBuscar();
            });
          });

          limpiar.addEventListener('click', function (e) {
            e.preventDefault();
            inputBusqueda.value = '';
            selectTipo.value = '';
            estadoActual = '';
            tabs.forEach(function (t) { t.classList.remove('activa'); });
            tabs[0].classList.add('activa'); // "Todas"
            construirUrlYBuscar();
          });

          // Los links de paginación se recrean cada vez que se reemplaza el HTML,
          // así que "escuchamos" los clics en el contenedor padre (delegación de eventos)
          // en vez de engancharlos uno por uno.
          wrapper.addEventListener('click', function (e) {
            const link = e.target.closest('a.pagina-link');
            if (link) {
              e.preventDefault();
              buscar(link.getAttribute('href'));
            }
          });
        })();
      </script>
    </div>
</div>
@endsection