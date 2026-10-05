<?php

use App\Models\EstadoSolicitud;
use App\Models\HistorialEstadoSolicitud;
use App\Models\LapsoAcademico;
use App\Models\Rol;
use App\Models\Solicitud;
use App\Models\TipoDocumento;
use App\Models\TipoSolicitud;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

/**
 * Catalogo minimo (idempotente) para crear solicitudes en pruebas.
 */
function catalogoTratadas(): array
{
    $documento = TipoDocumento::firstOrCreate(
        ['tdo_abreviatura' => 'V'],
        ['tdo_nombre_documento' => 'Cédula de Identidad'],
    );

    $estados = collect(['pendiente', 'aprobada', 'rechazada'])
        ->mapWithKeys(fn (string $nombre) => [
            $nombre => EstadoSolicitud::firstOrCreate(
                ['eso_nombre_estado' => $nombre],
                ['eso_descripcion' => 'Estado de prueba.'],
            ),
        ]);

    return [
        'tipo_documento' => $documento,
        'estados' => $estados,
        'periodo' => LapsoAcademico::firstOrCreate(
            ['lac_id_lapso' => '2026-1'],
            [
                'lac_fecha_inicio' => now()->startOfYear()->toDateString(),
                'lac_fecha_cierre' => now()->addMonths(5)->toDateString(),
                'lac_estado_lapso' => 'activo',
            ],
        ),
    ];
}

/**
 * Helpers para crear usuarios de prueba (idempotentes por documento).
 */
function admin(string $documento = 'V-90000000'): Usuario
{
    $catalogo = catalogoTratadas();

    return Usuario::firstOrCreate(
        ['usu_numero_documento' => $documento],
        [
            // La migración 2026_10_04_025359 renombró el rol: 'admin' ya no vale
            // y la restricción CHECK de PostgreSQL lo rechaza. Ahora el rol
            // administrativo se llama 'administrador'.
            'usu_rol' => Rol::ADMINISTRADOR,
            'usu_tdo_id' => $catalogo['tipo_documento']->tdo_id,
            'usu_primer_nombre' => 'Admin',
            'usu_primer_apellido' => 'Test',
            'usu_correo_electronico' => $documento.'@test.com',
            'usu_contrasena_hash' => bcrypt('password'),
            'usu_estado_cuenta' => 'activo',
            'usu_fecha_registro' => now(),
        ],
    );
}

function estudiante(string $documento = 'V-20000001'): Usuario
{
    $catalogo = catalogoTratadas();

    return Usuario::firstOrCreate(
        ['usu_numero_documento' => $documento],
        [
            'usu_rol' => 'estudiante',
            'usu_tdo_id' => $catalogo['tipo_documento']->tdo_id,
            'usu_primer_nombre' => 'Estudiante',
            'usu_primer_apellido' => 'Test',
            'usu_correo_electronico' => $documento.'@test.com',
            'usu_contrasena_hash' => bcrypt('password'),
            'usu_estado_cuenta' => 'activo',
            'usu_fecha_registro' => now(),
        ],
    );
}

function tipoSolicitud(string $nombre = 'Constancia de Estudio'): TipoSolicitud
{
    return TipoSolicitud::firstOrCreate(
        ['tsi_nombre_tipo' => $nombre],
        [
            'tsi_descripcion' => 'Descripción de prueba',
            'tsi_tiempo_estimado_dias' => 5,
            'tsi_requiere_aprobacion_especial' => false,
            'tsi_estado_tipo' => 'activo',
            'tsi_fecha_inicio' => now()->subDays(10),
            'tsi_fecha_fin' => now()->addDays(30),
        ],
    );
}

function estado(string $nombre): EstadoSolicitud
{
    return EstadoSolicitud::where('eso_nombre_estado', $nombre)->firstOrFail();
}

/**
 * Crea una solicitud CON historial y deja la solicitud en el ultimo estado del
 * recorrido, igual que hace AdminDashboardController::cambiarEstado().
 *
 * @param  array<int, string>  $estados  nombres de estado, en orden
 */
function solicitudConHistorial(Usuario $estudiante, TipoSolicitud $tipo, array $estados, ?Carbon $creadaEn = null, ?Carbon $resueltaEn = null): Solicitud
{
    $catalogo = catalogoTratadas();
    $creadaEn ??= now()->subDays(5);

    $s = Solicitud::create([
        'sol_usu_id' => $estudiante->usu_id,
        'sol_tsi_id' => $tipo->tsi_id,
        'sol_lac_id' => $catalogo['periodo']->lac_id,
        'sol_eso_id' => estado('pendiente')->eso_id,
        'sol_id_seguimiento' => 'SOL-TEST-'.fake()->unique()->bothify('??####'),
        'sol_motivo_detallado' => 'Motivo '.fake()->unique()->bothify('??####'),
        'sol_prioridad' => 'normal',
        'sol_fecha_creacion' => $creadaEn,
        'sol_fecha_ultima_actualizacion' => $creadaEn,
    ]);

    $estadoAnterior = estado('pendiente')->eso_id;
    $resolucion = null;

    foreach ($estados as $i => $nombreEstado) {
        $nuevo = estado($nombreEstado)->eso_id;
        $fecha = $i === 0
            ? ($resueltaEn ?? $creadaEn->copy()->addDays(2))
            : ($resueltaEn ?? $creadaEn->copy()->addDays(2))->copy()->addHours($i);

        HistorialEstadoSolicitud::create([
            'hes_sol_id' => $s->sol_id,
            'hes_usu_id_responsable' => admin()->usu_id,
            'hes_eso_id_anterior' => $estadoAnterior,
            'hes_eso_id_nuevo' => $nuevo,
            'hes_observaciones_comentarios' => "Observación $i",
            'hes_fecha_cambio' => $fecha,
        ]);

        if (in_array($nombreEstado, ['aprobada', 'rechazada'], true) && $resolucion === null) {
            $resolucion = $fecha;
        }

        $estadoAnterior = $nuevo;
    }

    // La solicitud queda en el estado final del recorrido, no en "pendiente".
    $s->update([
        'sol_eso_id' => $estadoAnterior,
        'sol_fecha_resolucion' => $resolucion,
        'sol_fecha_ultima_actualizacion' => $resolucion ?? $creadaEn,
    ]);

    $s->refresh();

    return $s;
}

function solicitudPendienteSinHistorial(Usuario $estudiante, TipoSolicitud $tipo, ?Carbon $creadaEn = null): Solicitud
{
    $catalogo = catalogoTratadas();

    return Solicitud::create([
        'sol_usu_id' => $estudiante->usu_id,
        'sol_tsi_id' => $tipo->tsi_id,
        'sol_lac_id' => $catalogo['periodo']->lac_id,
        'sol_eso_id' => estado('pendiente')->eso_id,
        'sol_id_seguimiento' => 'SOL-TEST-'.fake()->unique()->bothify('??####'),
        'sol_motivo_detallado' => 'Motivo '.fake()->unique()->bothify('??####'),
        'sol_prioridad' => 'normal',
        'sol_fecha_creacion' => $creadaEn ?? now(),
    ]);
}

beforeEach(function () {
    $this->actingAs(admin());
});

test('el listado de tratadas muestra solo solicitudes con historial', function () {
    $tipo = tipoSolicitud();
    $est = estudiante();

    $s1 = solicitudConHistorial($est, $tipo, ['aprobada']);
    $s2 = solicitudConHistorial($est, $tipo, ['rechazada']);
    $s3 = solicitudPendienteSinHistorial($est, $tipo);

    $this->get(route('admin.tratadas.index'))
        ->assertOk()
        ->assertSee($s1->sol_id_seguimiento)
        ->assertSee($s2->sol_id_seguimiento)
        ->assertDontSee($s3->sol_id_seguimiento);
});

test('el listado filtra por estado', function () {
    $tipo = tipoSolicitud();
    $est = estudiante();

    $aprobada = solicitudConHistorial($est, $tipo, ['aprobada']);
    $rechazada = solicitudConHistorial($est, $tipo, ['rechazada']);

    $this->get(route('admin.tratadas.index', ['estado' => 'aprobada']))
        ->assertOk()
        ->assertSee($aprobada->sol_id_seguimiento)
        ->assertDontSee($rechazada->sol_id_seguimiento);
});

test('el listado filtra por tipo de trámite', function () {
    $tipo1 = tipoSolicitud('Constancia');
    $tipo2 = tipoSolicitud('Cambio de Carrera');
    $est = estudiante();

    $s1 = solicitudConHistorial($est, $tipo1, ['aprobada']);
    $s2 = solicitudConHistorial($est, $tipo2, ['aprobada']);

    $this->get(route('admin.tratadas.index', ['tipo' => $tipo1->tsi_id]))
        ->assertOk()
        ->assertSee($s1->sol_id_seguimiento)
        ->assertDontSee($s2->sol_id_seguimiento);
});

test('el listado filtra por responsable', function () {
    $tipo = tipoSolicitud();
    $est = estudiante();
    $otroAdmin = admin('V-90000001'); // segundo admin, distinto del que esta en sesion

    $s1 = solicitudConHistorial($est, $tipo, ['aprobada']); // resuelta por el admin de la sesion

    // Otra solicitud resuelta por el segundo admin
    $s2 = Solicitud::create([
        'sol_usu_id' => $est->usu_id,
        'sol_tsi_id' => $tipo->tsi_id,
        'sol_lac_id' => catalogoTratadas()['periodo']->lac_id,
        'sol_eso_id' => estado('aprobada')->eso_id,
        'sol_id_seguimiento' => 'SOL-TEST-'.fake()->unique()->bothify('??####'),
        'sol_motivo_detallado' => 'Otra',
        'sol_prioridad' => 'normal',
        'sol_fecha_creacion' => now()->subDays(2),
        'sol_fecha_resolucion' => now(),
    ]);
    HistorialEstadoSolicitud::create([
        'hes_sol_id' => $s2->sol_id,
        'hes_usu_id_responsable' => $otroAdmin->usu_id,
        'hes_eso_id_anterior' => estado('pendiente')->eso_id,
        'hes_eso_id_nuevo' => estado('aprobada')->eso_id,
        'hes_fecha_cambio' => now(),
    ]);

    $this->get(route('admin.tratadas.index', ['responsable' => $otroAdmin->usu_id]))
        ->assertOk()
        ->assertSee($s2->sol_id_seguimiento)
        ->assertDontSee($s1->sol_id_seguimiento);
});

test('el listado filtra por rango de fechas', function () {
    $tipo = tipoSolicitud();
    $est = estudiante();

    // La antigua se resolvio hace 10 dias; la reciente, ayer.
    $antigua = solicitudConHistorial($est, $tipo, ['aprobada'], now()->subDays(12), now()->subDays(10));
    $reciente = solicitudConHistorial($est, $tipo, ['rechazada'], now()->subDays(3), now()->subDay());

    $this->get(route('admin.tratadas.index', ['desde' => now()->subDays(2)->format('Y-m-d')]))
        ->assertOk()
        ->assertSee($reciente->sol_id_seguimiento)
        ->assertDontSee($antigua->sol_id_seguimiento);
});

test('la ficha muestra la línea de tiempo completa con observaciones', function () {
    $tipo = tipoSolicitud();
    $est = estudiante();
    $s = solicitudConHistorial($est, $tipo, ['aprobada']);

    $this->get(route('admin.tratadas.show', $s))
        ->assertOk()
        ->assertSee($s->sol_id_seguimiento)
        ->assertSee('aprobada')
        ->assertSee('Observación 0')
        ->assertSee($est->nombre_completo);
});

test('la ficha muestra el tiempo de resolución', function () {
    $tipo = tipoSolicitud();
    $est = estudiante();
    $s = solicitudConHistorial($est, $tipo, ['aprobada']);

    $this->get(route('admin.tratadas.show', $s))
        ->assertOk()
        ->assertSee('Tiempo de resolución');
});

test('el resumen cuenta correctamente por estado', function () {
    $tipo = tipoSolicitud();
    $est = estudiante();

    solicitudConHistorial($est, $tipo, ['aprobada']);
    solicitudConHistorial($est, $tipo, ['aprobada']);
    solicitudConHistorial($est, $tipo, ['rechazada']);

    $this->get(route('admin.tratadas.index'))
        ->assertOk()
        ->assertSee('Total tratadas')
        ->assertSee('2') // aprobadas
        ->assertSee('1'); // rechazadas
});

test('un estudiante no puede acceder al listado de tratadas', function () {
    $est = estudiante();
    $this->actingAs($est);

    $this->get(route('admin.tratadas.index'))
        ->assertStatus(403);
});

test('un estudiante no puede acceder a la ficha de tratadas', function () {
    $est = estudiante();
    $tipo = tipoSolicitud();
    $s = solicitudConHistorial($est, $tipo, ['aprobada']);
    $this->actingAs($est);

    $this->get(route('admin.tratadas.show', $s))
        ->assertStatus(403);
});

test('el dashboard lista todas las solicitudes y separa las resueltas por el filtro de estado', function () {
    $tipo = tipoSolicitud();
    $est = estudiante();

    $pendiente = solicitudPendienteSinHistorial($est, $tipo);
    $aprobada = solicitudConHistorial($est, $tipo, ['aprobada']);

    // El panel muestra el listado completo (asi lo dejo el PR #15, con filtro
    // ?estado=), y las ya resueltas se consultan ademas en /admin/tratadas.
    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee($pendiente->sol_motivo_detallado)
        ->assertSee($aprobada->sol_motivo_detallado);

    $this->get(route('admin.dashboard', ['estado' => 'pendiente']))
        ->assertOk()
        ->assertSee($pendiente->sol_motivo_detallado)
        ->assertDontSee($aprobada->sol_motivo_detallado);

    $this->get(route('admin.dashboard', ['estado' => 'aprobada']))
        ->assertOk()
        ->assertSee($aprobada->sol_motivo_detallado)
        ->assertDontSee($pendiente->sol_motivo_detallado);
});

test('aprobar una solicitud desde el dashboard la mueve a tratadas y registra observación', function () {
    $tipo = tipoSolicitud();
    $est = estudiante();
    $s = solicitudPendienteSinHistorial($est, $tipo);

    $this->post(route('admin.dashboard.estado', ['id' => $s->sol_id, 'accion' => 'aprobar']), [
        'observacion' => 'Todo correcto, aprobado.',
    ])->assertRedirect();

    $s->refresh();
    expect($s->sol_eso_id)->toBe(estado('aprobada')->eso_id);
    expect($s->historialEstados)->toHaveCount(1);
    expect($s->historialEstados->first()->hes_observaciones_comentarios)->toBe('Todo correcto, aprobado.');
    expect($s->sol_fecha_resolucion)->not->toBeNull();
});

test('rechazar una solicitud desde el dashboard la mueve a tratadas', function () {
    $tipo = tipoSolicitud();
    $est = estudiante();
    $s = solicitudPendienteSinHistorial($est, $tipo);

    $this->post(route('admin.dashboard.estado', ['id' => $s->sol_id, 'accion' => 'rechazar']), [
        'observacion' => 'Falta documentación.',
    ])->assertRedirect();

    $s->refresh();
    expect($s->sol_eso_id)->toBe(estado('rechazada')->eso_id);
    expect($s->historialEstados)->toHaveCount(1);
    expect($s->historialEstados->first()->hes_observaciones_comentarios)->toBe('Falta documentación.');
});

test('aprobar sin observación no rompe el historial', function () {
    $tipo = tipoSolicitud();
    $est = estudiante();
    $s = solicitudPendienteSinHistorial($est, $tipo);

    $this->post(route('admin.dashboard.estado', ['id' => $s->sol_id, 'accion' => 'aprobar']))
        ->assertRedirect();

    $s->refresh();
    expect($s->historialEstados->first()->hes_observaciones_comentarios)->toBeNull();
});
