<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Calcula las métricas del panel estadístico de solicitudes.
 *
 * Todas las métricas se resuelven con agregaciones en base de datos (COUNT, GROUP BY,
 * MIN) en vez de traer las solicitudes a PHP, para que el panel siga respondiendo
 * rápido aunque la tabla tenga decenas de miles de filas.
 */
class SolicitudesEstadisticasService
{
    public const ESTADO_PENDIENTE = 'pendiente';

    public const ESTADO_APROBADA = 'aprobada';

    public const ESTADO_RECHAZADA = 'rechazada';

    /** Estados finales: una solicitud que está en alguno de ellos ya fue resuelta. */
    public const ESTADOS_TERMINALES = [
        self::ESTADO_APROBADA,
        self::ESTADO_RECHAZADA,
    ];

    /** Rangos (en días) que se usan para medir la antigüedad del backlog pendiente. */
    private const RANGOS_ANTIGUEDAD = [
        ['etiqueta' => '0 a 2 días', 'minimo' => 0, 'maximo' => 2],
        ['etiqueta' => '3 a 6 días', 'minimo' => 3, 'maximo' => 6],
        ['etiqueta' => '7 a 14 días', 'minimo' => 7, 'maximo' => 14],
        ['etiqueta' => '15 a 30 días', 'minimo' => 15, 'maximo' => 30],
        ['etiqueta' => 'Más de 30 días', 'minimo' => 31, 'maximo' => PHP_INT_MAX],
    ];

    /**
     * Tarjeta principal: totales absolutos y tasas porcentuales del proceso completo.
     *
     * @return array{total:int, pendientes:int, aprobadas:int, rechazadas:int, resueltas:int, tasa_resolucion:float, tasa_aprobacion:float, tasa_rechazo:float}
     */
    public function resumenGlobal(): array
    {
        $conteoPorEstado = DB::table('solicitudes')
            ->join('estado_solicitudes', 'estado_solicitudes.eso_id', '=', 'solicitudes.sol_eso_id')
            ->selectRaw('estado_solicitudes.eso_nombre_estado, count(*) as total')
            ->groupBy('estado_solicitudes.eso_nombre_estado')
            ->pluck('total', 'eso_nombre_estado');

        $total = (int) $conteoPorEstado->sum();
        $pendientes = (int) $conteoPorEstado->get(self::ESTADO_PENDIENTE, 0);
        $aprobadas = (int) $conteoPorEstado->get(self::ESTADO_APROBADA, 0);
        $rechazadas = (int) $conteoPorEstado->get(self::ESTADO_RECHAZADA, 0);
        $resueltas = $aprobadas + $rechazadas;

        return [
            'total' => $total,
            'pendientes' => $pendientes,
            'aprobadas' => $aprobadas,
            'rechazadas' => $rechazadas,
            'resueltas' => $resueltas,
            'tasa_resolucion' => $this->porcentaje($resueltas, $total),
            'tasa_aprobacion' => $this->porcentaje($aprobadas, $resueltas),
            'tasa_rechazo' => $this->porcentaje($rechazadas, $resueltas),
        ];
    }

    /**
     * Indicadores de ritmo: creación y resolución por día, semana y mes.
     *
     * Sirven para responder "¿el sistema se está haciendo más rápido o más lento?".
     *
     * @return array<int, array<string, mixed>>
     */
    public function indicadoresDeRitmo(): array
    {
        $hoy = CarbonImmutable::today();

        $creadasHoy = $this->contarSolicitudesCreadasDesde($hoy);
        $creadasSemana = $this->contarSolicitudesCreadasDesde($hoy->subDays(6));
        $creadasMes = $this->contarSolicitudesCreadasDesde($hoy->subDays(29));
        $resueltasHoy = $this->contarSolicitudesResueltasDesde($hoy);
        $resueltasSemana = $this->contarSolicitudesResueltasDesde($hoy->subDays(6));
        $resueltasMes = $this->contarSolicitudesResueltasDesde($hoy->subDays(29));

        return [
            [
                'clave' => 'creadas_hoy',
                'etiqueta' => 'Creadas hoy',
                'valor' => $creadasHoy,
                'contexto' => "{$creadasSemana} en los últimos 7 días · {$creadasMes} en los últimos 30 días",
            ],
            [
                'clave' => 'resueltas_hoy',
                'etiqueta' => 'Resueltas hoy',
                'valor' => $resueltasHoy,
                'contexto' => "{$resueltasSemana} en los últimos 7 días · {$resueltasMes} en los últimos 30 días",
            ],
            [
                'clave' => 'balance',
                'etiqueta' => 'Balance (creadas − resueltas)',
                'valor' => $creadasHoy - $resueltasHoy,
                'contexto' => 'Un balance positivo significa que se están acumulando pendientes',
            ],
        ];
    }

    /**
     * Tiempos de atención: días entre la creación y la resolución.
     *
     * La fecha de resolución se toma del historial de estados (primera transición a un
     * estado final), porque la columna solicitudes.sol_fecha_resolucion todavía no se
     * está escribiendo en el flujo de aprobación/rechazo.
     *
     * @return array{promedio:float|null, mediana:float|null, minimo:float|null, maximo:float|null, percentil_90:float|null, muestra:int}
     */
    public function tiemposDeResolucion(): array
    {
        $dias = $this->duracionesDeResolucionEnDias();

        if ($dias->isEmpty()) {
            return [
                'promedio' => null,
                'mediana' => null,
                'minimo' => null,
                'maximo' => null,
                'percentil_90' => null,
                'muestra' => 0,
            ];
        }

        $ordenadas = $dias->sort()->values()->all();

        return [
            'promedio' => round(array_sum($ordenadas) / count($ordenadas), 2),
            'mediana' => $this->mediana($ordenadas),
            'minimo' => round($ordenadas[0], 2),
            'maximo' => round($ordenadas[count($ordenadas) - 1], 2),
            'percentil_90' => $this->percentil($ordenadas, 0.90),
            'muestra' => count($ordenadas),
        ];
    }

    /**
     * Cumplimiento del tiempo estimado por trámite (SLA).
     *
     * Se compara el tiempo real de resolución contra tipo_solicitudes.tsi_tiempo_estimado_dias.
     * Las solicitudes resueltas sin historial no entran en el cálculo, por eso se reporta
     * la cobertura (muestra / total del tipo).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function cumplimientoPorTipo(): Collection
    {
        $resoluciones = $this->consultaResoluciones()->get();

        // Totales por tipo, para poder calcular la cobertura del SLA.
        $totalesPorTipo = DB::table('solicitudes')
            ->selectRaw('sol_tsi_id, count(*) as total')
            ->groupBy('sol_tsi_id')
            ->pluck('total', 'sol_tsi_id');

        return $resoluciones
            ->groupBy('tsi_id')
            ->map(function (Collection $filas, $tsiId) use ($totalesPorTipo) {
                $primera = $filas->first();

                $duraciones = $filas
                    ->map(fn ($fila) => $this->diasEntre($fila->sol_fecha_creacion, $fila->fecha_resolucion))
                    ->filter(fn (float $dias) => $dias >= 0)
                    ->sort()
                    ->values()
                    ->all();

                $estimado = $primera->tsi_tiempo_estimado_dias;

                $dentroDePlazo = $estimado !== null
                    ? count(array_filter($duraciones, fn (float $dias) => $dias <= (float) $estimado))
                    : null;

                return [
                    'tsi_id' => (int) $tsiId,
                    'nombre' => $primera->tsi_nombre_tipo,
                    'tiempo_estimado_dias' => $estimado !== null ? (int) $estimado : null,
                    'promedio_dias' => $duraciones !== [] ? round(array_sum($duraciones) / count($duraciones), 2) : null,
                    'mediana_dias' => $this->mediana($duraciones),
                    'maximo_dias' => $duraciones !== [] ? round(max($duraciones), 2) : null,
                    'total_tipo' => (int) $totalesPorTipo->get($tsiId, count($duraciones)),
                    'muestra' => count($duraciones),
                    'dentro_de_plazo' => $dentroDePlazo,
                    'fuera_de_plazo' => $dentroDePlazo !== null ? count($duraciones) - $dentroDePlazo : null,
                    'porcentaje_cumplimiento' => $dentroDePlazo !== null
                        ? $this->porcentaje($dentroDePlazo, count($duraciones))
                        : null,
                ];
            })
            ->sortByDesc('muestra')
            ->values();
    }

    /**
     * Salud del backlog: cómo se están acumulando las solicitudes sin resolver.
     *
     * @return array{rangos: array<int, array<string, mixed>>, total:int, promedio_dias:float|null, maxima_dias:float|null, vencidas_de_sla:int}
     */
    public function antiguedadDePendientes(): array
    {
        $filas = DB::table('solicitudes as s')
            ->join('estado_solicitudes as e', 'e.eso_id', '=', 's.sol_eso_id')
            ->join('tipo_solicitudes as t', 't.tsi_id', '=', 's.sol_tsi_id')
            ->where('e.eso_nombre_estado', self::ESTADO_PENDIENTE)
            ->selectRaw('s.sol_fecha_creacion, t.tsi_tiempo_estimado_dias')
            ->get();

        $dias = $filas
            ->map(fn ($fila) => $this->diasEntre($fila->sol_fecha_creacion, CarbonImmutable::now()))
            ->filter(fn (float $valor) => $valor >= 0)
            ->values();

        $rangos = [];
        foreach (self::RANGOS_ANTIGUEDAD as $rango) {
            $cantidad = $dias->filter(fn (float $valor) => $valor >= $rango['minimo'] && $valor <= $rango['maximo'])->count();

            $rangos[] = [
                'etiqueta' => $rango['etiqueta'],
                'cantidad' => $cantidad,
                'porcentaje' => $this->porcentaje($cantidad, $dias->count()),
            ];
        }

        $ordenadas = $dias->sort()->values()->all();

        // Pendientes que ya superaron el tiempo estimado del trámite que las originó.
        $vencidas = $filas->filter(function ($fila) {
            if ($fila->tsi_tiempo_estimado_dias === null) {
                return false;
            }

            return $this->diasEntre($fila->sol_fecha_creacion, CarbonImmutable::now()) > (float) $fila->tsi_tiempo_estimado_dias;
        })->count();

        return [
            'rangos' => $rangos,
            'total' => $dias->count(),
            'promedio_dias' => $dias->isEmpty() ? null : round($dias->avg(), 2),
            'maxima_dias' => $ordenadas === [] ? null : round($ordenadas[count($ordenadas) - 1], 2),
            'vencidas_de_sla' => $vencidas,
        ];
    }

    /**
     * Serie diaria de solicitudes creadas vs resueltas, con los días sin movimiento en cero.
     *
     * @return array<int, array{fecha:string, creadas:int, resueltas:int}>
     */
    public function seriesTemporales(int $dias = 30): array
    {
        $dias = max(7, min($dias, 365));
        $desde = CarbonImmutable::today()->subDays($dias - 1);

        $expresionCreacion = $this->expresionTruncado('solicitudes.sol_fecha_creacion');
        $expresionResolucion = $this->expresionTruncado('historial_estado_solicitudes.hes_fecha_cambio');

        $creadas = $this->normalizarFechas(
            DB::table('solicitudes')
                ->where('solicitudes.sol_fecha_creacion', '>=', $desde->startOfDay())
                ->selectRaw("{$expresionCreacion} as dia, count(*) as total")
                ->groupBy('dia')
                ->pluck('total', 'dia')
        );

        $resueltas = $this->normalizarFechas(
            DB::table('historial_estado_solicitudes')
                ->join('estado_solicitudes', 'estado_solicitudes.eso_id', '=', 'historial_estado_solicitudes.hes_eso_id_nuevo')
                ->whereIn('estado_solicitudes.eso_nombre_estado', self::ESTADOS_TERMINALES)
                ->where('historial_estado_solicitudes.hes_fecha_cambio', '>=', $desde->startOfDay())
                ->selectRaw("{$expresionResolucion} as dia, count(*) as total")
                ->groupBy('dia')
                ->pluck('total', 'dia')
        );

        $serie = [];
        for ($i = 0; $i < $dias; $i++) {
            $fecha = $desde->addDays($i)->toDateString();

            $serie[] = [
                'fecha' => $fecha,
                'creadas' => (int) ($creadas[$fecha] ?? 0),
                'resueltas' => (int) ($resueltas[$fecha] ?? 0),
            ];
        }

        return $serie;
    }

    /**
     * Desglose por tipo de trámite con su participación, pendientes y tasa de aprobación.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function distribucionPorTipo(): Collection
    {
        $filas = DB::table('solicitudes as s')
            ->join('tipo_solicitudes as t', 't.tsi_id', '=', 's.sol_tsi_id')
            ->join('estado_solicitudes as e', 'e.eso_id', '=', 's.sol_eso_id')
            ->selectRaw('t.tsi_id, t.tsi_nombre_tipo, t.tsi_tiempo_estimado_dias, t.tsi_estado_tipo, e.eso_nombre_estado, count(*) as total')
            ->groupBy('t.tsi_id', 't.tsi_nombre_tipo', 't.tsi_tiempo_estimado_dias', 't.tsi_estado_tipo', 'e.eso_nombre_estado')
            ->get();

        $totalGlobal = (int) $filas->sum('total');

        return $filas
            ->groupBy('tsi_nombre_tipo')
            ->map(function (Collection $grupo, $nombre) use ($totalGlobal) {
                $porEstado = $grupo->pluck('total', 'eso_nombre_estado');
                $primera = $grupo->first();

                $total = (int) $grupo->sum('total');
                $aprobadas = (int) $porEstado->get(self::ESTADO_APROBADA, 0);
                $rechazadas = (int) $porEstado->get(self::ESTADO_RECHAZADA, 0);
                $resueltas = $aprobadas + $rechazadas;

                return [
                    'tsi_id' => (int) $primera->tsi_id,
                    'nombre' => $nombre,
                    'estado_tipo' => $primera->tsi_estado_tipo,
                    'tiempo_estimado_dias' => $primera->tsi_tiempo_estimado_dias !== null
                        ? (int) $primera->tsi_tiempo_estimado_dias
                        : null,
                    'total' => $total,
                    'pendientes' => (int) $porEstado->get(self::ESTADO_PENDIENTE, 0),
                    'aprobadas' => $aprobadas,
                    'rechazadas' => $rechazadas,
                    'participacion' => $this->porcentaje($total, $totalGlobal),
                    'tasa_aprobacion' => $this->porcentaje($aprobadas, $resueltas),
                ];
            })
            ->sortByDesc('total')
            ->values();
    }

    /**
     * Productividad por administrador responsable de resolver.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function rendimientoPorResponsable(): Collection
    {
        $filas = DB::table('historial_estado_solicitudes as h')
            ->join('usuarios as u', 'u.usu_id', '=', 'h.hes_usu_id_responsable')
            ->join('estado_solicitudes as e', 'e.eso_id', '=', 'h.hes_eso_id_nuevo')
            ->selectRaw('u.usu_id, u.usu_primer_nombre, u.usu_primer_apellido, e.eso_nombre_estado, count(*) as total')
            ->groupBy('u.usu_id', 'u.usu_primer_nombre', 'u.usu_primer_apellido', 'e.eso_nombre_estado')
            ->get();

        return $filas
            ->groupBy('usu_id')
            ->map(function (Collection $grupo, $usuId) {
                $porEstado = $grupo->pluck('total', 'eso_nombre_estado');
                $primera = $grupo->first();

                $total = (int) $grupo->sum('total');
                $aprobadas = (int) $porEstado->get(self::ESTADO_APROBADA, 0);
                $rechazadas = (int) $porEstado->get(self::ESTADO_RECHAZADA, 0);

                return [
                    'usu_id' => (int) $usuId,
                    'nombre' => trim("{$primera->usu_primer_nombre} {$primera->usu_primer_apellido}"),
                    'total' => $total,
                    'aprobadas' => $aprobadas,
                    'rechazadas' => $rechazadas,
                    'tasa_aprobacion' => $this->porcentaje($aprobadas, $total),
                ];
            })
            ->sortByDesc('total')
            ->values();
    }

    /**
     * Solicitudes por tipo de documento del estudiante.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function distribucionPorTipoDocumento(): Collection
    {
        $filas = DB::table('solicitudes as s')
            ->join('usuarios as u', 'u.usu_id', '=', 's.sol_usu_id')
            ->leftJoin('tipo_documentos as d', 'd.tdo_id', '=', 'u.usu_tdo_id')
            ->selectRaw("coalesce(d.tdo_abreviatura, '—') as abreviatura, coalesce(d.tdo_nombre_documento, 'Sin tipo de documento') as nombre, count(*) as total")
            ->groupBy('d.tdo_abreviatura', 'd.tdo_nombre_documento')
            ->get();

        $totalGlobal = (int) $filas->sum('total');

        return $filas
            ->map(fn ($fila) => [
                'abreviatura' => $fila->abreviatura,
                'nombre' => $fila->nombre,
                'total' => (int) $fila->total,
                'participacion' => $this->porcentaje((int) $fila->total, $totalGlobal),
            ])
            ->sortByDesc('total')
            ->values();
    }

    /**
     * Solicitudes por periodo académico.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function distribucionPorLapso(): Collection
    {
        $filas = DB::table('solicitudes as s')
            ->leftJoin('lapso_academicos as l', 'l.lac_id', '=', 's.sol_lac_id')
            ->join('estado_solicitudes as e', 'e.eso_id', '=', 's.sol_eso_id')
            ->selectRaw("coalesce(l.lac_id_lapso, 'Sin lapso') as lapso, l.lac_estado_lapso, e.eso_nombre_estado, count(*) as total")
            ->groupBy('l.lac_id_lapso', 'l.lac_estado_lapso', 'e.eso_nombre_estado')
            ->get();

        return $filas
            ->groupBy('lapso')
            ->map(function (Collection $grupo, $lapso) {
                $porEstado = $grupo->pluck('total', 'eso_nombre_estado');

                return [
                    'lapso' => $lapso,
                    'estado_lapso' => $grupo->first()->lac_estado_lapso,
                    'total' => (int) $grupo->sum('total'),
                    'pendientes' => (int) $porEstado->get(self::ESTADO_PENDIENTE, 0),
                    'aprobadas' => (int) $porEstado->get(self::ESTADO_APROBADA, 0),
                    'rechazadas' => (int) $porEstado->get(self::ESTADO_RECHAZADA, 0),
                ];
            })
            ->sortByDesc('total')
            ->values();
    }

    /**
     * Estudiantes que más solicitudes han registrado.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function rankingDeEstudiantes(int $limite = 10): Collection
    {
        return DB::table('solicitudes as s')
            ->join('usuarios as u', 'u.usu_id', '=', 's.sol_usu_id')
            // El cast es necesario porque en PostgreSQL count(*) vuelve como texto y
            // ORDER BY ordenaría '10' antes que '9'.
            ->selectRaw('u.usu_id, u.usu_numero_documento, u.usu_primer_nombre, u.usu_primer_apellido, cast(count(*) as integer) as total')
            ->groupBy('u.usu_id', 'u.usu_numero_documento', 'u.usu_primer_nombre', 'u.usu_primer_apellido')
            ->orderByDesc('total')
            ->orderBy('u.usu_numero_documento')
            ->limit($limite)
            ->get()
            ->map(fn ($fila) => [
                'documento' => $fila->usu_numero_documento,
                'nombre' => trim("{$fila->usu_primer_nombre} {$fila->usu_primer_apellido}"),
                'total' => (int) $fila->total,
            ])
            ->values();
    }

    /**
     * Flujo real de transiciones entre estados (cuántas veces se pasa de un estado a otro).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function flujoDeEstados(): Collection
    {
        $filas = DB::table('historial_estado_solicitudes as h')
            ->leftJoin('estado_solicitudes as anterior', 'anterior.eso_id', '=', 'h.hes_eso_id_anterior')
            ->join('estado_solicitudes as nuevo', 'nuevo.eso_id', '=', 'h.hes_eso_id_nuevo')
            ->selectRaw("coalesce(anterior.eso_nombre_estado, 'Sin estado previo') as anterior, nuevo.eso_nombre_estado as nuevo, count(*) as total")
            ->groupBy('anterior.eso_nombre_estado', 'nuevo.eso_nombre_estado')
            ->get();

        $total = (int) $filas->sum('total');

        return $filas
            ->map(fn ($fila) => [
                'anterior' => $fila->anterior,
                'nuevo' => $fila->nuevo,
                'total' => (int) $fila->total,
                'participacion' => $this->porcentaje((int) $fila->total, $total),
            ])
            ->sortByDesc('total')
            ->values();
    }

    /**
     * Tiempo hasta la primera gestión: cuánto tarda un administrador en tocar una solicitud.
     *
     * @return array{promedio:float|null, mediana:float|null, sin_gestionar:int}
     */
    public function tiempoHastaPrimeraGestion(): array
    {
        $filas = DB::table('historial_estado_solicitudes as h')
            ->join('solicitudes as s', 's.sol_id', '=', 'h.hes_sol_id')
            ->groupBy('h.hes_sol_id', 's.sol_fecha_creacion')
            ->selectRaw('MIN(h.hes_fecha_cambio) as primera_gestion, s.sol_fecha_creacion')
            ->get();

        $dias = $filas
            ->map(fn ($fila) => $this->diasEntre($fila->sol_fecha_creacion, $fila->primera_gestion))
            ->filter(fn (float $valor) => $valor >= 0)
            ->values();

        return [
            'promedio' => $dias->isEmpty() ? null : round($dias->avg(), 2),
            'mediana' => $this->mediana($dias->all()),
            'sin_gestionar' => $this->contarSolicitudesSinHistorial(),
        ];
    }

    /**
     * Cobertura documental: solicitudes con archivos adjuntos y promedio de adjuntos.
     *
     * @return array{total_documentos:int, solicitudes_con_adjuntos:int, solicitudes_sin_adjuntos:int, promedio_adjuntos:float,total_solicitudes:int}
     */
    public function coberturaDocumental(): array
    {
        $totalSolicitudes = (int) DB::table('solicitudes')->count();
        $totalDocumentos = (int) DB::table('documentaciones')->count();
        $conAdjuntos = (int) DB::table('solicitudes as s')
            ->join('documentaciones as d', 'd.doc_sol_id', '=', 's.sol_id')
            ->distinct()
            ->count('s.sol_id');

        return [
            'total_documentos' => $totalDocumentos,
            'solicitudes_con_adjuntos' => $conAdjuntos,
            'solicitudes_sin_adjuntos' => max(0, $totalSolicitudes - $conAdjuntos),
            'promedio_adjuntos' => $totalSolicitudes > 0 ? round($totalDocumentos / $totalSolicitudes, 2) : 0.0,
            'total_solicitudes' => $totalSolicitudes,
        ];
    }

    /**
     * Único punto de entrada: arma el paquete completo de métricas para la vista.
     *
     * @return array<string, mixed>
     */
    public function metrics(int $diasSerie = 30): array
    {
        return [
            'resumen' => $this->resumenGlobal(),
            'ritmo' => $this->indicadoresDeRitmo(),
            'tiempos' => $this->tiemposDeResolucion(),
            'antiguedad' => $this->antiguedadDePendientes(),
            'serie' => $this->seriesTemporales($diasSerie),
            'por_tipo' => $this->distribucionPorTipo(),
            'sla_por_tipo' => $this->cumplimientoPorTipo(),
            'por_responsable' => $this->rendimientoPorResponsable(),
            'por_tipo_documento' => $this->distribucionPorTipoDocumento(),
            'por_lapso' => $this->distribucionPorLapso(),
            'ranking_estudiantes' => $this->rankingDeEstudiantes(),
            'flujo' => $this->flujoDeEstados(),
            'primera_gestion' => $this->tiempoHastaPrimeraGestion(),
            'documentacion' => $this->coberturaDocumental(),
        ];
    }

    /**
     * Base: una fila por solicitud resuelta con la fecha de su primera transición a un
     * estado final. El GROUP BY incluye todas las columnas proyectadas para que la
     * consulta sea válida también en PostgreSQL.
     */
    private function consultaResoluciones(): Builder
    {
        return DB::table('historial_estado_solicitudes')
            ->join('solicitudes', 'solicitudes.sol_id', '=', 'historial_estado_solicitudes.hes_sol_id')
            ->join('tipo_solicitudes', 'tipo_solicitudes.tsi_id', '=', 'solicitudes.sol_tsi_id')
            ->join('estado_solicitudes', 'estado_solicitudes.eso_id', '=', 'historial_estado_solicitudes.hes_eso_id_nuevo')
            ->whereIn('estado_solicitudes.eso_nombre_estado', self::ESTADOS_TERMINALES)
            ->groupBy(
                'historial_estado_solicitudes.hes_sol_id',
                'solicitudes.sol_fecha_creacion',
                'tipo_solicitudes.tsi_id',
                'tipo_solicitudes.tsi_nombre_tipo',
                'tipo_solicitudes.tsi_tiempo_estimado_dias',
            )
            ->selectRaw('MIN(historial_estado_solicitudes.hes_fecha_cambio) as fecha_resolucion, solicitudes.sol_fecha_creacion, tipo_solicitudes.tsi_id, tipo_solicitudes.tsi_nombre_tipo, tipo_solicitudes.tsi_tiempo_estimado_dias');
    }

    /**
     * @return Collection<int, float>
     */
    private function duracionesDeResolucionEnDias(): Collection
    {
        return $this->consultaResoluciones()->get()
            ->map(fn ($fila) => $this->diasEntre($fila->sol_fecha_creacion, $fila->fecha_resolucion))
            ->filter(fn (float $valor) => $valor >= 0)
            ->values();
    }

    private function contarSolicitudesCreadasDesde(CarbonImmutable $desde): int
    {
        return (int) DB::table('solicitudes')
            ->where('sol_fecha_creacion', '>=', $desde->startOfDay())
            ->count();
    }

    private function contarSolicitudesResueltasDesde(CarbonImmutable $desde): int
    {
        return (int) DB::table('historial_estado_solicitudes')
            ->join('estado_solicitudes', 'estado_solicitudes.eso_id', '=', 'historial_estado_solicitudes.hes_eso_id_nuevo')
            ->whereIn('estado_solicitudes.eso_nombre_estado', self::ESTADOS_TERMINALES)
            ->where('historial_estado_solicitudes.hes_fecha_cambio', '>=', $desde->startOfDay())
            ->count();
    }

    /**
     * Solicitudes que nunca pasaron por el historial (p. ej. las del seeder, que nacen ya
     * resueltas sin registro de auditoría). Son el motivo de que la muestra de tiempos
     * sea menor que el total de resueltas.
     */
    private function contarSolicitudesSinHistorial(): int
    {
        return (int) DB::table('solicitudes')
            ->whereNotIn('sol_id', DB::table('historial_estado_solicitudes')->select('hes_sol_id'))
            ->count();
    }

    /**
     * Expresión SQL para truncar una columna de timestamp a solo fecha, según el motor.
     */
    private function expresionTruncado(string $columna): string
    {
        return match (DB::connection()->getDriverName()) {
            'pgsql' => "date_trunc('day', {$columna})",
            default => "date({$columna})",
        };
    }

    /**
     * Los motores devuelven la fecha como texto ("2026-10-03" o "2026-10-03 00:00:00+00"),
     * así que nos quedamos solo con la parte de la fecha para poder compararla con Carbon.
     *
     * @param  Collection<string, mixed>  $conteo
     * @return array<string, int>
     */
    private function normalizarFechas(Collection $conteo): array
    {
        $normalizado = [];

        foreach ($conteo as $clave => $valor) {
            $normalizado[substr((string) $clave, 0, 10)] = (int) $valor;
        }

        return $normalizado;
    }

    /**
     * Días (con decimales) entre dos marcas de tiempo.
     */
    private function diasEntre(mixed $desde, mixed $hasta): float
    {
        $inicio = CarbonImmutable::parse($desde)->getTimestamp();
        $fin = CarbonImmutable::parse($hasta)->getTimestamp();

        return ($fin - $inicio) / 86400;
    }

    /**
     * Porcentaje redondeado a un decimal, evitando división por cero.
     */
    private function porcentaje(int $parte, int $total): float
    {
        return $total > 0 ? round(($parte / $total) * 100, 1) : 0.0;
    }

    /**
     * @param  array<int, float>  $valores  Lista ordenada de menor a mayor.
     */
    private function mediana(array $valores): ?float
    {
        if ($valores === []) {
            return null;
        }

        sort($valores);
        $centro = intdiv(count($valores), 2);

        $mediana = count($valores) % 2 === 0
            ? ($valores[$centro - 1] + $valores[$centro]) / 2
            : $valores[$centro];

        return round($mediana, 2);
    }

    /**
     * Percentil por interpolación lineal sobre una lista ya ordenada.
     *
     * @param  array<int, float>  $valores
     */
    private function percentil(array $valores, float $percentil): ?float
    {
        if ($valores === []) {
            return null;
        }

        $posicion = $percentil * (count($valores) - 1);
        $inferior = (int) floor($posicion);
        $superior = (int) ceil($posicion);

        if ($inferior === $superior) {
            return round($valores[$inferior], 2);
        }

        $fraccion = $posicion - $inferior;

        return round($valores[$inferior] * (1 - $fraccion) + $valores[$superior] * $fraccion, 2);
    }
}
