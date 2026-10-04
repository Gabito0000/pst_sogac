@extends('layouts.plantilla_admin')

@section('title', 'Panel Estadístico')

@section('content')

    @php
        $resumen = $metricas['resumen'];
        $ritmo = $metricas['ritmo'];
        $tiempos = $metricas['tiempos'];
        $antiguedad = $metricas['antiguedad'];
        $serie = $metricas['serie'];
        $documentacion = $metricas['documentacion'];
        $primeraGestion = $metricas['primera_gestion'];

        // Porcentajes del donut, calculados sobre el total real (no sobre un maximo fijo).
        $total = max(1, $resumen['total']);
        $pctPendiente = round($resumen['pendientes'] / $total * 100, 1);
        $pctAprobada = round($resumen['aprobadas'] / $total * 100, 1);
        $pctRechazada = round($resumen['rechazadas'] / $total * 100, 1);

        $cortePendiente = $pctPendiente;
        $corteAprobada = $cortePendiente + $pctAprobada;
        $corteRechazada = $corteAprobada + $pctRechazada;

        $gradienteDona = "conic-gradient("
            ."#d69a00 0% {$cortePendiente}%,"
            ."#22a35a {$cortePendiente}% {$corteAprobada}%,"
            ."#e11d2e {$corteAprobada}% {$corteRechazada}%,"
            ."#9a9a9a {$corteRechazada}% 100%)";

        // Geometria del grafico de lineas (SVG generado en el servidor, sin librerias).
        $anchoSvg = 1000;
        $altoSvg = 260;
        $margenIzq = 38;
        $margenDer = 12;
        $margenSup = 14;
        $margenInf = 28;
        $anchoUtil = $anchoSvg - $margenIzq - $margenDer;
        $altoUtil = $altoSvg - $margenSup - $margenInf;

        $maximoSerie = 1;
        foreach ($serie as $punto) {
            $maximoSerie = max($maximoSerie, $punto['creadas'], $punto['resueltas']);
        }
        // Redondeamos el tope a un numero "limpio" para que las etiquetas del eje se entiendan.
        $topoEje = $maximoSerie <= 5 ? $maximoSerie : (int) (ceil($maximoSerie / 5) * 5);

        $cantidadPuntos = max(1, count($serie));
        $pasoX = $cantidadPuntos > 1 ? $anchoUtil / ($cantidadPuntos - 1) : 0;

        $calcularX = fn (int $indice) => $margenIzq + $indice * $pasoX;
        $calcularY = fn (int $valor) => $margenSup + (1 - $valor / $topoEje) * $altoUtil;

        $lineaCreadas = [];
        $lineaResueltas = [];
        foreach ($serie as $indice => $punto) {
            $x = round($calcularX($indice), 2);
            $lineaCreadas[] = $x . ',' . round($calcularY($punto['creadas']), 2);
            $lineaResueltas[] = $x . ',' . round($calcularY($punto['resueltas']), 2);
        }

        $baseGrafico = $calcularY(0);
        $construirArea = function (string $linea) use ($baseGrafico) {
            $coordenadas = explode(' ', $linea);
            $primera = $coordenadas[0];
            $ultima = $coordenadas[count($coordenadas) - 1];
            $xUltima = explode(',', $ultima)[0];
            $xPrimera = explode(',', $primera)[0];

            // Recorremos la curva completa, bajamos a la base en el ultimo punto,
            // regresamos por la base hasta el primer punto y cerramos el poligono.
            return $linea . ' ' . $xUltima . ',' . $baseGrafico . ' ' . $xPrimera . ',' . $baseGrafico;
        };

        // Etiquetas del eje X: mostramos unas 6 fechas para que no se encimen.
        $pasoEtiqueta = max(1, (int) ceil($cantidadPuntos / 6));
        $etiquetasEjeX = [];
        foreach ($serie as $indice => $punto) {
            if ($indice % $pasoEtiqueta === 0 || $indice === $cantidadPuntos - 1) {
                $etiquetasEjeX[] = [
                    'x' => $calcularX($indice),
                    'texto' => \Illuminate\Support\Carbon::parse($punto['fecha'])->format('d/m'),
                ];
            }
        }

        $formatearDias = fn ($valor) => $valor === null ? '—' : rtrim(rtrim(number_format($valor, 2, ',', ''), '0'), ',');

        // Solo mostramos el aviso de calidad de datos cuando de verdad falta historial.
        $sinHistorial = $primeraGestion['sin_gestionar'];
    @endphp

    {{-- Encabezado --}}
    <section class="hero">
        <h1>Panel Estadístico de Solicitudes</h1>
        <p>Métricas del proceso completo: volumen, tiempos de atención, cumplimiento de los plazos
            definidos por cada trámite, antigüedad del backlog y rendimiento de los administradores.</p>
    </section>

    <div class="panel-toolbar">
        <div class="panel-toolbar__group">
            <span class="panel-toolbar__label">Serie temporal</span>
            @foreach ([7 => '7 días', 30 => '30 días', 90 => '90 días'] as $opcion => $etiqueta)
                <a href="{{ route('admin.estadisticas', ['dias' => $opcion]) }}"
                   class="btn btn--sm {{ $diasSerie === $opcion ? 'btn--primary' : 'btn--dark' }}">
                    {{ $etiqueta }}
                </a>
            @endforeach
        </div>

        <div class="panel-toolbar__group">
            <a href="{{ route('admin.dashboard') }}" class="btn btn--dark">Ver solicitudes</a>
            <a href="{{ route('admin.estadisticas.exportar', ['dias' => $diasSerie]) }}" class="btn btn--primary">Exportar CSV</a>
        </div>
    </div>

    @if ($resumen['total'] === 0)
        <div class="panel">
            <div class="estado-vacio">Todavía no hay solicitudes registradas en el sistema.</div>
        </div>
    @else

        {{-- ============================================================
             1. KPIs principales
             ============================================================ --}}
        <div class="kpi-grid">
            <div class="kpi">
                <div class="kpi__label">Total de solicitudes</div>
                <div class="kpi__value">{{ number_format($resumen['total'], 0, ',', '.') }}</div>
                <div class="kpi__hint">{{ $resumen['resueltas'] }} ya resueltas</div>
            </div>

            <div class="kpi kpi--pendiente">
                <div class="kpi__label">Pendientes</div>
                <div class="kpi__value">{{ number_format($resumen['pendientes'], 0, ',', '.') }}</div>
                <div class="kpi__hint">
                    {{ $antiguedad['vencidas_de_sla'] }} fuera del plazo estimado
                </div>
            </div>

            <div class="kpi kpi--aprobada">
                <div class="kpi__label">Tasa de aprobación</div>
                <div class="kpi__value">{{ rtrim(rtrim(number_format($resumen['tasa_aprobacion'], 1, ',', ''), '0'), ',') }}<small> %</small></div>
                <div class="kpi__hint">{{ number_format($resumen['aprobadas'], 0, ',', '.') }} aprobadas</div>
            </div>

            <div class="kpi kpi--rechazada">
                <div class="kpi__label">Tasa de rechazo</div>
                <div class="kpi__value">{{ rtrim(rtrim(number_format($resumen['tasa_rechazo'], 1, ',', ''), '0'), ',') }}<small> %</small></div>
                <div class="kpi__hint">{{ number_format($resumen['rechazadas'], 0, ',', '.') }} rechazadas</div>
            </div>

            <div class="kpi kpi--tiempo">
                <div class="kpi__label">Tiempo promedio de resolución</div>
                <div class="kpi__value">{{ $formatearDias($tiempos['promedio']) }}<small> días</small></div>
                <div class="kpi__hint">Mediana: {{ $formatearDias($tiempos['mediana']) }} días</div>
            </div>

            <div class="kpi kpi--tiempo">
                <div class="kpi__label">Tasa de resolución total</div>
                <div class="kpi__value">{{ rtrim(rtrim(number_format($resumen['tasa_resolucion'], 1, ',', ''), '0'), ',') }}<small> %</small></div>
                <div class="kpi__hint">
                    Pendiente más antigua: {{ $formatearDias($antiguedad['maxima_dias']) }} días
                </div>
            </div>
        </div>

        {{-- ============================================================
             2. Estado actual + ritmo diario
             ============================================================ --}}
        <div class="panel-grid">
            <div class="panel">
                <h2 class="panel__title">Distribución por estado</h2>
                <p class="panel__hint">Porcentaje de cada estado sobre el total de solicitudes.</p>

                <div class="donut-layout">
                    <div class="donut" style="background: {{ $gradienteDona }};" role="img"
                         aria-label="Distribución de solicitudes por estado">
                        <div class="donut__centro">
                            <div>
                                <strong>{{ number_format($resumen['total'], 0, ',', '.') }}</strong>
                                <span>Total</span>
                            </div>
                        </div>
                    </div>

                    <ul class="legend">
                        <li>
                            <span class="legend__dot" style="background:#d69a00"></span>
                            <span class="legend__label">Pendientes</span>
                            <span class="legend__value">{{ $pctPendiente }} %</span>
                        </li>
                        <li>
                            <span class="legend__dot" style="background:#22a35a"></span>
                            <span class="legend__label">Aprobadas</span>
                            <span class="legend__value">{{ $pctAprobada }} %</span>
                        </li>
                        <li>
                            <span class="legend__dot" style="background:#e11d2e"></span>
                            <span class="legend__label">Rechazadas</span>
                            <span class="legend__value">{{ $pctRechazada }} %</span>
                        </li>
                    </ul>
                </div>

                <div class="mini-stats" style="margin-top:22px;">
                    <div class="mini-stat">
                        <div class="mini-stat__label">Resueltas</div>
                        <div class="mini-stat__value">{{ number_format($resumen['resueltas'], 0, ',', '.') }}</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-stat__label">En curso</div>
                        <div class="mini-stat__value">{{ number_format($resumen['pendientes'], 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>

            <div class="panel">
                <h2 class="panel__title">Ritmo de hoy</h2>
                <p class="panel__hint">Entradas y salidas registradas en el día actual.</p>

                <div class="mini-stats">
                    @foreach ($ritmo as $indicador)
                        <div class="mini-stat">
                            <div class="mini-stat__label">{{ $indicador['etiqueta'] }}</div>
                            <div class="mini-stat__value"
                                 @if ($indicador['clave'] === 'balance' && $indicador['valor'] > 0) style="color: var(--red);"
                                 @elseif ($indicador['clave'] === 'balance' && $indicador['valor'] < 0) style="color: #14683a;" @endif>
                                {{ $indicador['valor'] > 0 ? '+' : '' }}{{ $indicador['valor'] }}
                            </div>
                            <div class="kpi__hint">{{ $indicador['contexto'] }}</div>
                        </div>
                    @endforeach
                </div>

                <h2 class="panel__title" style="margin-top:24px;">Tiempos de atención</h2>
                <p class="panel__hint">Días entre la creación de la solicitud y su resolución.</p>

                <div class="mini-stats">
                    <div class="mini-stat">
                        <div class="mini-stat__label">Más rápida</div>
                        <div class="mini-stat__value">{{ $formatearDias($tiempos['minimo']) }}</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-stat__label">Mediana</div>
                        <div class="mini-stat__value">{{ $formatearDias($tiempos['mediana']) }}</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-stat__label">Percentil 90</div>
                        <div class="mini-stat__value">{{ $formatearDias($tiempos['percentil_90']) }}</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-stat__label">Más lenta</div>
                        <div class="mini-stat__value">{{ $formatearDias($tiempos['maximo']) }}</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-stat__label">Primera gestión</div>
                        <div class="mini-stat__value">{{ $formatearDias($primeraGestion['promedio']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================
             3. Serie temporal creadas vs resueltas
             ============================================================ --}}
        <div class="panel" style="margin-bottom:24px;">
            <h2 class="panel__title">Solicitudes creadas frente a resueltas</h2>
            <p class="panel__hint">
                Últimos {{ $diasSerie }} días. Cuando la línea verde queda por debajo de la azul,
                el sistema está acumulando trabajo pendiente.
            </p>

            <svg viewBox="0 0 {{ $anchoSvg }} {{ $altoSvg }}" class="linea-grafico"
                 role="img" aria-label="Serie diaria de solicitudes creadas y resueltas">
                {{-- Lineas de rejilla y escala del eje Y --}}
                @foreach (array_values(array_unique([0, (int) round($topoEje / 2), $topoEje])) as $nivel)
                    <line class="linea-grafico__eje"
                          x1="{{ $margenIzq }}" y1="{{ round($calcularY($nivel), 2) }}"
                          x2="{{ $anchoSvg - $margenDer }}" y2="{{ round($calcularY($nivel), 2) }}"></line>
                    <text class="linea-grafico__etiqueta" x="4" y="{{ round($calcularY($nivel) + 3, 2) }}">{{ $nivel }}</text>
                @endforeach

                {{-- Areas y lineas --}}
                <polygon class="linea-grafico__area-creadas" points="{{ $construirArea(implode(' ', $lineaCreadas)) }}"></polygon>
                <polygon class="linea-grafico__area-resueltas" points="{{ $construirArea(implode(' ', $lineaResueltas)) }}"></polygon>
                <polyline class="linea-grafico__linea linea-grafico__linea--creadas" points="{{ implode(' ', $lineaCreadas) }}"></polyline>
                <polyline class="linea-grafico__linea linea-grafico__linea--resueltas" points="{{ implode(' ', $lineaResueltas) }}"></polyline>

                {{-- Fechas --}}
                @foreach ($etiquetasEjeX as $etiqueta)
                    <text class="linea-grafico__etiqueta" x="{{ round($etiqueta['x'], 2) }}" y="{{ $altoSvg - 8 }}"
                          text-anchor="middle">{{ $etiqueta['texto'] }}</text>
                @endforeach
            </svg>

            <div class="grafico-leyenda">
                <span><i style="background:#2f6fed"></i> Creadas</span>
                <span><i style="background:#22a35a"></i> Resueltas</span>
                <span style="color: var(--gray-400);">Tope del eje: {{ $topoEje }} por día</span>
            </div>
        </div>

        {{-- ============================================================
             4. Salud del backlog
             ============================================================ --}}
        <div class="panel-grid">
            <div class="panel">
                <h2 class="panel__title">Antigüedad de las pendientes</h2>
                <p class="panel__hint">
                    {{ $antiguedad['total'] }} solicitudes sin resolver, con un promedio de
                    {{ $formatearDias($antiguedad['promedio_dias']) }} días en espera.
                </p>

                <div class="barras">
                    @foreach ($antiguedad['rangos'] as $rango)
                        <div class="barra__fila">
                            <div class="barra__cabecera">
                                <span class="barra__nombre">{{ $rango['etiqueta'] }}</span>
                                <span class="barra__dato">{{ $rango['cantidad'] }} · {{ $rango['porcentaje'] }} %</span>
                            </div>
                            <div class="barra__pista">
                                <div class="barra__relleno barra__relleno--ambar" style="width: {{ $rango['porcentaje'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mini-stats" style="margin-top:20px;">
                    <div class="mini-stat">
                        <div class="mini-stat__label">Fuera de plazo</div>
                        <div class="mini-stat__value" style="color: var(--red);">{{ $antiguedad['vencidas_de_sla'] }}</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-stat__label">Más antigua</div>
                        <div class="mini-stat__value">{{ $formatearDias($antiguedad['maxima_dias']) }}</div>
                    </div>
                </div>
            </div>

            <div class="panel">
                <h2 class="panel__title">Cobertura documental</h2>
                <p class="panel__hint">Adjuntos registrados por el proceso de solicitudes.</p>

                <div class="mini-stats">
                    <div class="mini-stat">
                        <div class="mini-stat__label">Documentos subidos</div>
                        <div class="mini-stat__value">{{ number_format($documentacion['total_documentos'], 0, ',', '.') }}</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-stat__label">Promedio por solicitud</div>
                        <div class="mini-stat__value">{{ $formatearDias($documentacion['promedio_adjuntos']) }}</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-stat__label">Con adjuntos</div>
                        <div class="mini-stat__value">{{ number_format($documentacion['solicitudes_con_adjuntos'], 0, ',', '.') }}</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-stat__label">Sin adjuntos</div>
                        <div class="mini-stat__value" style="color: {{ $documentacion['solicitudes_sin_adjuntos'] > 0 ? 'var(--red)' : 'inherit' }};">
                            {{ number_format($documentacion['solicitudes_sin_adjuntos'], 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                @if ($metricas['flujo']->isNotEmpty())
                    <h2 class="panel__title" style="margin-top:24px;">Flujo de estados</h2>
                    <p class="panel__hint">Transiciones registradas en el historial de auditoría.</p>

                    <div class="tabla-metricas__wrap">
                        <table class="tabla-metricas">
                            <thead>
                            <tr>
                                <th>Transición</th>
                                <th class="num">Veces</th>
                                <th class="num">%</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($metricas['flujo'] as $flujo)
                                <tr>
                                    <td>
                                        <span style="text-transform: capitalize;">{{ $flujo['anterior'] }}</span>
                                        <span style="color: var(--gray-400);"> &rarr; </span>
                                        <span style="text-transform: capitalize; font-weight: 700;">{{ $flujo['nuevo'] }}</span>
                                    </td>
                                    <td class="num fuerte">{{ $flujo['total'] }}</td>
                                    <td class="num">{{ $flujo['participacion'] }} %</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- ============================================================
             5. Por tipo de trámite
             ============================================================ --}}
        <div class="panel" style="margin-bottom:24px;">
            <h2 class="panel__title">Rendimiento por tipo de trámite</h2>
            <p class="panel__hint">Volumen, participación en el total y tasa de aprobación de cada trámite.</p>

            <div class="tabla-metricas__wrap">
                <table class="tabla-metricas">
                    <thead>
                    <tr>
                        <th>Tipo de trámite</th>
                        <th class="num">Total</th>
                        <th>Participación</th>
                        <th class="num">Pendientes</th>
                        <th class="num">Aprobadas</th>
                        <th class="num">Rechazadas</th>
                        <th class="num">Aprobación</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($metricas['por_tipo'] as $tipo)
                        <tr>
                            <td class="fuerte">
                                {{ $tipo['nombre'] }}
                                @if ($tipo['estado_tipo'] !== 'activo')
                                    <span class="chip chip--neutro">{{ $tipo['estado_tipo'] }}</span>
                                @endif
                            </td>
                            <td class="num fuerte">{{ $tipo['total'] }}</td>
                            <td style="min-width:150px;">
                                <div class="barra__cabecera">
                                    <span class="barra__dato">{{ $tipo['participacion'] }} %</span>
                                </div>
                                <div class="barra__pista">
                                    <div class="barra__relleno barra__relleno--azul" style="width: {{ $tipo['participacion'] }}%"></div>
                                </div>
                            </td>
                            <td class="num">{{ $tipo['pendientes'] }}</td>
                            <td class="num" style="color:#14683a; font-weight:600;">{{ $tipo['aprobadas'] }}</td>
                            <td class="num" style="color:var(--red-dark); font-weight:600;">{{ $tipo['rechazadas'] }}</td>
                            <td class="num fuerte">{{ $tipo['tasa_aprobacion'] }} %</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ============================================================
             6. Cumplimiento del tiempo estimado (SLA)
             ============================================================ --}}
        <div class="panel" style="margin-bottom:24px;">
            <h2 class="panel__title">Cumplimiento del tiempo estimado (SLA)</h2>
            <p class="panel__hint">
                Compara los días reales de resolución contra el plazo configurado en cada trámite.
                Solo se miden las solicitudes que tienen historial de estados.
            </p>

            @if ($metricas['sla_por_tipo']->isEmpty())
                <div class="estado-vacio">No hay resoluciones con historial para calcular el cumplimiento.</div>
            @else
                <div class="tabla-metricas__wrap">
                    <table class="tabla-metricas">
                        <thead>
                        <tr>
                            <th>Tipo de trámite</th>
                            <th class="num">Plazo</th>
                            <th class="num">Promedio real</th>
                            <th class="num">Mediana real</th>
                            <th class="num">En plazo</th>
                            <th class="num">Fuera de plazo</th>
                            <th>Cumplimiento</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($metricas['sla_por_tipo'] as $sla)
                            @php
                                $cumplimiento = $sla['porcentaje_cumplimiento'];
                                $claseCumplimiento = $cumplimiento === null
                                    ? 'barra__relleno--ambar'
                                    : ($cumplimiento >= 80 ? 'barra__relleno--verde' : ($cumplimiento >= 50 ? 'barra__relleno--ambar' : 'barra__relleno--rojo'));
                                $chip = $cumplimiento === null
                                    ? 'chip--neutro'
                                    : ($cumplimiento >= 80 ? 'chip--ok' : ($cumplimiento >= 50 ? 'chip--warn' : 'chip--error'));
                                $chipTexto = $cumplimiento === null ? 'sin plazo' : ($cumplimiento >= 80 ? 'en objetivo' : ($cumplimiento >= 50 ? 'en riesgo' : 'fuera de objetivo'));
                            @endphp
                            <tr>
                                <td class="fuerte">{{ $sla['nombre'] }}</td>
                                <td class="num">{{ $sla['tiempo_estimado_dias'] !== null ? $sla['tiempo_estimado_dias'] . ' d' : '—' }}</td>
                                <td class="num fuerte">{{ $formatearDias($sla['promedio_dias']) }} d</td>
                                <td class="num">{{ $formatearDias($sla['mediana_dias']) }} d</td>
                                <td class="num" style="color:#14683a; font-weight:600;">{{ $sla['dentro_de_plazo'] ?? '—' }}</td>
                                <td class="num" style="color:var(--red-dark); font-weight:600;">{{ $sla['fuera_de_plazo'] ?? '—' }}</td>
                                <td style="min-width:170px;">
                                    <div class="barra__cabecera">
                                        <span class="chip {{ $chip }}">{{ $chipTexto }}</span>
                                        <span class="barra__dato">{{ $cumplimiento !== null ? $cumplimiento . ' %' : '' }}</span>
                                    </div>
                                    <div class="barra__pista">
                                        <div class="barra__relleno {{ $claseCumplimiento }}" style="width: {{ $cumplimiento ?? 0 }}%"></div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- ============================================================
             7. Rendimiento por administrador
             ============================================================ --}}
        <div class="panel-grid">
            <div class="panel">
                <h2 class="panel__title">Productividad por administrador</h2>
                <p class="panel__hint">Resoluciones registradas en el historial de estados.</p>

                @if ($metricas['por_responsable']->isEmpty())
                    <div class="estado-vacio">Todavía no se han registrado resoluciones con responsable.</div>
                @else
                    <div class="tabla-metricas__wrap">
                        <table class="tabla-metricas">
                            <thead>
                            <tr>
                                <th>Administrador</th>
                                <th class="num">Resoluciones</th>
                                <th class="num">Aprobadas</th>
                                <th class="num">Rechazadas</th>
                                <th class="num">Aprobación</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($metricas['por_responsable'] as $responsable)
                                <tr>
                                    <td class="fuerte">{{ $responsable['nombre'] }}</td>
                                    <td class="num fuerte">{{ $responsable['total'] }}</td>
                                    <td class="num" style="color:#14683a; font-weight:600;">{{ $responsable['aprobadas'] }}</td>
                                    <td class="num" style="color:var(--red-dark); font-weight:600;">{{ $responsable['rechazadas'] }}</td>
                                    <td class="num">{{ $responsable['tasa_aprobacion'] }} %</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="panel">
                <h2 class="panel__title">Estudiantes con más solicitudes</h2>
                <p class="panel__hint">Estudiantes que más usan el sistema, de mayor a menor.</p>

                @if ($metricas['ranking_estudiantes']->isEmpty())
                    <div class="estado-vacio">No hay solicitudes registradas.</div>
                @else
                    @php $maximoRanking = max(1, $metricas['ranking_estudiantes']->max('total')); @endphp
                    <div class="barras">
                        @foreach ($metricas['ranking_estudiantes'] as $estudiante)
                            <div class="barra__fila">
                                <div class="barra__cabecera">
                                    <span class="barra__nombre">{{ $estudiante['nombre'] }}</span>
                                    <span class="barra__dato">{{ $estudiante['total'] }}</span>
                                </div>
                                <div class="barra__pista">
                                    <div class="barra__relleno barra__relleno--azul"
                                         style="width: {{ round($estudiante['total'] / $maximoRanking * 100, 1) }}%"></div>
                                </div>
                                <div class="kpi__hint">{{ $estudiante['documento'] }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- ============================================================
             8. Segmentos: tipo de documento y periodo académico
             ============================================================ --}}
        <div class="panel-grid">
            <div class="panel">
                <h2 class="panel__title">Por tipo de documento</h2>
                <p class="panel__hint">Qué documento de identidad usan los estudiantes que más solicitan.</p>

                @if ($metricas['por_tipo_documento']->isEmpty())
                    <div class="estado-vacio">Sin datos.</div>
                @else
                    @php $maximoDocumento = max(1, $metricas['por_tipo_documento']->max('total')); @endphp
                    <div class="barras">
                        @foreach ($metricas['por_tipo_documento'] as $documento)
                            <div class="barra__fila">
                                <div class="barra__cabecera">
                                    <span class="barra__nombre">{{ $documento['abreviatura'] }} — {{ $documento['nombre'] }}</span>
                                    <span class="barra__dato">{{ $documento['total'] }} · {{ $documento['participacion'] }} %</span>
                                </div>
                                <div class="barra__pista">
                                    <div class="barra__relleno barra__relleno--verde"
                                         style="width: {{ round($documento['total'] / $maximoDocumento * 100, 1) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="panel">
                <h2 class="panel__title">Por periodo académico</h2>
                <p class="panel__hint">Volumen de solicitudes en cada lapso.</p>

                @if ($metricas['por_lapso']->isEmpty())
                    <div class="estado-vacio">Sin datos.</div>
                @else
                    @php $maximoLapso = max(1, $metricas['por_lapso']->max('total')); @endphp
                    <div class="barras">
                        @foreach ($metricas['por_lapso'] as $lapso)
                            <div class="barra__fila">
                                <div class="barra__cabecera">
                                    <span class="barra__nombre">
                                        {{ $lapso['lapso'] }}
                                        @if ($lapso['estado_lapso'] === 'activo')
                                            <span class="chip chip--ok">activo</span>
                                        @endif
                                    </span>
                                    <span class="barra__dato">{{ $lapso['total'] }} · {{ $lapso['pendientes'] }} pend.</span>
                                </div>
                                <div class="barra__pista">
                                    <div class="barra__relleno"
                                         style="width: {{ round($lapso['total'] / $maximoLapso * 100, 1) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- ============================================================
             9. Calidad de los datos que sostienen las métricas
             ============================================================ --}}
        @if ($sinHistorial > 0)
            <div class="nota-datos">
                <strong>Nota sobre la precisión de las métricas:</strong>
                {{ number_format($sinHistorial, 0, ',', '.') }} de las
                {{ number_format($resumen['total'], 0, ',', '.') }} solicitudes no tienen registro en el historial de estados.
                Por eso los tiempos de resolución y el cumplimiento de plazos se calculan sobre
                {{ number_format($tiempos['muestra'], 0, ',', '.') }} solicitudes con historial, y no sobre el total.
                Para que el panel refleje el 100 % del proceso, hay que guardar el historial al crear la solicitud
                y registrar la fecha de resolución al aprobarla o rechazarla.
            </div>
        @endif

    @endif

@endsection