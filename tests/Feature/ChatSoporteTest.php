<?php

use App\Models\ChatSoporte\HiloChat;
use App\Models\ChatSoporte\MensajeChat;
use App\Models\TipoDocumento;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

/**
 * El chat de soporte y las preguntas frecuentes son sectores separados: estas
 * pruebas fijan que las rutas, las plantillas y los permisos de cada uno no se
 * mezclen entre sí.
 */
function chatUsuario(string $rol, string $numeroDocumento): Usuario
{
    $documento = TipoDocumento::firstOrCreate(
        ['tdo_abreviatura' => 'V'],
        ['tdo_nombre_documento' => 'Cédula de Identidad'],
    );

    return Usuario::create([
        'usu_rol' => $rol,
        'usu_tdo_id' => $documento->tdo_id,
        'usu_primer_nombre' => 'Persona',
        'usu_primer_apellido' => $rol,
        'usu_numero_documento' => $numeroDocumento,
        'usu_correo_electronico' => $numeroDocumento.'@ejemplo.com',
        'usu_contrasena_hash' => bcrypt('password'),
        'usu_estado_cuenta' => 'activo',
        'usu_fecha_registro' => now(),
    ]);
}

function chatEstudiante(string $documento = 'V-30000001'): Usuario
{
    return chatUsuario('estudiante', $documento);
}

function chatAdmin(string $documento = 'V-30000100'): Usuario
{
    return chatUsuario('admin', $documento);
}

/**
 * Crea un hilo con un primer mensaje del estudiante.
 */
function chatHilo(Usuario $estudiante, string $estado = 'pendiente', ?Usuario $admin = null): HiloChat
{
    $hilo = HiloChat::create([
        'hch_id_usuario' => $estudiante->usu_id,
        'hch_id_admin' => $admin?->usu_id,
        'hch_estado' => $estado,
    ]);

    MensajeChat::create([
        'mch_id_hilo' => $hilo->hch_id,
        'mch_id_remitente' => $estudiante->usu_id,
        'mch_cuerpo' => 'No puedo descargar la constancia.',
    ]);

    return $hilo;
}

/*
|--------------------------------------------------------------------------
| Rutas separadas
|--------------------------------------------------------------------------
*/

test('las preguntas frecuentes no cuelgan del prefijo del chat', function () {
    expect(route('user.ayuda.preguntas'))->not->toStartWith(route('user.ayuda.chat.index'));
});

test('un visitante es enviado al login al pedir el chat', function () {
    $this->get(route('user.ayuda.chat.index'))->assertRedirect(route('login'));
});

test('un estudiante no puede abrir la bandeja de chats del administrador', function () {
    $this->actingAs(chatEstudiante())
        ->get(route('admin.chat.index'))
        ->assertForbidden();
});

test('un estudiante no puede abrir un chat que no es suyo', function () {
    $ajeno = chatEstudiante('V-30000002');
    $hilo = chatHilo($ajeno);

    $this->actingAs(chatEstudiante())
        ->get(route('user.ayuda.chat.mostrar', $hilo->hch_id))
        ->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| Bandeja del estudiante
|--------------------------------------------------------------------------
*/

test('la bandeja del estudiante muestra su consulta abierta', function () {
    $estudiante = chatEstudiante();
    $hilo = chatHilo($estudiante, 'activo', chatAdmin());

    $this->actingAs($estudiante)
        ->get(route('user.ayuda.chat.index'))
        ->assertOk()
        ->assertSee('Consulta #'.$hilo->hch_id)
        ->assertSee('En atención')
        ->assertSee(route('user.ayuda.chat.mostrar', $hilo->hch_id), false);
});

test('la bandeja del estudiante no ofrece abrir una segunda consulta', function () {
    // El servicio impide tener dos hilos abiertos: la vista no debe ofrecerlo.
    $estudiante = chatEstudiante();
    chatHilo($estudiante, 'pendiente');

    $this->actingAs($estudiante)
        ->get(route('user.ayuda.chat.index'))
        ->assertOk()
        ->assertDontSee('+ Nueva consulta');
});

test('el estudiante ve su propia conversación con el mensaje enviado', function () {
    $estudiante = chatEstudiante();
    $hilo = chatHilo($estudiante, 'activo', chatAdmin());

    $this->actingAs($estudiante)
        ->get(route('user.ayuda.chat.mostrar', $hilo->hch_id))
        ->assertOk()
        ->assertSee('No puedo descargar la constancia.')
        ->assertSee('Tú');
});

test('un chat pendiente de cierre ofrece confirmar o rechazar', function () {
    $estudiante = chatEstudiante();
    $hilo = chatHilo($estudiante, 'pendiente_cierre', chatAdmin());

    $this->actingAs($estudiante)
        ->get(route('user.ayuda.chat.mostrar', $hilo->hch_id))
        ->assertOk()
        ->assertSee(route('user.ayuda.chat.confirmar', $hilo->hch_id), false)
        ->assertSee(route('user.ayuda.chat.rechazar', $hilo->hch_id), false);
});

test('un chat cerrado no ofrece el formulario de respuesta', function () {
    $estudiante = chatEstudiante();
    $hilo = chatHilo($estudiante, 'cerrado', chatAdmin());

    $this->actingAs($estudiante)
        ->get(route('user.ayuda.chat.mostrar', $hilo->hch_id))
        ->assertOk()
        ->assertSee('Esta consulta se cerró')
        ->assertDontSee(route('user.ayuda.chat.enviar', $hilo->hch_id), false);
});

test('el estudiante confirma el cierre propuesto', function () {
    $estudiante = chatEstudiante();
    $hilo = chatHilo($estudiante, 'pendiente_cierre', chatAdmin());

    $this->actingAs($estudiante)
        ->post(route('user.ayuda.chat.confirmar', $hilo->hch_id))
        ->assertSessionHas('success');

    expect($hilo->refresh()->hch_estado)->toBe('cerrado');
});

test('el estudiante rechaza el cierre y el chat vuelve a estar activo', function () {
    $estudiante = chatEstudiante();
    $hilo = chatHilo($estudiante, 'pendiente_cierre', chatAdmin());

    $this->actingAs($estudiante)
        ->post(route('user.ayuda.chat.rechazar', $hilo->hch_id))
        ->assertSessionHas('success');

    expect($hilo->refresh()->hch_estado)->toBe('activo');
});

/*
|--------------------------------------------------------------------------
| Bandeja del administrador
|--------------------------------------------------------------------------
*/

test('la bandeja del administrador separa los tickets sin reclamar de los que atiende', function () {
    $admin = chatAdmin();
    $otro = chatEstudiante('V-30000003');

    $sinReclamar = chatHilo($otro, 'pendiente');
    $suyoPropio = chatHilo(chatEstudiante('V-30000004'), 'activo', $admin);

    $respuesta = $this->actingAs($admin)
        ->get(route('admin.chat.index'))
        ->assertOk()
        ->assertSee('Sin reclamar')
        ->assertSee('Chats que estás atendiendo')
        ->assertSee(route('admin.chat.reclamar', $sinReclamar->hch_id), false);

    // El hilo sin reclamar solo ofrece "Reclamar": todavía no es de este
    // administrador, así que no hay enlace para continuarlo. Contamos el
    // botón "Continuar" en vez de comparar URLs, porque /admin/chat/1 también
    // es prefijo de /admin/chat/1/reclamar.
    expect(substr_count($respuesta->getContent(), 'Continuar'))->toBe(1);

    expect(substr_count($respuesta->getContent(), route('admin.chat.mostrar', $suyoPropio->hch_id)))
        ->toBe(1);
});

test('la bandeja del administrador queda vacía sin resultados en vez de romperse', function () {
    $this->actingAs(chatAdmin())
        ->get(route('admin.chat.index'))
        ->assertOk()
        ->assertSee('No hay consultas esperando')
        ->assertSee('Todavía no hay consultas cerradas');
});

test('el administrador reclama un ticket y queda a su nombre', function () {
    $admin = chatAdmin();
    $hilo = chatHilo(chatEstudiante('V-30000005'), 'pendiente');

    $this->actingAs($admin)
        ->post(route('admin.chat.reclamar', $hilo->hch_id))
        ->assertRedirect(route('admin.chat.mostrar', $hilo->hch_id));

    $hilo->refresh();
    expect($hilo->hch_estado)->toBe('activo')
        ->and($hilo->hch_id_admin)->toBe($admin->usu_id);
});

test('el administrador propone el cierre con su etiqueta', function () {
    $admin = chatAdmin();
    $hilo = chatHilo(chatEstudiante('V-30000006'), 'activo', $admin);

    $this->actingAs($admin)
        ->post(route('admin.chat.proponer-cierre', $hilo->hch_id), ['etiqueta_tema' => 'Carga de archivos'])
        ->assertSessionHas('success');

    $hilo->refresh();
    expect($hilo->hch_estado)->toBe('pendiente_cierre')
        ->and($hilo->hch_etiqueta_tema)->toBe('Carga de archivos')
        // La fecha debe llegar como Carbon: el modelo la castea a datetime.
        ->and($hilo->hch_fecha_solicitud_cierre)->toBeInstanceOf(Carbon::class);
});

test('un administrador distinto al que atiende no puede proponer el cierre', function () {
    $primero = chatAdmin('V-30000101');
    $segundo = chatAdmin('V-30000102');
    $hilo = chatHilo(chatEstudiante('V-30000007'), 'activo', $primero);

    $this->actingAs($segundo)
        ->post(route('admin.chat.proponer-cierre', $hilo->hch_id), ['etiqueta_tema' => 'Robada'])
        ->assertSessionHas('error');

    expect($hilo->refresh()->hch_estado)->toBe('activo');
});

test('el chat del administrador sin reclamar muestra el botón de reclamar', function () {
    $admin = chatAdmin();
    $hilo = chatHilo(chatEstudiante('V-30000008'), 'pendiente');

    $this->actingAs($admin)
        ->get(route('admin.chat.mostrar', $hilo->hch_id))
        ->assertOk()
        ->assertSee('Sin reclamar')
        ->assertSee(route('admin.chat.reclamar', $hilo->hch_id), false);
});

/*
|--------------------------------------------------------------------------
| Estados legibles
|--------------------------------------------------------------------------
*/

test('cada estado del hilo tiene un texto propio', function (string $estado, string $etiqueta) {
    expect((new HiloChat(['hch_estado' => $estado]))->estado_etiqueta)->toBe($etiqueta);
})->with([
    ['pendiente', 'Esperando atención'],
    ['activo', 'En atención'],
    ['pendiente_cierre', 'Pendiente de confirmación'],
    ['cerrado', 'Cerrado'],
]);

test('un estado desconocido no rompe el render y cae en un texto genérico', function () {
    expect((new HiloChat(['hch_estado' => 'inventado']))->estado_etiqueta)->toBe('Inventado');
});
