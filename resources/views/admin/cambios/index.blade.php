@extends('layouts.plantilla_admin')

@section('title', 'Historial de cambios')

@section('content')
    @php
        use App\Models\HistorialCambio;

        // El enlace de exportación lleva solo los filtros que están puestos:
        // pasar los vacíos ensucia la URL con q=&entidad=&accion=...
        $filtrosActivos = array_filter(
            $filtros,
            fn ($valor) => $valor !== null && $valor !== '',
        );
    @endphp

    @include('partials.miga', ['miga' => [['texto' => 'Administración'], ['texto' => 'Historial de cambios']]])

    @include('partials.avisos')

    <div class="pagina-head">
        <div>
            <h1 class="pagina-head__titulo">Historial de cambios</h1>
            <p class="pagina-head__desc">
                Bitácora de todo lo que se ha modificado en el sistema: quién lo hizo, cuándo y qué valor
                tenía cada campo antes y después. Se escribe sola con cada alta, edición o baja, y no se
                puede editar ni borrar.
            </p>
        </div>

        <div class="pagina-head__acciones">
            <a href="{{ route('admin.estadisticas') }}" class="btn btn--dark">Ver estadísticas</a>
            <a href="{{ route('admin.cambios.exportar', $filtrosActivos) }}" class="btn btn--primary">Exportar CSV</a>
        </div>
    </div>

    <div class="mini-stats" style="margin-bottom: 24px;">
        <div class="mini-stat">
            <div class="mini-stat__label">Cambios registrados</div>
            <div class="mini-stat__value">{{ $resumen['total'] }}</div>
        </div>
        <div class="mini-stat">
            <div class="mini-stat__label">Altas</div>
            <div class="mini-stat__value" style="color: #14683a;">{{ $resumen['creo'] }}</div>
        </div>
        <div class="mini-stat">
            <div class="mini-stat__label">Ediciones</div>
            <div class="mini-stat__value">{{ $resumen['actualizo'] }}</div>
        </div>
        <div class="mini-stat">
            <div class="mini-stat__label">Bajas</div>
            <div class="mini-stat__value" style="color: var(--red-dark);">{{ $resumen['elimino'] }}</div>
        </div>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('admin.cambios.index') }}" class="toolbar">
            <div class="filtros">
                <input type="text"
                       name="q"
                       value="{{ $filtros['q'] }}"
                       placeholder="Buscar por resumen, autor o dirección IP…"
                       aria-label="Buscar en la bitácora">

                <select name="entidad" aria-label="Filtrar por tipo de registro">
                    <option value="">Todo lo que se audita</option>
                    @foreach ($entidades as $entidad)
                        <option value="{{ $entidad }}" @selected($filtros['entidad'] === $entidad)>
                            {{ HistorialCambio::etiquetaEntidad($entidad, true) }}
                        </option>
                    @endforeach
                </select>

                <select name="accion" aria-label="Filtrar por acción">
                    <option value="">Altas, ediciones y bajas</option>
                    @foreach (HistorialCambio::ACCIONES as $clave => $accion)
                        <option value="{{ $clave }}" @selected($filtros['accion'] === $clave)>{{ $accion['etiqueta'] }}</option>
                    @endforeach
                </select>

                <select name="autor" aria-label="Filtrar por autor">
                    <option value="">Cualquier autor</option>
                    @foreach ($autores as $autor)
                        <option value="{{ $autor->usu_id }}" @selected($filtros['autor'] === $autor->usu_id)>
                            {{ $autor->nombre_completo }}
                        </option>
                    @endforeach
                </select>

                <label class="filtros__rango">
                    <span>Desde</span>
                    <input type="date" name="desde" value="{{ $filtros['desde'] }}">
                </label>

                <label class="filtros__rango">
                    <span>Hasta</span>
                    <input type="date" name="hasta" value="{{ $filtros['hasta'] }}">
                </label>
            </div>

            <div class="toolbar__grupo">
                <button type="submit" class="btn btn--primary btn--sm">Filtrar</button>

                @if ($filtros['q'] !== '' || $filtros['entidad'] !== '' || $filtros['accion'] !== '' || $filtros['autor'] || $filtros['desde'] || $filtros['hasta'])
                    <a href="{{ route('admin.cambios.index') }}" class="btn btn--ghost btn--sm">Limpiar</a>
                @endif
            </div>
        </form>

        @if ($rangoInvertido)
            <div class="alert alert--warning">
                El rango de fechas está al revés: la fecha «desde» es posterior a la «hasta».
            </div>
        @endif

        @if ($cambios->isEmpty())
            @if ($filtros['q'] !== '' || $filtros['entidad'] !== '' || $filtros['accion'] !== '' || $filtros['autor'])
                <p class="estado-vacio">
                    Ningún cambio coincide con el filtro.
                    <br>
                    <a href="{{ route('admin.cambios.index') }}">Quitar los filtros</a> para ver toda la bitácora.
                </p>
            @else
                <p class="estado-vacio">
                    Todavía no hay cambios registrados.
                    <br>
                    En cuanto se cree o modifique un trámite, un requisito, una pregunta frecuente o una
                    solicitud, aparecerá aquí.
                </p>
            @endif
        @else
            <p class="lista__conteo">
                {{ $cambios->total() }}
                {{ $cambios->total() === 1 ? 'cambio registrado' : 'cambios registrados' }}.
            </p>

            <div class="cambios">
                @foreach ($cambios as $cambio)
                    <article class="cambio {{ $cambio->accion_chip ? 'cambio--'.$cambio->hcm_accion : '' }}">
                        <header class="cambio__cabecera">
                            <span class="chip {{ $cambio->accion_chip }}">{{ $cambio->accion_etiqueta }}</span>

                            <strong class="cambio__resumen">{{ $cambio->hcm_resumen }}</strong>

                            <span class="cambio__entidad">
                                {{ $cambio->entidad_etiqueta }}
                                @if ($cambio->hcm_entidad_id !== null)
                                    <span style="color: var(--gray-400);">#{{ $cambio->hcm_entidad_id }}</span>
                                @endif
                            </span>

                            @if ($cambio->enlace)
                                <a href="{{ $cambio->enlace }}" class="btn btn--dark btn--sm">Ver solicitud</a>
                            @endif
                        </header>

                        @include('partials.cambios.diff', ['cambios' => $cambio->diferencias])

                        <footer class="cambio__pie">
                            <span>
                                {{ $cambio->hcm_fecha->format('d/m/Y H:i') }}
                                &middot; {{ $cambio->autor_nombre }}
                                @if ($cambio->autor && $cambio->autor->usu_rol !== 'admin')
                                    <span class="chip chip--neutro">estudiante</span>
                                @endif
                            </span>

                            @if ($cambio->hcm_ruta)
                                <span title="Pantalla desde la que se hizo el cambio">
                                    {{ $cambio->hcm_ruta }}
                                </span>
                            @endif

                            @if ($cambio->hcm_ip)
                                <span>{{ $cambio->hcm_ip }}</span>
                            @endif
                        </footer>
                    </article>
                @endforeach
            </div>

            <div style="margin-top: 20px;">
                {{ $cambios->links('vendor.pagination.custom', ['etiqueta' => 'cambios']) }}
            </div>
        @endif
    </div>
@endsection