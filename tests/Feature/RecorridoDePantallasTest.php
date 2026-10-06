<?php

use App\Models\ChatSoporte\HiloChat;
use App\Models\ChatSoporte\MensajeChat;
use App\Models\PreguntasFrecuentes;
use App\Models\Requisito;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

/*
 * Recorrido de pantallas: pide TODAS las vistas de solo lectura y exige que
 * respondan 200.
 *
 * Existe porque los tests de cada modulo comprueban su parte, pero ninguna
 * prueba que el resto de pantallas siga abriendo. Un error de sintaxis en una
 * vista, una variable que dejo de pasar el controlador o una ruta movida se
 * detecta aqui, en un solo comando, en vez de que lo encuentre un usuario
 * entrando al panel.
 *
 * Los datos se crean DENTRO de cada test a proposito: un dataset se resuelve
 * antes de levantar la base de pruebas de la prueba, asi que consultarla desde
 * ahi falla.
 *
 * Reutiliza los helpers globales de AdminTratadasTest (admin(), estudiante(),
 * tipoSolicitud(), solicitudConHistorial()), que los demas archivos ya usan.
 */
uses(RefreshDatabase::class);

/**
 * Crea el catalogo minimo que necesitan las pantallas de administracion.
 *
 * @return array<string, mixed>
 */
function catalogoPantallas(): array
{
    $requisito = Requisito::firstOrCreate(
        ['req_nombre_requisito' => 'Cédula de identidad'],
        ['req_descripcion' => 'Copia legible de la cédula.', 'req_estado_requisito' => 'activo'],
    );

    // La tabla solo tiene pregunta y respuesta: no hay campo de estado, asi que
    // publicar una es insertarla.
    $pregunta = PreguntasFrecuentes::firstOrCreate(
        ['pregunta' => '¿Cómo solicito una constancia?'],
        ['respuesta' => 'Entra a Trámites y elige Constancia de Estudio.'],
    );

    return compact('requisito', 'pregunta');
}

/**
 * Abre un hilo de chat con un mensaje, para que la vista tenga conversacion.
 */
function hiloConMensaje(Usuario $estudiante, Usuario $administrador): HiloChat
{
    $hilo = HiloChat::create([
        'hch_id_usuario' => $estudiante->usu_id,
        'hch_id_admin' => $administrador->usu_id,
        'hch_estado' => 'activo',
        'hch_etiqueta_tema' => 'Constancia de estudio',
    ]);

    MensajeChat::create([
        'mch_id_hilo' => $hilo->hch_id,
        'mch_id_remitente' => $estudiante->usu_id,
        'mch_cuerpo' => 'No puedo cargar la constancia que solicité.',
    ]);

    return $hilo;
}

/**
 * Usuario con un rol administrativo concreto de la jerarquia.
 */
function usuarioConRol(string $rol, string $documento): Usuario
{
    $catalogo = catalogoTratadas();

    return Usuario::firstOrCreate(
        ['usu_numero_documento' => $documento],
        [
            'usu_rol' => $rol,
            'usu_tdo_id' => $catalogo['tipo_documento']->tdo_id,
            'usu_primer_nombre' => ' Prueba',
            'usu_primer_apellido' => ucfirst($rol),
            'usu_correo_electronico' => $documento.'@test.com',
            'usu_contrasena_hash' => bcrypt('password'),
            'usu_estado_cuenta' => 'activo',
            'usu_fecha_registro' => now(),
        ],
    );
}

test('todas las pantallas del administrador responden 200', function () {
    $tipo = tipoSolicitud();
    $estudiante = estudiante();
    $administrador = admin();
    $solicitud = solicitudConHistorial($estudiante, $tipo, ['aprobada']);
    $hilo = hiloConMensaje($estudiante, $administrador);
    $catalogo = catalogoPantallas();

    $pantallas = [
        'panel' => route('admin.dashboard'),
        'panel con filtro por estado' => route('admin.dashboard', ['estado' => 'pendiente']),
        'cola de solicitudes' => route('admin.solicitudes.index'),
        'solicitudes tratadas' => route('admin.tratadas.index'),
        'ficha de tratada' => route('admin.tratadas.show', $solicitud),
        'estadisticas' => route('admin.estadisticas'),
        'bitacora de cambios' => route('admin.cambios.index'),
        'bitacora filtrada' => route('admin.cambios.index', ['entidad' => 'Solicitud']),
        'bandeja de chat' => route('admin.chat.index'),
        'conversacion' => route('admin.chat.mostrar', $hilo),
        'preguntas frecuentes' => route('admin.preguntas.index'),
        'crear pregunta' => route('admin.preguntas.create'),
        'editar pregunta' => route('admin.preguntas.edit', $catalogo['pregunta']),
        'requisitos' => route('admin.requisitos.index'),
        'crear requisito' => route('admin.requisitos.create'),
        'editar requisito' => route('admin.requisitos.edit', $catalogo['requisito']),
        'tipos de tramite' => route('admin.tipos-solicitud.index'),
        'crear tipo' => route('admin.tipos-solicitud.create'),
        'editar tipo' => route('admin.tipos-solicitud.edit', $tipo),
        'gestion de usuarios' => route('admin.usuarios.index'),
    ];

    $fallos = [];

    foreach ($pantallas as $nombre => $url) {
        $respuesta = $this->actingAs($administrador)->get($url);

        if ($respuesta->status() !== 200) {
            $fallos[] = $nombre.' ('.$respuesta->status().')';
        }
    }

    expect($fallos)->toBe([], ' pantallas que no respondieron 200: '.implode(', ', $fallos));
});

test('todas las pantallas del estudiante responden 200', function () {
    $tipo = tipoSolicitud();
    $estudiante = estudiante();
    $solicitud = solicitudConHistorial($estudiante, $tipo, ['aprobada']);
    $hilo = hiloConMensaje($estudiante, admin());

    $pantallas = [
        'inicio' => route('dashboard'),
        'tramites' => route('user.tramites.index'),
        'formulario de solicitud' => route('user.tramites.solicitar', $tipo),
        'historial' => route('user.historial.index'),
        'ficha de solicitud' => route('user.historial.show', $solicitud),
        'calendario de citas' => route('user.citas'),
        'preguntas frecuentes' => route('user.ayuda.preguntas'),
        'bandeja de chat' => route('user.ayuda.chat.index'),
        'conversacion' => route('user.ayuda.chat.mostrar', $hilo),
    ];

    $fallos = [];

    foreach ($pantallas as $nombre => $url) {
        $respuesta = $this->actingAs($estudiante)->get($url);

        if ($respuesta->status() !== 200) {
            $fallos[] = $nombre.' ('.$respuesta->status().')';
        }
    }

    expect($fallos)->toBe([], ' pantallas que no respondieron 200: '.implode(', ', $fallos));
});

test('las pantallas publicas responden 200', function (string $url) {
    $this->get($url)->assertOk();
})->with([
    'portada' => [fn () => route('portada')],
    'login' => [fn () => route('login')],
    'registro' => [fn () => route('register')],
    'olvide mi contrasena' => [fn () => route('password.request')],
]);

test('un visitante es expulsado de las pantallas privadas', function (string $url) {
    $this->get($url)->assertRedirect(route('login'));
})->with([
    'panel' => [fn () => route('admin.dashboard')],
    'cola' => [fn () => route('admin.solicitudes.index')],
    'inicio' => [fn () => route('dashboard')],
    'historial' => [fn () => route('user.historial.index')],
    'chat' => [fn () => route('user.ayuda.chat.index')],
]);

test('el estudiante no entra al panel de administracion', function (string $url) {
    $this->actingAs(estudiante())->get($url)->assertForbidden();
})->with([
    'panel' => [fn () => route('admin.dashboard')],
    'cola' => [fn () => route('admin.solicitudes.index')],
    'tratadas' => [fn () => route('admin.tratadas.index')],
    'estadisticas' => [fn () => route('admin.estadisticas')],
    'usuarios' => [fn () => route('admin.usuarios.index')],
]);

test('el taquillero no abre los modulos reservados al administrador', function (string $url) {
    $this->actingAs(usuarioConRol(Rol::TAQUILLERO, 'V-90000002'))
        ->get($url)
        ->assertForbidden();
})->with([
    'estadisticas' => [fn () => route('admin.estadisticas')],
    'bitacora' => [fn () => route('admin.cambios.index')],
    'usuarios' => [fn () => route('admin.usuarios.index')],
]);

test('el taquillero si puede trabajar la cola de solicitudes', function () {
    $this->actingAs(usuarioConRol(Rol::TAQUILLERO, 'V-90000002'))
        ->get(route('admin.solicitudes.index'))
        ->assertOk();
});

test('el analista no abre la gestion de usuarios', function () {
    $this->actingAs(usuarioConRol(Rol::ANALISTA, 'V-90000003'))
        ->get(route('admin.usuarios.index'))
        ->assertForbidden();
});
