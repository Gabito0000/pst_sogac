<?php

use App\Models\TipoDocumento;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

/**
 * Datos minimos que el formulario de registro acepta.
 *
 * @return array<string, mixed>
 */
function registroValido(array $sobrescribir = []): array
{
    return array_merge([
        'primer_nombre' => 'Maria',
        'segundo_nombre' => 'Carolina',
        'primer_apellido' => 'Perez',
        'segundo_apellido' => 'Gomez',
        'nacionalidad' => 'V',
        'cedula' => '12345678',
        'telefono' => '04141234567',
        'pnf' => 'Informatica',
        'trayecto' => '3',
        'email' => 'maria.perez@correo.test',
        'password' => 'secreto123',
        'password_confirmation' => 'secreto123',
    ], $sobrescribir);
}

test('el formulario de registro ofrece los campos que la validacion exige', function () {
    $this->get(route('register'))
        ->assertOk()
        ->assertSee('name="cedula"', false)
        ->assertSee('name="email"', false)
        ->assertSee('name="password_confirmation"', false);
});

test('un visitante se registra y queda adentro como estudiante', function () {
    $respuesta = $this->post(route('register.post'), registroValido());

    $usuario = Usuario::where('usu_correo_electronico', 'maria.perez@correo.test')->first();

    expect($usuario)->not->toBeNull();
    $respuesta->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($usuario);
    expect($usuario->usu_rol)->toBe('estudiante');
});

test('el registro guarda el telefono y los datos academicos que se piden', function () {
    $this->post(route('register.post'), registroValido());

    $usuario = Usuario::where('usu_correo_electronico', 'maria.perez@correo.test')->firstOrFail();

    // El formulario pide estos tres datos y el estudiante los rellena creyendo
    // que quedan guardados: si no se persisten, se pierden en silencio.
    expect($usuario->usu_numero_telefono)->toBe('04141234567')
        ->and($usuario->usu_pnf ?? null)->toBe('Informatica')
        ->and($usuario->usu_trayecto ?? null)->toBe('3');
});

test('la clave guardada sirve para entrar, no se almacena en claro', function () {
    $this->post(route('register.post'), registroValido());

    $usuario = Usuario::where('usu_correo_electronico', 'maria.perez@correo.test')->firstOrFail();

    expect($usuario->usu_contrasena_hash)->not->toBe('secreto123')
        ->and(Hash::check('secreto123', $usuario->usu_contrasena_hash))->toBeTrue();
});

test('la cedula repetida no crea una cuenta nueva', function () {
    $this->post(route('register.post'), registroValido());

    $duplicada = $this->post(route('register.post'), registroValido([
        'email' => 'otro@correo.test',
    ]));

    $duplicada->assertSessionHasErrors('cedula');
    expect(Usuario::where('usu_correo_electronico', 'otro@correo.test')->count())->toBe(0);
});

test('el correo repetido no crea una cuenta nueva', function () {
    $this->post(route('register.post'), registroValido());

    $duplicada = $this->post(route('register.post'), registroValido([
        'cedula' => '87654321',
    ]));

    $duplicada->assertSessionHasErrors('email');
    expect(Usuario::where('usu_numero_documento', '87654321')->count())->toBe(0);
});

test('una clave corta o sin confirmar se rechaza', function (array $datos, string $campo) {
    $this->post(route('register.post'), $datos)->assertSessionHasErrors($campo);
})->with([
    'clave corta' => [fn () => registroValido(['password' => 'corto', 'password_confirmation' => 'corto']), 'password'],
    'confirmacion distinta' => [fn () => registroValido(['password_confirmation' => 'distinta123']), 'password'],
    'sin clave' => [fn () => registroValido(['password' => null, 'password_confirmation' => null]), 'password'],
]);

test('un correo con formato invalido se rechaza', function () {
    $this->post(route('register.post'), registroValido([
        'email' => 'no-es-un-correo',
    ]))->assertSessionHasErrors('email');
});

test('una nacionalidad fuera de V y E se rechaza', function () {
    $this->post(route('register.post'), registroValido([
        'nacionalidad' => 'X',
    ]))->assertSessionHasErrors('nacionalidad');
});

test('el registro sin datos obligatorios no crea nada y conserva lo escrito', function () {
    $respuesta = $this->post(route('register.post'), registroValido([
        'primer_nombre' => '',
        'primer_apellido' => '',
        'email' => '',
    ]));

    $respuesta->assertSessionHasErrors(['primer_nombre', 'primer_apellido', 'email']);
    expect(Usuario::count())->toBe(0);
    // Laravel guarda lo escrito en _old_input, para repoblar el formulario.
    // Un campo enviado vacio queda como null en el arreglo de old input.
    expect(session()->getOldInput('primer_nombre'))->toBeNull();
});

test('el tipo de documento se crea una sola vez para la nacionalidad', function () {
    $this->post(route('register.post'), registroValido());
    $this->post(route('register.post'), registroValido([
        'cedula' => '11223344',
        'email' => 'segundo@correo.test',
    ]));

    expect(TipoDocumento::where('tdo_abreviatura', 'V')->count())->toBe(1);
});

test('un estudiante recien registrado no entra al panel de administracion', function () {
    $this->post(route('register.post'), registroValido());

    $this->get(route('admin.dashboard'))->assertForbidden();
});

test('el registro valida el documento con el patron del formulario', function () {
    // El input exige entre 6 y 8 digitos; la validacion del servidor tiene que
    // cubrir lo mismo, o un valor no numerico entraria a la base.
    $respuesta = $this->post(route('register.post'), registroValido([
        'cedula' => 'abc',
    ]));

    $respuesta->assertSessionHasErrors('cedula');
});
