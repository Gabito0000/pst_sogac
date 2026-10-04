<?php

use App\Models\EstadoSolicitud;
use App\Models\HistorialCambio;
use App\Models\LapsoAcademico;
use App\Models\PreguntasFrecuentes;
use App\Models\Requisito;
use App\Models\Solicitud;
use App\Models\TipoDocumento;
use App\Models\TipoSolicitud;
use App\Models\Usuario;
use App\Services\RegistroCambios;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Crea un usuario con el catalogo de tipo de documento que exige usuarios.
 */
function cambiosUsuario(string $rol = 'admin', string $documento = 'V-10000001'): Usuario
{
    $tipoDocumento = TipoDocumento::firstOrCreate(
        ['tdo_abreviatura' => 'V'],
        ['tdo_nombre_documento' => 'Cédula de identidad'],
    );

    return Usuario::create([
        'usu_rol' => $rol,
        'usu_tdo_id' => $tipoDocumento->tdo_id,
        'usu_primer_nombre' => 'Patricia',
        'usu_primer_apellido' => 'Medina',
        'usu_numero_documento' => $documento,
        'usu_correo_electronico' => $documento.'@ejemplo.com',
        'usu_contrasena_hash' => bcrypt('password'),
        'usu_estado_cuenta' => 'activo',
        'usu_fecha_registro' => now(),
    ]);
}

/*
|--------------------------------------------------------------------------
| La bitacora se escribe sola
|--------------------------------------------------------------------------
*/

test('crear un registro lo deja anotado en la bitacora', function () {
    $admin = cambiosUsuario();

    $this->actingAs($admin);

    $requisito = Requisito::create([
        'req_nombre_requisito' => 'Cédula de identidad',
        'req_descripcion' => 'Vigente y con sello húmedo.',
        'req_formato_esperado' => 'PDF',
    ]);

    $cambio = HistorialCambio::query()
        ->where('hcm_entidad', 'Requisito')
        ->where('hcm_entidad_id', $requisito->req_id)
        ->where('hcm_accion', 'creo')
        ->sole();

    expect($cambio->hcm_usu_id)->toBe($admin->usu_id)
        ->and($cambio->autor_nombre)->toBe('Patricia Medina')
        ->and($cambio->entidad_etiqueta)->toBe('Requisito')
        ->and($cambio->diferencias)->toContain([
            'campo' => 'Nombre',
            'anterior' => '—',
            'nuevo' => 'Cédula de identidad',
        ]);
});

test('editar un registro guarda el valor anterior y el nuevo', function () {
    $this->actingAs(cambiosUsuario());

    $pregunta = PreguntasFrecuentes::create([
        'pregunta' => '¿Cómo pido una constancia?',
        'respuesta' => 'Desde la sección de trámites.',
    ]);

    // El servicio real edita sobre la instancia, no con una consulta de masas.
    PreguntasFrecuentes::where('id', $pregunta->id)->firstOrFail()
        ->update(['respuesta' => 'Desde el menú Trámites.']);

    $actualizacion = HistorialCambio::query()
        ->where('hcm_entidad', 'PreguntasFrecuentes')
        ->where('hcm_accion', 'actualizo')
        ->sole();

    expect($actualizacion->diferencias)->toBe([
        [
            'campo' => 'Respuesta',
            'anterior' => 'Desde la sección de trámites.',
            'nuevo' => 'Desde el menú Trámites.',
        ],
    ]);
});

test('los valores booleanos y las claves foraneas se guardan en texto', function () {
    $catalogo = cambiosCatalogo();

    $this->actingAs(cambiosUsuario());

    $tipo = TipoSolicitud::create([
        'tsi_nombre_tipo' => 'Cambio de carrera',
        'tsi_tiempo_estimado_dias' => 10,
        'tsi_requiere_aprobacion_especial' => false,
        'tsi_estado_tipo' => 'activo',
    ]);

    $tipo->update([
        'tsi_requiere_aprobacion_especial' => true,
        'tsi_tiempo_estimado_dias' => 15,
    ]);

    $actualizacion = HistorialCambio::query()
        ->where('hcm_entidad', 'TipoSolicitud')
        ->where('hcm_accion', 'actualizo')
        ->sole();

    $diferencias = collect($actualizacion->diferencias)->keyBy('campo');

    // El booleano se lee "No → Sí" y no "0 → 1".
    expect($diferencias['Requiere aprobación especial'])->toBe([
        'campo' => 'Requiere aprobación especial',
        'anterior' => 'No',
        'nuevo' => 'Sí',
    ])->and($diferencias['Tiempo estimado (días)'])->toBe([
        'campo' => 'Tiempo estimado (días)',
        'anterior' => '10',
        'nuevo' => '15',
    ]);

    // Y el alta de una solicitud guarda el nombre del tramite, no su clave.
    $solicitud = cambiosSolicitud(cambiosUsuario('estudiante', 'V-30000001'), $catalogo);

    $alta = $solicitud->cambios()->sole();

    expect(collect($alta->diferencias)->keyBy('campo')['Trámite']['nuevo'])
        ->toBe('Constancia de estudio')
        ->not->toBe((string) $catalogo['tipo']->tsi_id);
});

test('borrar un registro deja constancia de lo que era', function () {
    $this->actingAs(cambiosUsuario());

    $pregunta = PreguntasFrecuentes::create([
        'pregunta' => '¿Puedo editar una solicitud?',
        'respuesta' => 'No, abre un chat.',
    ]);

    PreguntasFrecuentes::where('id', $pregunta->id)->firstOrFail()->delete();

    $baja = HistorialCambio::query()->where('hcm_accion', 'elimino')->sole();

    expect($baja->entidad_etiqueta)->toBe('Pregunta frecuente')
        ->and(collect($baja->diferencias)->pluck('nuevo')->all())->toContain('¿Puedo editar una solicitud?');
});

test('la bitacora guarda el contexto de pantalla e IP', function () {
    $this->actingAs(cambiosUsuario());

    $this->post(route('admin.preguntas.store'), [
        'pregunta' => '¿Dónde veo el estado de mi trámite?',
        'respuesta' => 'En el historial de solicitudes.',
    ])->assertRedirect(route('admin.preguntas.index'));

    $cambio = HistorialCambio::query()->where('hcm_accion', 'creo')->sole();

    expect($cambio->hcm_ruta)->toBe('admin/preguntas')->not->toBeNull()
        ->and($cambio->hcm_ip)->not->toBeNull();
});

test('los cambios de una solicitud quedan colgados de ella', function () {
    $catalogo = cambiosCatalogo();

    $solicitud = cambiosSolicitud(cambiosUsuario('estudiante', 'V-30000001'), $catalogo);

    expect($solicitud->cambios)->toHaveCount(1)
        ->and($solicitud->cambios->first()->hcm_sol_id)->toBe($solicitud->sol_id);
});

test('sin registro no se escribe nada, y el interruptor se restaura', function () {
    $this->actingAs(cambiosUsuario());

    RegistroCambios::sinRegistro(function () {
        PreguntasFrecuentes::create(['pregunta' => 'Sin bitácora', 'respuesta' => 'Nada']);
    });

    expect(HistorialCambio::query()->count())->toBe(0)
        ->and(RegistroCambios::activo())->toBeTrue();
});

test('un texto largo editado solo al final sí se registra', function () {
    $this->actingAs(cambiosUsuario());

    // Más de 200 caracteres para que el recorte de la bitácora entre en juego:
    // si la comparación se hiciera sobre el texto recortado, los 200 primeros
    // caracteres serían idénticos y el cambio se descartaría.
    $largo = str_repeat('Explica el motivo con detalle. ', 12);

    $pregunta = PreguntasFrecuentes::create([
        'pregunta' => '¿Puedo editar el motivo?',
        'respuesta' => $largo,
    ]);

    PreguntasFrecuentes::where('id', $pregunta->id)->firstOrFail()
        ->update(['respuesta' => $largo.'Ahora ya no.']);

    $cambio = HistorialCambio::query()
        ->where('hcm_accion', 'actualizo')
        ->sole();

    $diferencia = $cambio->diferencias[0];

    expect($diferencia['campo'])->toBe('Respuesta')
        ->and($diferencia['nuevo'])->not->toBe($diferencia['anterior'])
        // El recorte tiene que sacar a la luz el final del texto, que es donde
        // está la edición.
        ->and($diferencia['nuevo'])->toContain('Ahora ya no.');
});

test('un texto sin cambios reales no genera diferencias', function () {
    $this->actingAs(cambiosUsuario());

    $pregunta = PreguntasFrecuentes::create([
        'pregunta' => '¿Y si no cambia nada?',
        'respuesta' => 'La misma respuesta de siempre.',
    ]);

    PreguntasFrecuentes::where('id', $pregunta->id)->firstOrFail()->touch();

    expect(HistorialCambio::query()->where('hcm_accion', 'actualizo')->count())->toBe(0);
});

test('un nombre renombrado se refleja en el cambio siguiente', function () {
    $catalogo = cambiosCatalogo();
    $catalogo['tipo']->update(['tsi_nombre_tipo' => 'Constancia de estudios (nuevo)']);

    $solicitud = cambiosSolicitud(cambiosUsuario('estudiante', 'V-30000001'), $catalogo);

    $alta = $solicitud->cambios()->sole();

    // Sin esto, el nombre resuelto se cacheaba entre pruebas y en procesos de
    // larga duración, y la bitácora seguiría mostrando el nombre viejo.
    expect(collect($alta->diferencias)->keyBy('campo')['Trámite']['nuevo'])
        ->toBe('Constancia de estudios (nuevo)');
});

/*
|--------------------------------------------------------------------------
| Pantalla de la bitacora
|--------------------------------------------------------------------------
*/

test('el administrador ve la bitacora con sus filtros', function () {
    $this->actingAs(cambiosUsuario());

    PreguntasFrecuentes::create(['pregunta' => '¿Cuándo se abre la oficina?', 'respuesta' => 'En la Oficina.']);

    Requisito::create(['req_nombre_requisito' => 'Partida de nacimiento']);

    $this->get(route('admin.cambios.index'))
        ->assertOk()
        ->assertSee('Historial de cambios')
        ->assertSee('¿Cuándo se abre la oficina?')
        ->assertSee('Partida de nacimiento');

    $this->get(route('admin.cambios.index', ['entidad' => 'Requisito']))
        ->assertOk()
        ->assertSee('Partida de nacimiento')
        ->assertDontSee('¿Cuándo se abre la oficina?');

    $this->get(route('admin.cambios.index', ['accion' => 'elimino']))
        ->assertOk()
        ->assertSee('Ningún cambio coincide con el filtro');
});

test('la bitacora filtra por autor y por rango de fechas', function () {
    $admin = cambiosUsuario();
    $otro = cambiosUsuario('admin', 'V-10000002');

    $this->actingAs($admin);
    PreguntasFrecuentes::create(['pregunta' => 'Del primer admin', 'respuesta' => 'Sí']);

    $this->actingAs($otro);
    PreguntasFrecuentes::create(['pregunta' => 'Del segundo admin', 'respuesta' => 'También']);

    $this->get(route('admin.cambios.index', ['autor' => $otro->usu_id]))
        ->assertOk()
        ->assertSee('Del segundo admin')
        ->assertDontSee('Del primer admin');

    $this->get(route('admin.cambios.index', [
        'desde' => now()->format('Y-m-d'),
        'hasta' => now()->format('Y-m-d'),
    ]))
        ->assertOk()
        ->assertSee('Del segundo admin');

    // Un rango al revés se avisa en vez de devolver una lista vacía sin explicación.
    $this->get(route('admin.cambios.index', [
        'desde' => now()->format('Y-m-d'),
        'hasta' => now()->subWeek()->format('Y-m-d'),
    ]))
        ->assertOk()
        ->assertSee('El rango de fechas está al revés');
});

test('la bitacora vacía explica cuando empieza a llenarse', function () {
    $this->actingAs(cambiosUsuario())
        ->get(route('admin.cambios.index'))
        ->assertOk()
        ->assertSee('Todavía no hay cambios registrados');
});

test('los cambios sin autor (cargas de datos) también se ven', function () {
    $this->actingAs(cambiosUsuario());

    HistorialCambio::create([
        'hcm_usu_id' => null,
        'hcm_entidad' => 'Requisito',
        'hcm_accion' => 'actualizo',
        'hcm_resumen' => 'Carga inicial de requisitos',
        'hcm_cambios' => [],
        'hcm_fecha' => now(),
    ]);

    // Con un join interior sobre usuarios, los cambios sin autor desaparecían
    // del listado: el contador del pie acababa menores que las cifras de arriba.
    $this->get(route('admin.cambios.index'))
        ->assertOk()
        ->assertSee('Carga inicial de requisitos')
        ->assertSee('Sistema');
});

test('la bitacora se exporta a CSV con los mismos filtros del listado', function () {
    $this->actingAs(cambiosUsuario());

    PreguntasFrecuentes::create(['pregunta' => '¿Se entrega?', 'respuesta' => 'En dos días.']);
    Requisito::create(['req_nombre_requisito' => 'Foto del carnet']);

    $respuesta = $this->get(route('admin.cambios.exportar', ['entidad' => 'PreguntasFrecuentes']));

    $respuesta->assertOk();

    // El nombre lleva la hora de la exportación, así que se comprueba el prefijo.
    expect($respuesta->headers->get('content-disposition'))
        ->toContain('historial-cambios-')
        ->toContain('.csv');

    $csv = $respuesta->streamedContent();

    expect($csv)->toContain('¿Se entrega?', 'En dos días.')
        ->and($csv)->not->toContain('Foto del carnet');
});

test('un estudiante no puede ver la bitacora', function () {
    $estudiante = cambiosUsuario('estudiante', 'V-30000001');

    $this->actingAs($estudiante)->get(route('admin.cambios.index'))->assertForbidden();
    $this->actingAs($estudiante)->get(route('admin.cambios.exportar'))->assertForbidden();
});

test('la bitacora exige sesión', function () {
    $this->get(route('admin.cambios.index'))->assertRedirect(route('login'));
});

/*
|--------------------------------------------------------------------------
| Utilidades de los tests
|--------------------------------------------------------------------------
*/

function cambiosCatalogo(): array
{
    TipoDocumento::firstOrCreate(
        ['tdo_abreviatura' => 'V'],
        ['tdo_nombre_documento' => 'Cédula de identidad'],
    );

    return [
        'estado' => EstadoSolicitud::create([
            'eso_nombre_estado' => 'pendiente',
            'eso_descripcion' => 'En espera.',
        ]),
        'periodo' => LapsoAcademico::create([
            'lac_id_lapso' => '2026-1',
            'lac_fecha_inicio' => now()->startOfYear()->toDateString(),
            'lac_fecha_cierre' => now()->addMonths(5)->toDateString(),
            'lac_estado_lapso' => 'activo',
        ]),
        'tipo' => TipoSolicitud::create([
            'tsi_nombre_tipo' => 'Constancia de estudio',
            'tsi_estado_tipo' => 'activo',
        ]),
    ];
}

function cambiosSolicitud(Usuario $estudiante, array $catalogo): Solicitud
{
    return Solicitud::create([
        'sol_usu_id' => $estudiante->usu_id,
        'sol_tsi_id' => $catalogo['tipo']->tsi_id,
        'sol_lac_id' => $catalogo['periodo']->lac_id,
        'sol_eso_id' => $catalogo['estado']->eso_id,
        'sol_id_seguimiento' => 'SOL-'.uniqid(),
        'sol_motivo_detallado' => 'Motivo de prueba.',
        'sol_fecha_creacion' => now(),
    ]);
}
