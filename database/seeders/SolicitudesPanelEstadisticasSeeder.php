<?php

namespace Database\Seeders;

use App\Models\Documentacion;
use App\Models\EstadoSolicitud;
use App\Models\HistorialEstadoSolicitud;
use App\Models\LapsoAcademico;
use App\Models\Solicitud;
use App\Models\TipoDocumento;
use App\Models\TipoSolicitud;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Datos de prueba pensados para el panel estadistico
 * (app/Services/SolicitudesEstadisticasService.php).
 *
 * A diferencia de SolicitudesDePruebaSeeder, este si genera el historial de estados,
 * que es de donde el panel saca los tiempos de resolucion y el cumplimiento de plazos.
 * Tambien reparte las solicitudes a lo largo de los ultimos 120 dias para que las
 * series de 7, 30 y 90 dias tengan datos, y deja pendientes en las cinco franjas
 * de antiguedad.
 *
 * Es idempotente: las solicitudes se borran y se vuelven a crear en cada ejecucion
 * (el historial y los adjuntos se van en cascada), y los catalogos se reutilizan.
 *
 * Las cifras concretas varian entre ejecuciones porque las fechas y los tiempos se
 * sortean al azar, pero la estructura si esta elegida para que todas las secciones
 * del panel tengan datos: los cinco tramos de antiguedad, los tres estados, los
 * cuatro rangos de 7, 30 y 90 dias con movimiento, y trámites dentro y fuera de plazo.
 *
 *   php artisan db:seed --class=SolicitudesPanelEstadisticasSeeder
 */
class SolicitudesPanelEstadisticasSeeder extends Seeder
{
    /** Prefijo que identifica todo lo que crea este seeder. */
    private const PREFIJO = 'SOL-DEMO-';

    /** Dias hacia atras que abarcan los datos generados. */
    private const DIAS_DE_HISTORIA = 118;

    public function run(): void
    {
        $this->command?->info('Limpiando los datos de prueba anteriores del panel...');
        $this->limpiarSolicitudesDemo();

        $catalogos = $this->crearCatalogos();
        $estudiantes = $this->crearEstudiantes($catalogos);
        $administradores = $this->crearAdministradores($catalogos);

        $resueltas = $this->crearSolicitudesResueltas($catalogos, $estudiantes, $administradores);
        $pendientes = $this->crearSolicitudesPendientes($catalogos, $estudiantes);

        $this->command?->info(sprintf(
            '%d resueltas y %d pendientes, sobre %d estudiantes y %d administradores.',
            $resueltas,
            $pendientes,
            count($estudiantes),
            count($administradores),
        ));

        $this->command?->newLine();
        $this->command?->info('Accesos de prueba (contrasena: password)');

        foreach ($administradores as $admin) {
            $this->command?->line(sprintf(
                '  admin      %-30s %s',
                $admin->usu_correo_electronico,
                $admin->usu_numero_documento,
            ));
        }

        $this->command?->line(sprintf(
            '  estudiante %-30s %s',
            $estudiantes[0]->usu_correo_electronico,
            $estudiantes[0]->usu_numero_documento,
        ));
    }

    /**
     * Borra las solicitudes creadas por este seeder. El historial y los adjuntos se
     * eliminan en cascada porque asi estan definidas las claves foraneas.
     */
    private function limpiarSolicitudesDemo(): void
    {
        DB::table('solicitudes')
            ->where('sol_id_seguimiento', 'like', self::PREFIJO.'%')
            ->delete();
    }

    /**
     * @return array<string, mixed>
     */
    private function crearCatalogos(): array
    {
        $cedula = TipoDocumento::firstOrCreate(
            ['tdo_abreviatura' => 'V'],
            ['tdo_nombre_documento' => 'Cédula de Identidad'],
        );

        $pasaporte = TipoDocumento::firstOrCreate(
            ['tdo_abreviatura' => 'E'],
            ['tdo_nombre_documento' => 'Pasaporte'],
        );

        $pendiente = EstadoSolicitud::firstOrCreate(['eso_nombre_estado' => 'pendiente']);
        $aprobada = EstadoSolicitud::firstOrCreate(['eso_nombre_estado' => 'aprobada']);
        $rechazada = EstadoSolicitud::firstOrCreate(['eso_nombre_estado' => 'rechazada']);

        // Plazos muy distintos a proposito: asi el panel muestra tramites que se
        // cumplen y tramites que se van de plazo.
        $constancia = TipoSolicitud::firstOrCreate(
            ['tsi_nombre_tipo' => 'Constancia de Estudio'],
            [
                'tsi_descripcion' => 'Certificacion de que el estudiante esta inscrito.',
                'tsi_estado_tipo' => 'activo',
                'tsi_tiempo_estimado_dias' => 1,
            ],
        );

        $revisionNota = TipoSolicitud::firstOrCreate(
            ['tsi_nombre_tipo' => 'Revisión de Nota'],
            [
                'tsi_descripcion' => 'Repeticion de una evaluacion academica.',
                'tsi_estado_tipo' => 'activo',
                'tsi_tiempo_estimado_dias' => 3,
            ],
        );

        $matricula = TipoSolicitud::firstOrCreate(
            ['tsi_nombre_tipo' => 'Matrícula Ordinaria'],
            [
                'tsi_descripcion' => 'Inscripcion en asignaturas del periodo.',
                'tsi_estado_tipo' => 'activo',
                'tsi_tiempo_estimado_dias' => 5,
            ],
        );

        $cambioCarrera = TipoSolicitud::firstOrCreate(
            ['tsi_nombre_tipo' => 'Cambio de Carrera'],
            [
                'tsi_descripcion' => 'Traslado a otra carrera de la facultad.',
                'tsi_estado_tipo' => 'activo',
                'tsi_tiempo_estimado_dias' => 7,
            ],
        );

        $suspension = TipoSolicitud::firstOrCreate(
            ['tsi_nombre_tipo' => 'Suspensión de Estudios'],
            [
                'tsi_descripcion' => 'Pausa temporal del expediente academico.',
                'tsi_estado_tipo' => 'activo',
                'tsi_tiempo_estimado_dias' => 15,
            ],
        );

        // Tramite retirado: sirve para comprobar que el panel separa los inactivos.
        $traslado = TipoSolicitud::firstOrCreate(
            ['tsi_nombre_tipo' => 'Traslado de Sede'],
            [
                'tsi_descripcion' => 'Cambio de sede, disponible solo en temporada.',
                'tsi_estado_tipo' => 'inactivo',
                'tsi_tiempo_estimado_dias' => 20,
            ],
        );

        $lapsoActual = LapsoAcademico::firstOrCreate(
            ['lac_id_lapso' => '2026-2'],
            [
                'lac_fecha_inicio' => '2026-07-01',
                'lac_fecha_cierre' => '2026-12-15',
                'lac_estado_lapso' => 'activo',
            ],
        );

        $lapsoAnterior = LapsoAcademico::firstOrCreate(
            ['lac_id_lapso' => '2026-1'],
            [
                'lac_fecha_inicio' => '2026-01-15',
                'lac_fecha_cierre' => '2026-06-30',
                'lac_estado_lapso' => 'cerrado',
            ],
        );

        return compact(
            'cedula',
            'pasaporte',
            'pendiente',
            'aprobada',
            'rechazada',
            'constancia',
            'revisionNota',
            'matricula',
            'cambioCarrera',
            'suspension',
            'traslado',
            'lapsoActual',
            'lapsoAnterior',
        );
    }

    /**
     * @param  array<string, mixed>  $catalogos
     * @return array<int, Usuario>
     */
    private function crearEstudiantes(array $catalogos): array
    {
        $datos = [
            ['V-90000001', 'María',    'Pérez',     'CED'],
            ['V-90000002', 'Carlos',   'Gómez',     'CED'],
            ['V-90000003', 'Andreína', 'Rojas',     'CED'],
            ['V-90000004', 'Luis',     'Fernández', 'CED'],
            ['V-90000005', 'Sofía',    'Herrera',   'CED'],
            ['V-90000006', 'Miguel',   'Torres',    'CED'],
            ['V-90000007', 'Valeria',  'Cruz',      'CED'],
            ['V-90000008', 'Diego',    'Salazar',   'CED'],
            ['E-90000009', 'Renata',   'Muller',    'PAS'],
            ['E-90000010', 'Julio',    'Bianchi',   'PAS'],
            ['V-90000011', 'Camila',   'Navarro',   'CED'],
            ['V-90000012', 'Ivan',     'Mendoza',   'CED'],
        ];

        $estudiantes = [];

        foreach ($datos as $indice => [$documento, $nombre, $apellido, $tipo]) {
            $correo = strtolower($this->sinAcentos($nombre).'.'.$this->sinAcentos($apellido)).$indice.'@demo.test';

            $estudiantes[] = Usuario::firstOrCreate(
                ['usu_numero_documento' => $documento],
                [
                    'usu_rol' => 'estudiante',
                    'usu_tdo_id' => $tipo === 'PAS' ? $catalogos['pasaporte']->tdo_id : $catalogos['cedula']->tdo_id,
                    'usu_primer_nombre' => $nombre,
                    'usu_primer_apellido' => $apellido,
                    'usu_correo_electronico' => $correo,
                    'usu_contrasena_hash' => bcrypt('password'),
                    'usu_estado_cuenta' => 'activo',
                    'usu_fecha_registro' => Carbon::now()->subDays(200 - $indice * 5),
                    'usu_ultimo_acceso' => Carbon::now()->subDays(random_int(0, 20)),
                ],
            );
        }

        return $estudiantes;
    }

    /**
     * Varios administradores para que la seccion de productividad tenga sentido.
     *
     * @param  array<string, mixed>  $catalogos
     * @return array<int, Usuario>
     */
    private function crearAdministradores(array $catalogos): array
    {
        $datos = [
            ['V-80000001', 'Patricia', 'Medina',   'patricia.medina@demo.test'],
            ['V-80000002', 'Rodrigo',  'Aguilar',  'rodrigo.aguilar@demo.test'],
            ['V-80000003', 'Gabriela', 'Rivas',    'gabriela.rivas@demo.test'],
        ];

        $administradores = [];

        foreach ($datos as [$documento, $nombre, $apellido, $correo]) {
            $administradores[] = Usuario::firstOrCreate(
                ['usu_numero_documento' => $documento],
                [
                    'usu_rol' => 'admin',
                    'usu_tdo_id' => $catalogos['cedula']->tdo_id,
                    'usu_primer_nombre' => $nombre,
                    'usu_primer_apellido' => $apellido,
                    'usu_correo_electronico' => $correo,
                    'usu_contrasena_hash' => bcrypt('password'),
                    'usu_estado_cuenta' => 'activo',
                    'usu_fecha_registro' => Carbon::now()->subDays(300),
                    'usu_ultimo_acceso' => Carbon::now()->subHours(random_int(1, 48)),
                ],
            );
        }

        return $administradores;
    }

    /**
     * Solicitudes ya resueltas, con historial de estados para que el panel pueda
     * medir los tiempos de resolucion y el cumplimiento de plazos.
     *
     * @param  array<string, mixed>  $catalogos
     * @param  array<int, Usuario>  $estudiantes
     * @param  array<int, Usuario>  $administradores
     */
    private function crearSolicitudesResueltas(array $catalogos, array $estudiantes, array $administradores): int
    {
        $tramites = [
            $catalogos['constancia'],
            $catalogos['revisionNota'],
            $catalogos['matricula'],
            $catalogos['cambioCarrera'],
            $catalogos['suspension'],
        ];

        // Estas tres nacen resueltas SIN historial, igual que las que crea
        // SolicitudesDePruebaSeeder. Sirven para que el panel muestre su aviso de
        // calidad de datos en vez de inventar unos tiempos que no puede medir.
        $sinHistorial = [3, 11, 19];

        // Tramites que se quedaron atascados. La mayoria se resuelve rapido, asi que
        // sin esto la cola de la distribucion seria tan corta que el percentil 90 y
        // el indicador "mas lento" no tendrian nada que mostrar.
        $atascadas = [7 => 21, 23 => 28, 41 => 16, 52 => 24];

        $total = 0;

        for ($i = 0; $i < 64; $i++) {
            // 40% rechazos, para que la tasa de rechazo no quede en cero.
            $estado = ($i % 5 === 0 || $i % 5 === 3) ? 'rechazada' : 'aprobada';

            // Sesgo hacia lo reciente: un quinto de las solicitudes nace en la ultima
            // semana, para que la serie de 7 dias del panel tambien tenga recorrido.
            $antiguedad = random_int(1, 100) <= 20
                ? random_int(1, 7)
                : random_int(8, self::DIAS_DE_HISTORIA);

            $creacion = Carbon::now()
                ->subDays($antiguedad)
                ->subHours(random_int(0, 23));

            $resolucion = $creacion->copy()->addDays($atascadas[$i] ?? $this->duracionAleatoria());

            // Nunca dejamos transiciones fechadas en el futuro.
            if ($resolucion->isFuture()) {
                $resolucion = Carbon::now()->subHours(random_int(1, 20));
            }

            $solicitud = $this->crearSolicitud(
                catalogos: $catalogos,
                estudiante: $estudiantes[array_rand($estudiantes)],
                tramite: $tramites[array_rand($tramites)],
                estado: $catalogos[$estado],
                creacion: $creacion,
                resolucion: $resolucion,
            );

            if (! in_array($i, $sinHistorial, true)) {
                HistorialEstadoSolicitud::create([
                    'hes_sol_id' => $solicitud->sol_id,
                    'hes_usu_id_responsable' => $administradores[array_rand($administradores)]->usu_id,
                    'hes_eso_id_anterior' => $catalogos['pendiente']->eso_id,
                    'hes_eso_id_nuevo' => $catalogos[$estado]->eso_id,
                    'hes_observaciones_comentarios' => $estado === 'aprobada'
                        ? 'Documentación correcta, se procede a resolver.'
                        : 'La documentación presentada no cumple con los requisitos.',
                    'hes_fecha_cambio' => $resolucion,
                ]);
            }

            $total++;
        }

        return $total;
    }

    /**
     * Solicitudes pendientes repartidas por las cinco franjas de antiguedad que usa
     * el panel (0-2, 3-6, 7-14, 15-30 y mas de 30 dias). Las mas viejas se pasan del
     * plazo estimado de su tramite, que es lo que dispara el contador de
     * "fuera de plazo" del panel.
     *
     * @param  array<string, mixed>  $catalogos
     * @param  array<int, Usuario>  $estudiantes
     */
    private function crearSolicitudesPendientes(array $catalogos, array $estudiantes): int
    {
        $tramites = [
            $catalogos['constancia'],
            $catalogos['revisionNota'],
            $catalogos['matricula'],
            $catalogos['cambioCarrera'],
            $catalogos['suspension'],
            $catalogos['traslado'],
        ];

        // Un representative por franja, repetido para llenar cada barra.
        $edades = [1, 4, 9, 20, 40];

        $total = 0;

        for ($i = 0; $i < 45; $i++) {
            $edad = $edades[$i % count($edades)] + random_int(0, 2);

            $this->crearSolicitud(
                catalogos: $catalogos,
                estudiante: $estudiantes[array_rand($estudiantes)],
                tramite: $tramites[array_rand($tramites)],
                estado: $catalogos['pendiente'],
                creacion: Carbon::now()->subDays($edad)->subHours(random_int(0, 23)),
                resolucion: null,
            );

            $total++;
        }

        return $total;
    }

    /**
     * @param  array<string, mixed>  $catalogos
     */
    private function crearSolicitud(
        array $catalogos,
        Usuario $estudiante,
        TipoSolicitud $tramite,
        EstadoSolicitud $estado,
        Carbon $creacion,
        ?Carbon $resolucion,
    ): Solicitud {
        // Una solicitud pertenece al lapso que estaba abierto cuando se creó, así que
        // se compara contra la fecha de inicio del catálogo, no contra el año natural.
        // Con esto la tabla "por periodo académico" del panel sale con los dos lapsos.
        $inicioPeriodoActual = Carbon::parse($catalogos['lapsoActual']->lac_fecha_inicio)->startOfDay();

        $lapso = $creacion->lt($inicioPeriodoActual)
            ? $catalogos['lapsoAnterior']
            : $catalogos['lapsoActual'];

        $solicitud = Solicitud::create([
            'sol_usu_id' => $estudiante->usu_id,
            'sol_tsi_id' => $tramite->tsi_id,
            'sol_lac_id' => $lapso->lac_id,
            'sol_eso_id' => $estado->eso_id,
            'sol_id_seguimiento' => self::PREFIJO.strtoupper(Str::random(6)),
            'sol_motivo_detallado' => $this->motivoAleatorio(),
            'sol_prioridad' => 'normal',
            'sol_fecha_creacion' => $creacion,
            'sol_fecha_ultima_actualizacion' => $resolucion ?? $creacion,
            'sol_fecha_resolucion' => $resolucion,
        ]);

        // Adjuntos en aproximadamente el 70% de las solicitudes, para que el bloque de
        // cobertura documental muestre tanto las que tienen archivo como las que no.
        if (random_int(1, 10) <= 7) {
            $this->crearAdjuntos($solicitud, $creacion, random_int(1, 3));
        }

        return $solicitud;
    }

    private function crearAdjuntos(Solicitud $solicitud, Carbon $creacion, int $cantidad): void
    {
        $extensiones = ['pdf', 'jpg', 'png'];

        for ($d = 0; $d < $cantidad; $d++) {
            $extension = $extensiones[$d % count($extensiones)];

            Documentacion::create([
                'doc_sol_id' => $solicitud->sol_id,
                'doc_nombre_original_archivo' => 'soporte_'.($d + 1).'.'.$extension,
                'doc_tipo_documento' => $d === 0 ? 'Cédula de Identidad' : 'Soporte académico',
                'doc_formato_archivo' => strtoupper($extension),
                'doc_tamano_bytes' => random_int(80_000, 4_500_000),
                'doc_ruta_almacenamiento_url' => 'documentos/demo/'.$solicitud->sol_id_seguimiento.'/soporte_'.($d + 1).'.'.$extension,
                'doc_fecha_subida' => $creacion->copy()->addHours(random_int(1, 10)),
                'doc_estado_validacion' => 'validado',
            ]);
        }
    }

    /**
     * Duracion de la atencion con una distribucion realista: muchas se resuelven el
     * mismo dia o al dia siguiente, y una cola larga se va de plazo.
     */
    private function duracionAleatoria(): int
    {
        return match (random_int(1, 100)) {
            98, 99, 100 => random_int(13, 30),        // ~3% muy lenta
            91, 92, 93, 94, 95, 96, 97 => random_int(6, 12), // ~7% lenta
            51, 52, 53, 54, 55, 56, 57, 58, 59,
            60, 61, 62, 63, 64, 65, 66, 67, 68,
            69, 70, 71, 72, 73, 74, 75, 76, 77,
            78, 79, 80, 81, 82, 83, 84, 85, 86,
            87, 88, 89, 90 => random_int(2, 5),      // ~40% normal
            default => random_int(0, 1),               // ~50% express
        };
    }

    private function motivoAleatorio(): string
    {
        $motivos = [
            'Necesito el documento para iniciar un trámite externo.',
            'Solicitud presentada dentro del plazo establecido.',
            'Adjunto la documentación solicitada por la coordinación académica.',
            'Continuidad del trámite que inicié en el periodo anterior.',
            'Necesito la certificación para un proceso legal.',
            'Solicito información sobre el estado de mi expediente.',
        ];

        return $motivos[array_rand($motivos)];
    }

    /**
     * Quita acentos y pasa a minusculas para poder construir correos validos.
     */
    private function sinAcentos(string $texto): string
    {
        return mb_strtolower(strtr($texto, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
            'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u', 'Ü' => 'u', 'Ñ' => 'n',
        ]));
    }
}
