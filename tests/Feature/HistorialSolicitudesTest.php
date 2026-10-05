<?php

use App\Models\EstadoSolicitud;
use App\Models\HistorialEstadoSolicitud;
use App\Models\LapsoAcademico;
use App\Models\Solicitud;
use App\Models\TipoDocumento;
use App\Models\TipoSolicitud;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Catalogo minimo para poder crear solicitudes.
 */
function historialCatalogo(): array
{
    $documento = TipoDocumento::firstOrCreate(
        ['tdo_abreviatura' => 'V'],
        ['tdo_nombre_documento' => 'Cédula de identidad'],
    );

    $estados = collect(['pendiente', 'aprobada', 'rechazada'])
        ->mapWithKeys(fn (string $nombre) => [
            $nombre => EstadoSolicitud::firstOrCreate(
                ['eso_nombre_estado' => $nombre],
                ['eso_descripcion' => 'Estado de prueba.'],
            )->eso_id,
        ]);

    return [
        'tipo_documento' => $documento,
        'periodo' => LapsoAcademico::firstOrCreate(
            ['lac_id_lapso' => '2026-1'],
            [
                'lac_fecha_inicio' => now()->startOfYear()->toDateString(),
                'lac_fecha_cierre' => now()->addMonths(5)->toDateString(),
                'lac_estado_lapso' => 'activo',
            ],
        ),
        'constancia' => TipoSolicitud::firstOrCreate(
            ['tsi_nombre_tipo' => 'Constancia de estudio'],
            ['tsi_estado_tipo' => 'activo', 'tsi_tiempo_estimado_dias' => 5],
        ),
        'cambio' => TipoSolicitud::firstOrCreate(
            ['tsi_nombre_tipo' => 'Cambio de carrera'],
            ['tsi_estado_tipo' => 'activo'],
        ),
        'estados' => $estados,
    ];
}

function historialUsuario(string $rol = 'estudiante', string $documento = 'V-30000001'): Usuario
{
    $catalogo = historialCatalogo();

    return Usuario::create([
        'usu_rol' => $rol,
        'usu_tdo_id' => $catalogo['tipo_documento']->tdo_id,
        'usu_primer_nombre' => 'Ana',
        'usu_primer_apellido' => 'Pérez',
        'usu_numero_documento' => $documento,
        'usu_correo_electronico' => $documento.'@ejemplo.com',
        'usu_contrasena_hash' => bcrypt('password'),
        'usu_estado_cuenta' => 'activo',
        'usu_fecha_registro' => now(),
    ]);
}

function historialSolicitud(Usuario $estudiante, string $estado = 'pendiente', array $extra = []): Solicitud
{
    $catalogo = historialCatalogo();

    return Solicitud::create(array_merge([
        'sol_usu_id' => $estudiante->usu_id,
        'sol_tsi_id' => $catalogo['constancia']->tsi_id,
        'sol_lac_id' => $catalogo['periodo']->lac_id,
        'sol_eso_id' => $catalogo['estados'][$estado],
        'sol_id_seguimiento' => 'SOL-'.uniqid(),
        'sol_motivo_detallado' => 'Necesito el certificado para una beca de intercambio.',
        'sol_prioridad' => 'normal',
        'sol_fecha_creacion' => now()->subDays(3),
    ], $extra));
}

/*
|--------------------------------------------------------------------------
| Listado del estudiante
|--------------------------------------------------------------------------
*/

test('el historial lista solo las solicitudes del estudiante', function () {
    $propia = historialSolicitud(historialUsuario());
    $ajena = historialSolicitud(historialUsuario('estudiante', 'V-30000002'));

    $this->actingAs($propia->usuario)
        ->get(route('user.historial.index'))
        ->assertOk()
        ->assertSee($propia->sol_id_seguimiento)
        ->assertDontSee($ajena->sol_id_seguimiento);
});

test('el historial filtra por estado, tramite, texto y rango de fechas', function () {
    $catalogo = historialCatalogo();
    $estudiante = historialUsuario();

    $constancia = historialSolicitud($estudiante, 'pendiente', [
        'sol_id_seguimiento' => 'SOL-AAAA1111',
        'sol_motivo_detallado' => 'Constancia para la beca de intercambio',
    ]);

    $cambio = Solicitud::create([
        'sol_usu_id' => $estudiante->usu_id,
        'sol_tsi_id' => $catalogo['cambio']->tsi_id,
        'sol_lac_id' => $catalogo['periodo']->lac_id,
        'sol_eso_id' => $catalogo['estados']['aprobada'],
        'sol_id_seguimiento' => 'SOL-BBBB2222',
        'sol_motivo_detallado' => 'Cambio de carrera al semester 2026-2',
        'sol_fecha_creacion' => now()->subMonths(3),
        'sol_fecha_resolucion' => now()->subMonths(3),
    ]);

    $this->actingAs($estudiante);

    $this->get(route('user.historial.index', ['estado' => 'aprobada']))
        ->assertOk()
        ->assertSee($cambio->sol_id_seguimiento)
        ->assertDontSee($constancia->sol_id_seguimiento);

    $this->get(route('user.historial.index', ['tipo' => $catalogo['cambio']->tsi_id]))
        ->assertOk()
        ->assertSee($cambio->sol_id_seguimiento)
        ->assertDontSee($constancia->sol_id_seguimiento);

    $this->get(route('user.historial.index', ['q' => 'SOL-AAAA']))
        ->assertOk()
        ->assertSee($constancia->sol_id_seguimiento)
        ->assertDontSee($cambio->sol_id_seguimiento);

    // Rango de fechas: la de hace tres meses se queda fuera.
    $this->get(route('user.historial.index', [
        'desde' => now()->subMonth()->format('Y-m-d'),
        'hasta' => now()->format('Y-m-d'),
    ]))
        ->assertOk()
        ->assertSee($constancia->sol_id_seguimiento)
        ->assertDontSee($cambio->sol_id_seguimiento);
});

test('un filtro con formato invalido no rompe el historial', function () {
    $estudiante = historialUsuario();
    historialSolicitud($estudiante);

    $this->actingAs($estudiante)
        ->get(route('user.historial.index', ['desde' => 'no-es-una-fecha', 'hasta' => '???']))
        ->assertOk()
        ->assertDontSee('Whoops');
});

test('el historial ofrece todos los filtros y avisa cuando no hay coincidencias', function () {
    $estudiante = historialUsuario();
    historialSolicitud($estudiante);

    $this->actingAs($estudiante)
        ->get(route('user.historial.index', ['q' => 'nada-de-esto']))
        ->assertOk()
        ->assertSee('Ninguna solicitud coincide con el filtro');
});

test('sin solicitudes el historial invita a empezar', function () {
    $this->actingAs(historialUsuario())
        ->get(route('user.historial.index'))
        ->assertOk()
        ->assertSee('Todavía no has enviado ninguna solicitud');
});

/*
|--------------------------------------------------------------------------
| Ficha de detalle
|--------------------------------------------------------------------------
*/

test('la ficha muestra el recorrido y los cambios de la solicitud', function () {
    $catalogo = historialCatalogo();
    $admin = historialUsuario('administrador', 'V-10000001');
    $estudiante = historialUsuario();

    $solicitud = historialSolicitud($estudiante);

    HistorialEstadoSolicitud::create([
        'hes_sol_id' => $solicitud->sol_id,
        'hes_usu_id_responsable' => $admin->usu_id,
        'hes_eso_id_anterior' => $catalogo['estados']['pendiente'],
        'hes_eso_id_nuevo' => $catalogo['estados']['aprobada'],
        'hes_observaciones_comentarios' => 'Se verificó la documentación.',
        'hes_fecha_cambio' => now()->subDay(),
    ]);

    $solicitud->update(['sol_eso_id' => $catalogo['estados']['aprobada']]);

    $this->actingAs($estudiante)
        ->get(route('user.historial.show', $solicitud))
        ->assertOk()
        ->assertSee('Recorrido de la solicitud')
        ->assertSee('Se verificó la documentación')
        ->assertSee('Cambios registrados')
        // El estado se resuelve a texto, no a identificador.
        ->assertSee('aprobada')
        ->assertDontSee('sol_eso_id');
});

test('el estudiante no puede abrir la ficha de otro estudiante', function () {
    $ajena = historialSolicitud(historialUsuario('estudiante', 'V-30000002'));
    $otro = historialUsuario('estudiante', 'V-30000003');

    $this->actingAs($otro)
        ->get(route('user.historial.show', $ajena))
        ->assertForbidden();
});

test('un estudiante no puede abrir una solicitud que no existe', function () {
    $this->actingAs(historialUsuario())
        ->get(route('user.historial.show', 999))
        ->assertNotFound();
});

test('el historial exige sesion', function () {
    $this->get(route('user.historial.index'))->assertRedirect(route('login'));

    $solicitud = historialSolicitud(historialUsuario());

    $this->get(route('user.historial.show', $solicitud))->assertRedirect(route('login'));
});
