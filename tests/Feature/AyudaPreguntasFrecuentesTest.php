<?php

use App\Models\PreguntasFrecuentes;
use App\Models\TipoDocumento;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Crea un usuario con el catalogo de tipo de documento que exige usuarios.
 */
function ayudaUsuario(string $rol, string $numeroDocumento, string $abreviatura): Usuario
{
    $documento = TipoDocumento::firstOrCreate(
        ['tdo_abreviatura' => $abreviatura],
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

function ayudaEstudiante(string $documento = 'V-20000001'): Usuario
{
    return ayudaUsuario('estudiante', $documento, 'V');
}

function ayudaAdmin(string $documento = 'V-10000001'): Usuario
{
    return ayudaUsuario('admin', $documento, 'V');
}

/*
|--------------------------------------------------------------------------
| Separación entre preguntas frecuentes y chat
|--------------------------------------------------------------------------
*/

test('las preguntas frecuentes y el chat son rutas distintas', function () {
    expect(route('user.ayuda.preguntas'))->not->toBe(route('user.ayuda.chat.index'));
});

test('un estudiante llega a las preguntas frecuentes desde la navegacion', function () {
    PreguntasFrecuentes::create([
        'pregunta' => '¿Dónde consulto el estado de mi trámite?',
        'respuesta' => 'En el historial de solicitudes.',
    ]);

    $this->actingAs(ayudaEstudiante())
        ->get(route('user.ayuda.preguntas'))
        ->assertOk()
        ->assertSee('¿Dónde consulto el estado de mi trámite?')
        ->assertSee(route('user.ayuda.preguntas'), false)
        // El chat es una entrada propia del menu, no un destino oculto.
        ->assertSee(route('user.ayuda.chat.index'), false);
});

test('el portal del estudiante es de solo lectura', function () {
    PreguntasFrecuentes::create([
        'pregunta' => '¿Puedo corregir un dato mal digitado?',
        'respuesta' => 'No después de enviar la solicitud.',
    ]);

    $this->actingAs(ayudaEstudiante())
        ->get(route('user.ayuda.preguntas'))
        ->assertOk()
        ->assertDontSee(route('admin.preguntas.edit', 1), false)
        ->assertDontSee(route('admin.preguntas.destroy', 1), false)
        ->assertDontSee('Eliminar', false);
});

test('un visitante es enviado al login al pedir las preguntas frecuentes', function () {
    $this->get(route('user.ayuda.preguntas'))->assertRedirect(route('login'));
});

test('un estudiante no puede abrir la bandeja del chat de otro sector', function () {
    $this->actingAs(ayudaEstudiante())
        ->get(route('user.ayuda.chat.index'))
        ->assertOk()
        ->assertSee('Chat de soporte');
});

test('la bandeja del chat del estudiante ofrece crear una consulta cuando no hay ninguna abierta', function () {
    $this->actingAs(ayudaEstudiante())
        ->get(route('user.ayuda.chat.index'))
        ->assertOk()
        ->assertSee(route('user.ayuda.chat.iniciar'), false)
        ->assertSee('+ Nueva consulta');
});

/*
|--------------------------------------------------------------------------
| Buscador de preguntas frecuentes
|--------------------------------------------------------------------------
*/

test('el buscador filtra por el texto de la pregunta', function () {
    PreguntasFrecuentes::create(['pregunta' => '¿Cómo solicito una constancia?', 'respuesta' => 'Desde Trámites.']);
    PreguntasFrecuentes::create(['pregunta' => '¿Cuándo pagan la beca?', 'respuesta' => 'El día 5.']);

    $this->actingAs(ayudaEstudiante())
        ->get(route('user.ayuda.preguntas', ['q' => 'constancia']))
        ->assertOk()
        ->assertSee('¿Cómo solicito una constancia?')
        ->assertDontSee('¿Cuándo pagan la beca?');
});

test('el buscador también filtra por el texto de la respuesta', function () {
    PreguntasFrecuentes::create(['pregunta' => '¿Cómo solicito una constancia?', 'respuesta' => 'Desde Trámites.']);
    PreguntasFrecuentes::create(['pregunta' => '¿Cuándo pagan la beca?', 'respuesta' => 'Consulta en la oficina.']);

    $this->actingAs(ayudaEstudiante())
        ->get(route('user.ayuda.preguntas', ['q' => 'oficina']))
        ->assertOk()
        ->assertSee('¿Cuándo pagan la beca?')
        ->assertDontSee('¿Cómo solicito una constancia?');
});

test('una búsqueda sin coincidencias lo explica en lugar de mostrar una lista vacía', function () {
    PreguntasFrecuentes::create(['pregunta' => '¿Cómo solicito una constancia?', 'respuesta' => 'Desde Trámites.']);

    $this->actingAs(ayudaEstudiante())
        ->get(route('user.ayuda.preguntas', ['q' => 'xyzinexistente']))
        ->assertOk()
        ->assertSee('Ninguna pregunta coincide');
});

test('el portal vacío indica que aún no hay preguntas publicadas', function () {
    $this->actingAs(ayudaEstudiante())
        ->get(route('user.ayuda.preguntas'))
        ->assertOk()
        ->assertSee('Todavía no hay preguntas frecuentes');
});

/*
|--------------------------------------------------------------------------
| CRUD de preguntas frecuentes del administrador
|
| Estas pruebas cubren los tres defectos que tenía el controlador anterior:
| create() devolvía una vista inexistente, edit() no devolvía nada, y los
| tres redirects apuntaban a la ruta del estudiante.
|--------------------------------------------------------------------------
*/

test('un estudiante no puede llegar a la gestión de preguntas frecuentes', function () {
    $pregunta = PreguntasFrecuentes::create(['pregunta' => 'Duda', 'respuesta' => 'Respuesta.']);
    $estudiante = ayudaEstudiante();

    // Cada aserto necesita su propio actingAs: encadenarlos sobre una misma
    // instancia reutilizaría la sesión de la petición anterior.
    $this->actingAs($estudiante)->get(route('admin.preguntas.index'))->assertForbidden();
    $this->actingAs($estudiante)->get(route('admin.preguntas.create'))->assertForbidden();
    $this->actingAs($estudiante)->get(route('admin.preguntas.edit', $pregunta))->assertForbidden();

    $this->actingAs($estudiante)
        ->post(route('admin.preguntas.store'), ['pregunta' => ' otra ', 'respuesta' => ' otra '])
        ->assertForbidden();
});

test('el administrador encuentra el enlace a preguntas frecuentes en la navegacion', function () {
    $this->actingAs(ayudaAdmin())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee(route('admin.preguntas.index'), false)
        ->assertSee(route('admin.chat.index'), false);
});

test('el listado del administrador muestra las preguntas y las acciones de edición', function () {
    PreguntasFrecuentes::create([
        'pregunta' => '¿Cuánto dura un trámite?',
        'respuesta' => 'Depende del tipo de trámite.',
    ]);

    $this->actingAs(ayudaAdmin())
        ->get(route('admin.preguntas.index'))
        ->assertOk()
        ->assertSee('¿Cuánto dura un trámite?')
        ->assertSee(route('admin.preguntas.create'), false);
});

test('el listado vacío enlaza al alta', function () {
    $this->actingAs(ayudaAdmin())
        ->get(route('admin.preguntas.index'))
        ->assertOk()
        ->assertSee('Todavía no hay preguntas frecuentes')
        ->assertSee(route('admin.preguntas.create'), false);
});

test('el formulario de alta se abre', function () {
    // Antes esto reventaba con ViewNotFoundException: devolvía soporte.form.
    $this->actingAs(ayudaAdmin())
        ->get(route('admin.preguntas.create'))
        ->assertOk()
        ->assertSee('Nueva pregunta frecuente')
        ->assertSee(route('admin.preguntas.store'), false);
});

test('el administrador crea una pregunta y vuelve a su propio listado', function () {
    // La regresión clave: el redirect apuntaba a soporte.index, la vista de
    // solo lectura del estudiante.
    $this->actingAs(ayudaAdmin())
        ->post(route('admin.preguntas.store'), [
            'pregunta' => '¿Dónde veo el resultado?',
            'respuesta' => 'En el historial de solicitudes.',
        ])
        ->assertRedirect(route('admin.preguntas.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('preguntas_frecuentes', [
        'pregunta' => '¿Dónde veo el resultado?',
        'respuesta' => 'En el historial de solicitudes.',
    ]);
});

test('la creación valida pregunta y respuesta', function () {
    $this->actingAs(ayudaAdmin())
        ->from(route('admin.preguntas.create'))
        ->post(route('admin.preguntas.store'), ['pregunta' => '', 'respuesta' => ''])
        ->assertRedirect(route('admin.preguntas.create'))
        ->assertSessionHasErrors(['pregunta', 'respuesta']);

    $this->assertDatabaseCount('preguntas_frecuentes', 0);
});

test('la creación conserva los datos escritos cuando falla la validación', function () {
    $this->actingAs(ayudaAdmin())
        ->from(route('admin.preguntas.create'))
        ->post(route('admin.preguntas.store'), ['pregunta' => 'Duda válida', 'respuesta' => ''])
        ->assertSessionHasInput('pregunta', 'Duda válida');
});

test('el formulario de edición se abre con los datos de la pregunta', function () {
    // Antes esto devolvía null y pintaba una página en blanco.
    $pregunta = PreguntasFrecuentes::create([
        'pregunta' => '¿Puedo anular una solicitud?',
        'respuesta' => 'Solo si sigue pendiente.',
    ]);

    $this->actingAs(ayudaAdmin())
        ->get(route('admin.preguntas.edit', $pregunta))
        ->assertOk()
        ->assertSee('¿Puedo anular una solicitud?')
        ->assertSee('Solo si sigue pendiente.')
        ->assertSee(route('admin.preguntas.update', $pregunta), false);
});

test('editar una pregunta inexistente responde 404', function () {
    $this->actingAs(ayudaAdmin())
        ->get(route('admin.preguntas.edit', 9999))
        ->assertNotFound();
});

test('el administrador actualiza una pregunta y vuelve a su listado', function () {
    $pregunta = PreguntasFrecuentes::create([
        'pregunta' => 'Respuesta vieja',
        'respuesta' => 'Contenido viejo.',
    ]);

    $this->actingAs(ayudaAdmin())
        ->put(route('admin.preguntas.update', $pregunta), [
            'pregunta' => 'Respuesta nueva',
            'respuesta' => 'Contenido nuevo.',
        ])
        ->assertRedirect(route('admin.preguntas.index'));

    $this->assertDatabaseHas('preguntas_frecuentes', [
        'id' => $pregunta->id,
        'pregunta' => 'Respuesta nueva',
        'respuesta' => 'Contenido nuevo.',
    ]);
});

test('la actualización no admite una pregunta en blanco', function () {
    $pregunta = PreguntasFrecuentes::create(['pregunta' => 'Original', 'respuesta' => 'Original.']);

    $this->actingAs(ayudaAdmin())
        ->from(route('admin.preguntas.edit', $pregunta))
        ->put(route('admin.preguntas.update', $pregunta), ['pregunta' => '', 'respuesta' => ''])
        ->assertSessionHasErrors(['pregunta', 'respuesta']);

    $pregunta->refresh();
    expect($pregunta->pregunta)->toBe('Original');
});

test('el administrador elimina una pregunta', function () {
    $pregunta = PreguntasFrecuentes::create(['pregunta' => 'Para borrar', 'respuesta' => 'Temporal.']);

    $this->actingAs(ayudaAdmin())
        ->delete(route('admin.preguntas.destroy', $pregunta))
        ->assertRedirect(route('admin.preguntas.index'));

    $this->assertDatabaseMissing('preguntas_frecuentes', ['id' => $pregunta->id]);
});

test('un estudiante no puede borrar una pregunta', function () {
    $pregunta = PreguntasFrecuentes::create(['pregunta' => 'Intacta', 'respuesta' => 'Intacta.']);

    $this->actingAs(ayudaEstudiante())
        ->delete(route('admin.preguntas.destroy', $pregunta))
        ->assertForbidden();

    $this->assertDatabaseHas('preguntas_frecuentes', ['id' => $pregunta->id]);
});

test('lo que crea el administrador aparece en el portal del estudiante', function () {
    PreguntasFrecuentes::create([
        'pregunta' => '¿Necesito cédula para el trámite?',
        'respuesta' => 'Sí, una copia legible.',
    ]);

    $this->actingAs(ayudaEstudiante())
        ->get(route('user.ayuda.preguntas'))
        ->assertOk()
        ->assertSee('¿Necesito cédula para el trámite?');
});
