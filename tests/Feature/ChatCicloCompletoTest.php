<?php

use App\Models\ChatSoporte\HiloChat;
use App\Mail\NuevoMensajeSoporte;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

/*
 * Ciclo completo del chat de soporte, de punta a punta y con los dos roles.
 *
 * Los tests de cada sector comprueban su parte; este comprueba el TRAMO
 * COMPLETO: estudiante abre consulta, administrador la ve en su bandeja, la
 * reclama, responde (que ademas dispara el correo al estudiante) y propone el
 * cierre, y el estudiante lo confirma.
 *
 * El correo NO se falsa a proposito: en el navegador la llamada a Mail::send()
 * es real y, si algo therein falla (una relacion que no existe, una columna
 * vacia), el mensaje tampoco se guarda y el estudiante se queda sin respuesta.
 * Con Mail::fake() ese fallo pasaria inadvertido.
 */
uses(RefreshDatabase::class);

test('el estudiante abre una consulta y el administrador la ve en su bandeja', function () {
    $this->actingAs(estudiante())
        ->post(route('user.ayuda.chat.iniciar'), ['mch_cuerpo' => 'No puedo cargar la constancia.'])
        ->assertRedirect();

    $hilo = HiloChat::first();
    expect($hilo)->not->toBeNull();
    expect($hilo->hch_estado)->toBe('pendiente');
    expect($hilo->mensajes)->toHaveCount(1);

    // La bandeja del administrador tiene que listarla: si no, el chat existe
    // pero el personal no lo encuentra. La lista muestra quien pregunta y el
    // numero de ticket, no el texto del mensaje.
    $this->actingAs(admin())
        ->get(route('admin.chat.index'))
        ->assertOk()
        ->assertSee('#'.$hilo->hch_id)
        ->assertSee('Estudiante');
});

test('el administrador reclama, responde y propone el cierre', function () {
    $this->actingAs(estudiante())
        ->post(route('user.ayuda.chat.iniciar'), ['mch_cuerpo' => 'Necesito ayuda.']);

    $hilo = HiloChat::first();

    $this->actingAs(admin())
        ->post(route('admin.chat.reclamar', $hilo))
        ->assertRedirect();

    expect($hilo->fresh()->hch_id_admin)->not->toBeNull();

    // La respuesta del administrador es el paso que dispara el correo: si el
    // Mail::send() revienta, el mensaje no se guarda y el chat se queda mudo.
    $this->actingAs(admin())
        ->post(route('admin.chat.enviar', $hilo), ['mch_cuerpo' => 'Revisalo y me dices.'])
        ->assertRedirect();

    expect($hilo->fresh()->mensajes)->toHaveCount(2);

    // Proponer el cierre exige una etiqueta: es lo que el student's ve luego
    // como resumen de como se resolvio.
    $this->actingAs(admin())
        ->post(route('admin.chat.proponer-cierre', $hilo), [
            'etiqueta_tema' => 'Constancia de estudio',
        ])
        ->assertRedirect();

    expect($hilo->fresh()->hch_estado)->toBe('pendiente_cierre');
});

test('el estudiante ve la respuesta del administrador y confirma el cierre', function () {
    $this->actingAs(estudiante())
        ->post(route('user.ayuda.chat.iniciar'), ['mch_cuerpo' => 'Necesito ayuda.']);

    $hilo = HiloChat::first();

    $this->actingAs(admin())->post(route('admin.chat.reclamar', $hilo));
    $this->actingAs(admin())->post(route('admin.chat.enviar', $hilo), ['mch_cuerpo' => 'Revisalo.']);
    $this->actingAs(admin())->post(route('admin.chat.proponer-cierre', $hilo), [
        'etiqueta_tema' => 'Constancia de estudio',
    ]);

    // El mensaje del administrador tiene que verse en la pantalla del estudiante.
    $this->actingAs(estudiante())
        ->get(route('user.ayuda.chat.mostrar', $hilo))
        ->assertOk()
        ->assertSee('Revisalo.')
        ->assertSee('Necesito ayuda.');

    $this->actingAs(estudiante())
        ->post(route('user.ayuda.chat.confirmar', $hilo))
        ->assertRedirect();

    expect($hilo->fresh()->hch_estado)->toBe('cerrado');
});

test('alumno que no es el dueño del chat no lo abre', function () {
    $this->actingAs(estudiante())
        ->post(route('user.ayuda.chat.iniciar'), ['mch_cuerpo' => 'Necesito ayuda.']);

    $hilo = HiloChat::first();

    $otro = estudiante('V-20000009');

    $this->actingAs($otro)
        ->get(route('user.ayuda.chat.mostrar', $hilo))
        ->assertForbidden();
});

test('el correo al estudiante se envía solo cuando responde el administrador', function () {
    Mail::fake();

    $this->actingAs(estudiante())
        ->post(route('user.ayuda.chat.iniciar'), ['mch_cuerpo' => 'Necesito ayuda.']);

    // El mensaje del estudiante no genera correo: nadie espera uno.
    Mail::assertNothingSent();

    $hilo = HiloChat::first();

    $this->actingAs(admin())->post(route('admin.chat.enviar', $hilo), ['mch_cuerpo' => 'Revisalo.']);

    Mail::assertSent(NuevoMensajeSoporte::class);
});
