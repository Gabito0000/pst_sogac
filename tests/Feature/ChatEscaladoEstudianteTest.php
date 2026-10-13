<?php

use App\Models\ChatSoporte\HiloChat;
use App\Models\Rol;
use App\Models\TipoDocumento;
use App\Models\Usuario;
/*
 * El estudiante pide que un administrador atienda su consulta. Ese camino tiene
 * que terminar de vuelta en SU chat, no en la bandeja del administrador, y la
 * peticion tiene que aparecer en el apartado de solicitudes del admin.
 *
 * uses() con RefreshDatabase queda en el archivo porque las pruebas de este
 * modulo comparten el mismo esqueleto de datos.
 */

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function usuarioDeChat(string $rol, string $documento): Usuario
{
    $tipo = TipoDocumento::firstOrCreate(
        ['tdo_abreviatura' => 'V'],
        ['tdo_nombre_documento' => 'Cédula de Identidad']
    );

    return Usuario::create([
        'usu_rol' => $rol,
        'usu_tdo_id' => $tipo->tdo_id,
        'usu_primer_nombre' => 'Ana',
        'usu_primer_apellido' => 'Pérez',
        'usu_numero_documento' => $documento,
        'usu_correo_electronico' => mb_strtolower($rol.$documento).'@ejemplo.com',
        'usu_contrasena_hash' => bcrypt('password'),
        'usu_estado_cuenta' => 'activo',
        'usu_fecha_registro' => now(),
    ]);
}

function chatDe(Usuario $dueno, string $estado = 'activo'): HiloChat
{
    return HiloChat::create([
        'hch_id_usuario' => $dueno->usu_id,
        'hch_estado' => $estado,
        'hch_nivel_atencion' => 1,
    ]);
}

it('el estudiante vuelve a su propio chat tras solicitar el escalado', function () {
    $estudiante = usuarioDeChat(Rol::ESTUDIANTE, '10000001');

    $hilo = chatDe($estudiante, 'activo');

    $respuesta = test()->actingAs($estudiante)
        ->from(route('user.ayuda.chat.mostrar', $hilo->hch_id))
        ->post(route('user.ayuda.chat.escalar', $hilo->hch_id));

    // Antes de arreglarlo caia en admin.chat.index y el estudiante recibia un
    // 403 de "Acceso restringido al personal administrativo".
    $respuesta->assertRedirect(route('user.ayuda.chat.mostrar', $hilo->hch_id));
    $respuesta->assertSessionHasNoErrors();
});

it('el estudiante no puede terminar en la bandeja del administrador', function () {
    $estudiante = usuarioDeChat(Rol::ESTUDIANTE, '10000002');

    $hilo = chatDe($estudiante, 'activo');

    $respuesta = test()->actingAs($estudiante)
        ->post(route('user.ayuda.chat.escalar', $hilo->hch_id));

    expect($respuesta->headers->get('Location'))->not->toContain('/admin/chat');
});

it('el estudiante no puede escalar el chat de otro', function () {
    $estudiante = usuarioDeChat(Rol::ESTUDIANTE, '10000003');
    $otro = usuarioDeChat(Rol::ESTUDIANTE, '10000004');

    $hiloAjeno = chatDe($otro, 'activo');

    test()->actingAs($estudiante)
        ->from(route('user.ayuda.chat.mostrar', $hiloAjeno->hch_id))
        ->post(route('user.ayuda.chat.escalar', $hiloAjeno->hch_id))
        ->assertRedirect();

    expect($hiloAjeno->fresh()->hch_estado)->toBe('activo');
});

it('la solicitud queda esperando al administrador y no se aplica sola', function () {
    $estudiante = usuarioDeChat(Rol::ESTUDIANTE, '10000005');

    $hilo = chatDe($estudiante, 'activo');

    test()->actingAs($estudiante)
        ->post(route('user.ayuda.chat.escalar', $hilo->hch_id));

    $hilo->refresh();

    expect($hilo->hch_estado)->toBe('pending_escalation')
        // El nivel NO sube solo: eso lo decide el administrador.
        ->and($hilo->hch_nivel_atencion)->toBe(1);
});

it('el administrador ve la solicitud en su bandeja', function () {
    $estudiante = usuarioDeChat(Rol::ESTUDIANTE, '10000006');
    $admin = usuarioDeChat(Rol::ADMINISTRADOR, '10000007');

    $hilo = chatDe($estudiante, 'activo');

    test()->actingAs($estudiante)->post(route('user.ayuda.chat.escalar', $hilo->hch_id));

    $respuesta = test()->actingAs($admin)->get(route('admin.chat.index'));

    $respuesta->assertOk();
    $respuesta->assertSee('Solicitan un administrador');
    $respuesta->assertViewHas('solicitudesEscalado');
    expect($respuesta->viewData('solicitudesEscalado')->pluck('hch_id')->all())
        ->toContain($hilo->hch_id);
});

it('un taquillero no puede aprobar el escalado, solo el administrador', function () {
    $estudiante = usuarioDeChat(Rol::ESTUDIANTE, '10000008');
    $taquillero = usuarioDeChat(Rol::TAQUILLERO, '10000009');

    $hilo = chatDe($estudiante, 'activo');

    test()->actingAs($estudiante)->post(route('user.ayuda.chat.escalar', $hilo->hch_id));

    test()->actingAs($taquillero)
        ->post(route('admin.chat.aprobar-escalado', $hilo->hch_id))
        ->assertForbidden();

    expect($hilo->fresh()->hch_nivel_atencion)->toBe(1);
});

it('el administrador aprueba y ahi si sube de nivel', function () {
    $estudiante = usuarioDeChat(Rol::ESTUDIANTE, '10000010');
    $admin = usuarioDeChat(Rol::ADMINISTRADOR, '10000011');

    $hilo = chatDe($estudiante, 'activo');

    test()->actingAs($estudiante)->post(route('user.ayuda.chat.escalar', $hilo->hch_id));

    test()->actingAs($admin)
        ->post(route('admin.chat.aprobar-escalado', $hilo->hch_id))
        ->assertSessionHasNoErrors();

    $hilo->refresh();

    expect($hilo->hch_estado)->toBe('pendiente')
        ->and($hilo->hch_nivel_atencion)->toBe(2)
        ->and($hilo->hch_id_admin)->toBeNull();
});

it('al rechazar el escalado el chat vuelve a su estado y sin subir de nivel', function () {
    $estudiante = usuarioDeChat(Rol::ESTUDIANTE, '10000012');
    $admin = usuarioDeChat(Rol::ADMINISTRADOR, '10000013');

    $hilo = chatDe($estudiante, 'activo');

    test()->actingAs($estudiante)->post(route('user.ayuda.chat.escalar', $hilo->hch_id));

    test()->actingAs($admin)
        ->post(route('admin.chat.rechazar-escalado', $hilo->hch_id), [
            'motivo' => 'Se resuelve en taquillería.',
        ])
        ->assertSessionHasNoErrors();

    $hilo->refresh();

    expect($hilo->hch_estado)->toBe('pendiente')
        ->and($hilo->hch_nivel_atencion)->toBe(1);
});

it('el analista tambien ve las solicitudes de escalado', function () {
    $estudiante = usuarioDeChat(Rol::ESTUDIANTE, '10000015');
    $analista = usuarioDeChat(Rol::ANALISTA, '10000016');

    $hilo = chatDe($estudiante, 'activo');

    test()->actingAs($estudiante)->post(route('user.ayuda.chat.escalar', $hilo->hch_id));

    $respuesta = test()->actingAs($analista)->get(route('admin.chat.index'));

    $respuesta->assertOk();
    $respuesta->assertSee('Solicitan un administrador');
    expect($respuesta->viewData('solicitudesEscalado')->pluck('hch_id')->all())
        ->toContain($hilo->hch_id);
});

it('el taquillero NO ve las solicitudes de escalado', function () {
    $estudiante = usuarioDeChat(Rol::ESTUDIANTE, '10000017');
    $taquillero = usuarioDeChat(Rol::TAQUILLERO, '10000018');

    $hilo = chatDe($estudiante, 'activo');

    test()->actingAs($estudiante)->post(route('user.ayuda.chat.escalar', $hilo->hch_id));

    $respuesta = test()->actingAs($taquillero)->get(route('admin.chat.index'));

    $respuesta->assertOk();
    $respuesta->assertDontSee('Solicitan un administrador');
    expect($respuesta->viewData('solicitudesEscalado'))->toBeEmpty();
});

it('el analista ve la peticion pero no puede decidir sobre ella', function () {
    $estudiante = usuarioDeChat(Rol::ESTUDIANTE, '10000019');
    $analista = usuarioDeChat(Rol::ANALISTA, '10000020');

    $hilo = chatDe($estudiante, 'activo');

    test()->actingAs($estudiante)->post(route('user.ayuda.chat.escalar', $hilo->hch_id));

    // Le aparece el bloque, pero sin los botones de decidir.
    test()->actingAs($analista)
        ->get(route('admin.chat.index'))
        ->assertOk()
        ->assertSee('Solicitan un administrador')
        ->assertDontSee('Aprobar');

    test()->actingAs($analista)
        ->post(route('admin.chat.aprobar-escalado', $hilo->hch_id))
        ->assertForbidden();

    expect($hilo->fresh()->hch_nivel_atencion)->toBe(1);
});

it('el estudiante ve que su consulta quedo esperando al administrador', function () {
    $estudiante = usuarioDeChat(Rol::ESTUDIANTE, '10000014');

    $hilo = chatDe($estudiante, 'activo');

    test()->actingAs($estudiante)
        ->post(route('user.ayuda.chat.escalar', $hilo->hch_id));

    test()->actingAs($estudiante)
        ->get(route('user.ayuda.chat.mostrar', $hilo->hch_id))
        ->assertOk()
        ->assertSee('administrador');
});
