<?php

use App\Models\Requisito;
use App\Models\Rol;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Reutiliza los helpers globales de AdminTratadasTest (admin(), estudiante(),
// catalogoTratadas(), estado(), ...). Cada archivo los declara una vez y Pest
// los carga todos juntos al correr la suite.

// ---------------------------------------------------------------
// Gestion de usuarios
// ---------------------------------------------------------------

test('el administrador lista la pantalla de gestion de usuarios', function () {
    $this->actingAs(admin())
        ->get(route('admin.usuarios.index'))
        ->assertOk();
});

test('un estudiante no accede a la gestion de usuarios', function () {
    $this->actingAs(estudiante())
        ->get(route('admin.usuarios.index'))
        ->assertForbidden();
});

test('la busqueda de usuarios filtra por nombre', function () {
    $this->actingAs(admin());
    $buscado = estudiante('V-20100001');

    $this->get(route('admin.usuarios.search', ['q' => $buscado->usu_primer_nombre]))
        ->assertOk()
        ->assertJsonFragment(['usu_numero_documento' => 'V-20100001']);
});

test('agregar por correo asciende a un estudiante existente', function () {
    $this->actingAs(admin());
    $est = estudiante('V-20200001');

    $this->post(route('admin.usuarios.agregar'), [
        'email' => $est->usu_correo_electronico,
        'rol' => Rol::ANALISTA,
    ])->assertRedirect();

    expect($est->refresh()->usu_rol)->toBe(Rol::ANALISTA);
});

test('agregar por correo avisa cuando no existe la persona', function () {
    $this->actingAs(admin());

    $this->post(route('admin.usuarios.agregar'), [
        'email' => 'nadie@correo.test',
        'rol' => Rol::ANALISTA,
    ])->assertSessionHas('error');

    expect(Usuario::where('usu_correo_electronico', 'nadie@correo.test')->exists())->toBeFalse();
});

test('no se puede degradar al unico administrador', function () {
    // Solo existe el admin de la sesion: intentar bajarlo de rol debe
    // devolver un aviso controlado, no un error 500 por RuntimeException.
    $unico = admin('V-20300001');
    $this->actingAs($unico);
    // No hay ningun otro administrador en la base.
    expect(Usuario::where('usu_rol', Rol::ADMINISTRADOR)->count())->toBe(1);

    $respuesta = $this->post(route('admin.usuarios.agregar'), [
        'email' => $unico->usu_correo_electronico,
        'rol' => Rol::TAQUILLERO,
    ]);

    $respuesta->assertRedirect();
    $respuesta->assertSessionHas('error');
    expect($unico->refresh()->usu_rol)->toBe(Rol::ADMINISTRADOR);
});

test('el update no puede degradar al unico administrador', function () {
    $unico = admin('V-20300002');
    $this->actingAs($unico);

    $respuesta = $this->put(route('admin.usuarios.update', $unico), [
        'usu_primer_nombre' => $unico->usu_primer_nombre,
        'usu_primer_apellido' => $unico->usu_primer_apellido,
        'usu_correo_electronico' => $unico->usu_correo_electronico,
        'usu_rol' => Rol::TAQUILLERO,
    ]);

    $respuesta->assertSessionHas('error');
    expect($unico->refresh()->usu_rol)->toBe(Rol::ADMINISTRADOR);
});

test('se puede degradar a un administrador cuando hay otro', function () {
    $uno = admin('V-20300003');
    $otro = admin('V-20300004');
    $this->actingAs($otro);

    $this->put(route('admin.usuarios.update', $uno), [
        'usu_primer_nombre' => $uno->usu_primer_nombre,
        'usu_primer_apellido' => $uno->usu_primer_apellido,
        'usu_correo_electronico' => $uno->usu_correo_electronico,
        'usu_rol' => Rol::ANALISTA,
    ])->assertRedirect(route('admin.usuarios.index'));

    expect($uno->refresh()->usu_rol)->toBe(Rol::ANALISTA);
});

test('no se puede eliminar al unico administrador', function () {
    $unico = admin('V-20300005');
    $this->actingAs($unico);

    $this->delete(route('admin.usuarios.destroy', $unico))
        ->assertSessionHas('error');

    expect(Usuario::find($unico->usu_id))->not->toBeNull();
});

test('desbloquear una cuenta la deja activa', function () {
    $this->actingAs(admin());
    $bloqueado = estudiante('V-20400001');
    $bloqueado->update(['usu_estado_cuenta' => 'bloqueado']);

    $this->post(route('admin.usuarios.unlock', $bloqueado))->assertRedirect();

    expect($bloqueado->refresh()->usu_estado_cuenta)->toBe('activo');
});

// ---------------------------------------------------------------
// CRUD de requisitos
// ---------------------------------------------------------------

test('el administrador lista los requisitos', function () {
    $this->actingAs(admin())
        ->get(route('admin.requisitos.index'))
        ->assertOk();
});

test('un estudiante no accede a los requisitos', function () {
    $this->actingAs(estudiante())
        ->get(route('admin.requisitos.index'))
        ->assertForbidden();
});

test('se crea un requisito y se asigna a un tramite como obligatorio', function () {
    $this->actingAs(admin());
    $tipo = tipoSolicitud('Tramite con requisito');

    $this->post(route('admin.requisitos.store'), [
        'req_nombre_requisito' => 'Fondo negro',
        'req_descripcion' => 'Foto con fondo negro.',
        'tipos' => [$tipo->tsi_id => 'on'],
        'obligatorios' => [$tipo->tsi_id => '1'],
    ])->assertRedirect(route('admin.requisitos.index'));

    $requisito = Requisito::where('req_nombre_requisito', 'Fondo negro')->firstOrFail();
    expect($requisito->tiposSolicitud)->toHaveCount(1);
    expect((bool) $requisito->tiposSolicitud->first()->pivot->tsr_es_obligatorio)->toBeTrue();
});

test('editar un requisito actualiza sus datos y asignaciones', function () {
    $this->actingAs(admin());
    $tipo = tipoSolicitud('Tramite editable');

    $requisito = Requisito::create(['req_nombre_requisito' => 'Original']);

    $this->put(route('admin.requisitos.update', $requisito), [
        'req_nombre_requisito' => 'Renombrado',
        'req_descripcion' => 'Nueva descripcion.',
        'tipos' => [$tipo->tsi_id => 'on'],
        'obligatorios' => [],
    ])->assertRedirect(route('admin.requisitos.index'));

    $requisito->refresh();
    expect($requisito->req_nombre_requisito)->toBe('Renombrado');
    expect($requisito->tiposSolicitud)->toHaveCount(1);
    expect((bool) $requisito->tiposSolicitud->first()->pivot->tsr_es_obligatorio)->toBeFalse();
});

test('se elimina un requisito', function () {
    $this->actingAs(admin());
    $requisito = Requisito::create(['req_nombre_requisito' => 'Temporal']);

    $this->delete(route('admin.requisitos.destroy', $requisito))
        ->assertRedirect(route('admin.requisitos.index'));

    expect(Requisito::find($requisito->req_id))->toBeNull();
});

// ---------------------------------------------------------------
// CRUD de tipos de solicitud
// ---------------------------------------------------------------

test('el administrador lista los tipos de solicitud', function () {
    $this->actingAs(admin())
        ->get(route('admin.tipos-solicitud.index'))
        ->assertOk();
});

test('un estudiante no accede a los tipos de solicitud', function () {
    $this->actingAs(estudiante())
        ->get(route('admin.tipos-solicitud.index'))
        ->assertForbidden();
});

test('se crea un tramite activo con requisitos', function () {
    $this->actingAs(admin());
    $requisito = Requisito::create(['req_nombre_requisito' => 'Cedula vigente']);

    $this->post(route('admin.tipos-solicitud.store'), [
        'tsi_nombre_tipo' => 'Constancia nueva',
        'tsi_descripcion' => 'Descripcion.',
        'tsi_tiempo_estimado_dias' => 3,
        'activar_ahora' => 1,
        'requisitos' => [$requisito->req_id => 'on'],
        'obligatorios' => [$requisito->req_id => '1'],
    ])->assertRedirect(route('admin.tipos-solicitud.index'));

    $tipo = TipoSolicitud::where('tsi_nombre_tipo', 'Constancia nueva')->firstOrFail();
    expect($tipo->tsi_estado_tipo)->toBe('activo');
    expect($tipo->requisitos)->toHaveCount(1);
});

test('se crea un tramite como borrador inactivo', function () {
    $this->actingAs(admin());

    $this->post(route('admin.tipos-solicitud.store'), [
        'tsi_nombre_tipo' => 'Tramite borrador',
    ])->assertRedirect();

    expect(TipoSolicitud::where('tsi_nombre_tipo', 'Tramite borrador')->firstOrFail()->tsi_estado_tipo)
        ->toBe('inactivo');
});

test('alternar estado enciende y apaga un tramite', function () {
    $this->actingAs(admin());
    $tipo = tipoSolicitud('Tramite alternable');
    $inicial = $tipo->tsi_estado_tipo;

    $this->patch(route('admin.tipos-solicitud.alternar-estado', $tipo->tsi_id))
        ->assertRedirect(route('admin.tipos-solicitud.index'));

    $esperado = $inicial === 'activo' ? 'inactivo' : 'activo';
    expect($tipo->refresh()->tsi_estado_tipo)->toBe($esperado);
});

test('no se elimina un tramite con solicitudes asociadas', function () {
    $this->actingAs(admin());
    $tipo = tipoSolicitud('Tramite usado');
    $est = estudiante('V-20500001');
    solicitudPendienteSinHistorial($est, $tipo);

    $this->delete(route('admin.tipos-solicitud.destroy', $tipo->tsi_id))
        ->assertSessionHas('error');

    expect(TipoSolicitud::find($tipo->tsi_id))->not->toBeNull();
});

test('se elimina un tramite sin solicitudes', function () {
    $this->actingAs(admin());
    $tipo = tipoSolicitud('Tramite sin uso');

    $this->delete(route('admin.tipos-solicitud.destroy', $tipo->tsi_id))
        ->assertRedirect(route('admin.tipos-solicitud.index'));

    expect(TipoSolicitud::find($tipo->tsi_id))->toBeNull();
});

// ---------------------------------------------------------------
// Solicitud de tramite por el estudiante
// ---------------------------------------------------------------

test('un estudiante solicita un tramite disponible y queda pendiente', function () {
    $est = estudiante('V-20600001');
    $this->actingAs($est);
    $tipo = tipoSolicitud('Tramite solicitable');
    $tipo->update(['tsi_estado_tipo' => 'activo', 'tsi_fecha_inicio' => null, 'tsi_fecha_fin' => null]);

    $this->post(route('user.tramites.store', $tipo->tsi_id), [
        'motivo' => 'Necesito la constancia para un tramite externo.',
    ])->assertRedirect(route('dashboard'));

    $solicitud = Solicitud::where('sol_usu_id', $est->usu_id)->firstOrFail();
    expect($solicitud->sol_motivo_detallado)->toBe('Necesito la constancia para un tramite externo.');
    expect($solicitud->estadoActual->eso_nombre_estado)->toBe('pendiente');
});

test('no se puede solicitar un tramite inactivo', function () {
    $est = estudiante('V-20600002');
    $this->actingAs($est);
    $tipo = tipoSolicitud('Tramite inactivo');
    $tipo->update(['tsi_estado_tipo' => 'inactivo']);

    $this->post(route('user.tramites.store', $tipo->tsi_id), [
        'motivo' => 'Intento fallido.',
    ])->assertRedirect(route('dashboard'));

    expect(Solicitud::where('sol_usu_id', $est->usu_id)->count())->toBe(0);
});

test('la solicitud exige un motivo', function () {
    $est = estudiante('V-20600003');
    $this->actingAs($est);
    $tipo = tipoSolicitud('Tramite con motivo');
    $tipo->update(['tsi_estado_tipo' => 'activo', 'tsi_fecha_inicio' => null, 'tsi_fecha_fin' => null]);

    $this->post(route('user.tramites.store', $tipo->tsi_id), ['motivo' => ''])
        ->assertSessionHasErrors('motivo');
});
