<?php

use App\Models\EstadoSolicitud;
use App\Models\HistorialEstadoSolicitud;
use App\Models\LapsoAcademico;
use App\Models\Solicitud;
use App\Models\TipoDocumento;
use App\Models\TipoSolicitud;
use App\Models\Usuario;
use App\Services\SolicitudesEstadisticasService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * Crea los catalogos minimos (tipos de documento, estados y un tipo de tramite)
 * que necesitan las tablas de solicitudes. El plazo del tramite es de 3 dias,
 * que es el valor que despues usamos para comprobar el calculo del SLA.
 */
function estadisticasCatalogo(): array
{
    $documento = TipoDocumento::create([
        'tdo_nombre_documento' => 'Cédula de Identidad',
        'tdo_abreviatura' => 'V',
    ]);

    $pendiente = EstadoSolicitud::create(['eso_nombre_estado' => 'pendiente']);
    $aprobada = EstadoSolicitud::create(['eso_nombre_estado' => 'aprobada']);
    $rechazada = EstadoSolicitud::create(['eso_nombre_estado' => 'rechazada']);

    $tramite = TipoSolicitud::create([
        'tsi_nombre_tipo' => 'Constancia de Estudio',
        'tsi_estado_tipo' => 'activo',
        'tsi_tiempo_estimado_dias' => 3,
    ]);

    $lapso = LapsoAcademico::create([
        'lac_id_lapso' => '2026-2',
        'lac_fecha_inicio' => '2026-07-01',
        'lac_fecha_cierre' => '2026-12-15',
        'lac_estado_lapso' => 'activo',
    ]);

    return compact('documento', 'pendiente', 'aprobada', 'rechazada', 'tramite', 'lapso');
}

function estadisticasUsuario(string $rol, string $numeroDocumento, int $tipoDocumentoId): Usuario
{
    return Usuario::create([
        'usu_rol' => $rol,
        'usu_tdo_id' => $tipoDocumentoId,
        'usu_primer_nombre' => 'Usuario',
        'usu_primer_apellido' => $rol,
        'usu_numero_documento' => $numeroDocumento,
        'usu_correo_electronico' => $numeroDocumento.'@ejemplo.com',
        'usu_contrasena_hash' => bcrypt('password'),
        'usu_estado_cuenta' => 'activo',
        'usu_fecha_registro' => now(),
    ]);
}

/**
 * @param  array<string, mixed>  $catalogo
 */
function estadisticasSolicitud(
    array $catalogo,
    Usuario $estudiante,
    string $estado,
    ?DateTimeInterface $resolucionEn = null,
    ?DateTimeInterface $creadaEn = null,
): Solicitud {
    $creadaEn ??= now()->subDays(5);

    $solicitud = Solicitud::create([
        'sol_usu_id' => $estudiante->usu_id,
        'sol_tsi_id' => $catalogo['tramite']->tsi_id,
        'sol_lac_id' => $catalogo['lapso']->lac_id,
        'sol_eso_id' => $catalogo[$estado]->eso_id,
        'sol_id_seguimiento' => 'SOL-'.Str::random(8),
        'sol_motivo_detallado' => 'Motivo de prueba',
        'sol_prioridad' => 'normal',
        'sol_fecha_creacion' => $creadaEn,
        'sol_fecha_ultima_actualizacion' => $resolucionEn ?? now(),
    ]);

    // Si la solicitud nació resuelta, dejamos la traza de auditoría que el panel usa
    // para medir los tiempos.
    if ($resolucionEn !== null) {
        HistorialEstadoSolicitud::create([
            'hes_sol_id' => $solicitud->sol_id,
            'hes_usu_id_responsable' => $estudiante->usu_id,
            'hes_eso_id_anterior' => $catalogo['pendiente']->eso_id,
            'hes_eso_id_nuevo' => $catalogo[$estado]->eso_id,
            'hes_fecha_cambio' => $resolucionEn,
        ]);
    }

    return $solicitud;
}

/*
|--------------------------------------------------------------------------
| Acceso al panel
|--------------------------------------------------------------------------
*/

test('un visitante sin sesión es enviado al login', function () {
    $this->get(route('admin.estadisticas'))->assertRedirect(route('login'));
});

test('un estudiante no puede ver el panel estadístico', function () {
    $catalogo = estadisticasCatalogo();
    $estudiante = estadisticasUsuario('estudiante', 'V-11111111', $catalogo['documento']->tdo_id);

    $this->actingAs($estudiante)
        ->get(route('admin.estadisticas'))
        ->assertForbidden();
});

test('un estudiante tampoco puede exportar las métricas', function () {
    $catalogo = estadisticasCatalogo();
    $estudiante = estadisticasUsuario('estudiante', 'V-11111112', $catalogo['documento']->tdo_id);

    $this->actingAs($estudiante)
        ->get(route('admin.estadisticas.exportar'))
        ->assertForbidden();
});

test('un administrador accede al panel y encuentra el enlace en el header', function () {
    $catalogo = estadisticasCatalogo();
    $admin = estadisticasUsuario('administrador', 'V-99999999', $catalogo['documento']->tdo_id);

    $estudiante = estadisticasUsuario('estudiante', 'V-11111113', $catalogo['documento']->tdo_id);
    estadisticasSolicitud($catalogo, $estudiante, 'pendiente');

    $this->actingAs($admin)
        ->get(route('admin.estadisticas'))
        ->assertOk()
        ->assertSee('Panel Estadístico de Solicitudes')
        ->assertSee(route('admin.estadisticas'), false)
        ->assertViewHas('metricas');
});

test('el administrador puede exportar las métricas a CSV', function () {
    $catalogo = estadisticasCatalogo();
    $admin = estadisticasUsuario('administrador', 'V-99999998', $catalogo['documento']->tdo_id);

    $this->actingAs($admin)
        ->get(route('admin.estadisticas.exportar'))
        ->assertOk()
        ->assertDownload();
});

/*
|--------------------------------------------------------------------------
| Precisión de las métricas
|--------------------------------------------------------------------------
*/

test('el resumen global cuenta cada estado y calcula las tasas', function () {
    $catalogo = estadisticasCatalogo();
    $estudiante = estadisticasUsuario('estudiante', 'V-22222222', $catalogo['documento']->tdo_id);

    estadisticasSolicitud($catalogo, $estudiante, 'pendiente');
    estadisticasSolicitud($catalogo, $estudiante, 'aprobada', now()->subDays(2));
    estadisticasSolicitud($catalogo, $estudiante, 'rechazada', now()->subDays(1));

    $resumen = app(SolicitudesEstadisticasService::class)->resumenGlobal();

    expect($resumen['total'])->toBe(3)
        ->and($resumen['pendientes'])->toBe(1)
        ->and($resumen['aprobadas'])->toBe(1)
        ->and($resumen['rechazadas'])->toBe(1)
        ->and($resumen['resueltas'])->toBe(2)
        ->and($resumen['tasa_resolucion'])->toBe(66.7)
        // 1 aprobada de 2 resueltas.
        ->and($resumen['tasa_aprobacion'])->toBe(50.0)
        ->and($resumen['tasa_rechazo'])->toBe(50.0);
});

test('los tiempos de resolución se miden desde el historial de estados', function () {
    $catalogo = estadisticasCatalogo();
    $estudiante = estadisticasUsuario('estudiante', 'V-22222223', $catalogo['documento']->tdo_id);

    // Creada hace 5 días y resuelta hace 2 días => 3 días de atención.
    estadisticasSolicitud($catalogo, $estudiante, 'aprobada', now()->subDays(2));

    $tiempos = app(SolicitudesEstadisticasService::class)->tiemposDeResolucion();

    expect($tiempos['muestra'])->toBe(1)
        ->and($tiempos['promedio'])->toBe(3.0)
        ->and($tiempos['mediana'])->toBe(3.0)
        ->and($tiempos['minimo'])->toBe(3.0)
        ->and($tiempos['maximo'])->toBe(3.0);
});

test('las solicitudes sin historial no se cuelan en los tiempos', function () {
    $catalogo = estadisticasCatalogo();
    $estudiante = estadisticasUsuario('estudiante', 'V-22222224', $catalogo['documento']->tdo_id);

    // Nace resuelta pero sin traza de auditoría: el panel debe avisarlo, no inventar el tiempo.
    estadisticasSolicitud($catalogo, $estudiante, 'aprobada');

    $servicio = app(SolicitudesEstadisticasService::class);

    expect($servicio->tiemposDeResolucion()['muestra'])->toBe(0)
        ->and($servicio->tiempoHastaPrimeraGestion()['sin_gestionar'])->toBe(1);
});

test('el cumplimiento del SLA compara el plazo configurado contra el tiempo real', function () {
    $catalogo = estadisticasCatalogo();
    $estudiante = estadisticasUsuario('estudiante', 'V-22222225', $catalogo['documento']->tdo_id);

    // El trámite tiene un plazo de 3 días, pero se resolvió en 5.
    estadisticasSolicitud($catalogo, $estudiante, 'aprobada', now());

    $sla = app(SolicitudesEstadisticasService::class)->cumplimientoPorTipo();

    expect($sla)->toHaveCount(1);

    $fila = $sla->first();

    expect($fila['nombre'])->toBe('Constancia de Estudio')
        ->and($fila['tiempo_estimado_dias'])->toBe(3)
        ->and($fila['promedio_dias'])->toBe(5.0)
        ->and($fila['dentro_de_plazo'])->toBe(0)
        ->and($fila['fuera_de_plazo'])->toBe(1)
        ->and($fila['porcentaje_cumplimiento'])->toBe(0.0);
});

test('la serie temporal devuelve un punto por día e incluye los ceros', function () {
    $catalogo = estadisticasCatalogo();
    $estudiante = estadisticasUsuario('estudiante', 'V-22222226', $catalogo['documento']->tdo_id);

    // Creada y resuelta hoy: es el único día con movimiento de la ventana.
    estadisticasSolicitud($catalogo, $estudiante, 'aprobada', now(), now());

    $serie = app(SolicitudesEstadisticasService::class)->seriesTemporales(7);

    expect($serie)->toHaveCount(7);

    $hoy = $serie[6];

    expect($hoy['fecha'])->toBe(now()->toDateString())
        ->and($hoy['creadas'])->toBe(1)
        ->and($hoy['resueltas'])->toBe(1)
        // Los seis días anteriores no registran ni creación ni resolución.
        ->and($serie[0]['creadas'])->toBe(0)
        ->and($serie[0]['resueltas'])->toBe(0);
});

test('el rango de la serie se acota a los valores permitidos', function () {
    $servicio = app(SolicitudesEstadisticasService::class);

    expect($servicio->seriesTemporales(7))->toHaveCount(7)
        ->and($servicio->seriesTemporales(30))->toHaveCount(30)
        // Un valor absurdo se recorta al máximo permitido, no revienta.
        ->and($servicio->seriesTemporales(5000))->toHaveCount(365);
});

test('la distribución por tipo de trámite suma el total del sistema', function () {
    $catalogo = estadisticasCatalogo();
    $estudiante = estadisticasUsuario('estudiante', 'V-22222227', $catalogo['documento']->tdo_id);

    estadisticasSolicitud($catalogo, $estudiante, 'pendiente');
    estadisticasSolicitud($catalogo, $estudiante, 'aprobada', now()->subDays(1));

    $porTipo = app(SolicitudesEstadisticasService::class)->distribucionPorTipo();

    expect($porTipo)->toHaveCount(1);

    $fila = $porTipo->first();

    expect($fila['total'])->toBe(2)
        ->and($fila['pendientes'])->toBe(1)
        ->and($fila['aprobadas'])->toBe(1)
        ->and($fila['participacion'])->toBe(100.0)
        ->and($fila['tasa_aprobacion'])->toBe(100.0);
});

test('el panel no rompe cuando el sistema no tiene solicitudes', function () {
    $catalogo = estadisticasCatalogo();
    $admin = estadisticasUsuario('administrador', 'V-99999997', $catalogo['documento']->tdo_id);

    $servicio = app(SolicitudesEstadisticasService::class);

    expect($servicio->resumenGlobal())->toMatchArray([
        'total' => 0,
        'pendientes' => 0,
        'tasa_resolucion' => 0.0,
    ])
        ->and($servicio->tiemposDeResolucion()['mediana'])->toBeNull()
        ->and($servicio->antiguedadDePendientes()['total'])->toBe(0);

    $this->actingAs($admin)
        ->get(route('admin.estadisticas'))
        ->assertOk()
        ->assertSee('Todavía no hay solicitudes registradas en el sistema.');
});

test('las pendientes que superan el plazo de su trámite se cuentan como vencidas', function () {
    $catalogo = estadisticasCatalogo();
    $estudiante = estadisticasUsuario('estudiante', 'V-22222228', $catalogo['documento']->tdo_id);

    // Plazo de 3 días, pero lleva 10 días esperando.
    $solicitud = estadisticasSolicitud($catalogo, $estudiante, 'pendiente');
    $solicitud->update(['sol_fecha_creacion' => now()->subDays(10)]);

    $antiguedad = app(SolicitudesEstadisticasService::class)->antiguedadDePendientes();

    expect($antiguedad['total'])->toBe(1)
        ->and($antiguedad['vencidas_de_sla'])->toBe(1)
        ->and($antiguedad['maxima_dias'])->toBeGreaterThanOrEqual(9.9);
});

/*
|--------------------------------------------------------------------------
| Consultas con coalesce en el SELECT
|
| Estas métricas aplican coalesce() sobre columnas que pueden venir NULL.
| El GROUP BY debe referenciar las columnas reales, no la expresión, porque
| Laravel escapa groupBy() como un identificador y rompería el SQL.
|
| Ojo: solo la del flujo de estados tiene una columna que de verdad admite NULL
| (historial_estado_solicitudes.hes_eso_id_anterior). usuarios.usu_tdo_id y
| solicitudes.sol_lac_id son NOT NULL con clave foránea, así que sus ramas
| '—' y 'Sin lapso' son defensivas y no se pueden provocar desde un test.
| Las pruebas de abajo cubren entonces el agrupamiento, que es lo que importa.
|--------------------------------------------------------------------------
*/

test('la distribución por tipo de documento agrupa por tipo y reparte el porcentaje', function () {
    $catalogo = estadisticasCatalogo();

    // Un segundo tipo de documento, para que el agrupamiento tenga algo que separar.
    $pasaporte = TipoDocumento::create([
        'tdo_nombre_documento' => 'Pasaporte',
        'tdo_abreviatura' => 'E',
    ]);

    $conCedula = estadisticasUsuario('estudiante', 'V-33333331', $catalogo['documento']->tdo_id);
    estadisticasSolicitud($catalogo, $conCedula, 'pendiente');

    $conPasaporte = estadisticasUsuario('estudiante', 'V-33333332', $pasaporte->tdo_id);
    estadisticasSolicitud($catalogo, $conPasaporte, 'aprobada', now());

    $filas = app(SolicitudesEstadisticasService::class)->distribucionPorTipoDocumento();

    expect($filas)->toHaveCount(2);

    $cedula = $filas->firstWhere('abreviatura', 'V');
    $pasaporteFila = $filas->firstWhere('abreviatura', 'E');

    expect($cedula)->not->toBeNull()
        ->and($cedula['nombre'])->toBe('Cédula de Identidad')
        ->and($cedula['total'])->toBe(1)
        ->and($cedula['participacion'])->toBe(50.0)
        ->and($pasaporteFila)->not->toBeNull()
        ->and($pasaporteFila['nombre'])->toBe('Pasaporte')
        ->and($pasaporteFila['participacion'])->toBe(50.0);

    // La suma de la distribución debe seguir cuadrando con el total real.
    expect($filas->sum('total'))->toBe(app(SolicitudesEstadisticasService::class)->resumenGlobal()['total']);
});

test('la distribución por periodo académico junta los estados del mismo lapso', function () {
    $catalogo = estadisticasCatalogo();
    $estudiante = estadisticasUsuario('estudiante', 'V-33333333', $catalogo['documento']->tdo_id);

    // Dos solicitudes del mismo lapso pero en estados distintos: la consulta agrupa
    // por (lapso, estado) y luego reagrupa por lapso, así que deben colapsar en una
    // sola fila con los dos contadores.
    estadisticasSolicitud($catalogo, $estudiante, 'pendiente');
    estadisticasSolicitud($catalogo, $estudiante, 'aprobada', now());

    $filas = app(SolicitudesEstadisticasService::class)->distribucionPorLapso();

    expect($filas)->toHaveCount(1);

    $fila = $filas->first();

    expect($fila['lapso'])->toBe('2026-2')
        ->and($fila['estado_lapso'])->toBe('activo')
        ->and($fila['total'])->toBe(2)
        ->and($fila['pendientes'])->toBe(1)
        ->and($fila['aprobadas'])->toBe(1)
        ->and($fila['rechazadas'])->toBe(0);
});

test('el flujo de estados cuenta la primera transición y las siguientes', function () {
    $catalogo = estadisticasCatalogo();
    $estudiante = estadisticasUsuario('estudiante', 'V-33333334', $catalogo['documento']->tdo_id);

    $solicitud = estadisticasSolicitud($catalogo, $estudiante, 'pendiente');

    // Primera gestión: el admin pasa la solicitud a aprobada.
    HistorialEstadoSolicitud::create([
        'hes_sol_id' => $solicitud->sol_id,
        'hes_usu_id_responsable' => $estudiante->usu_id,
        'hes_eso_id_anterior' => $catalogo['pendiente']->eso_id,
        'hes_eso_id_nuevo' => $catalogo['aprobada']->eso_id,
        'hes_fecha_cambio' => now(),
    ]);

    // Segunda transición, para verificar el conteo agregado.
    HistorialEstadoSolicitud::create([
        'hes_sol_id' => $solicitud->sol_id,
        'hes_usu_id_responsable' => $estudiante->usu_id,
        'hes_eso_id_anterior' => $catalogo['aprobada']->eso_id,
        'hes_eso_id_nuevo' => $catalogo['rechazada']->eso_id,
        'hes_fecha_cambio' => now()->addDay(),
    ]);

    // Transición sin estado previo, para el ramal del coalesce.
    $otra = estadisticasSolicitud($catalogo, $estudiante, 'pendiente');
    HistorialEstadoSolicitud::create([
        'hes_sol_id' => $otra->sol_id,
        'hes_usu_id_responsable' => $estudiante->usu_id,
        'hes_eso_id_anterior' => null,
        'hes_eso_id_nuevo' => $catalogo['aprobada']->eso_id,
        'hes_fecha_cambio' => now(),
    ]);

    $flujo = app(SolicitudesEstadisticasService::class)->flujoDeEstados();

    expect($flujo)->toHaveCount(3);

    $pendienteAprobada = $flujo->firstWhere('anterior', 'pendiente');

    expect($pendienteAprobada['nuevo'])->toBe('aprobada')
        ->and($pendienteAprobada['total'])->toBe(1);

    // Todas las transiciones del historial deben estar representadas.
    expect($flujo->sum('total'))->toBe(HistorialEstadoSolicitud::count());
});
