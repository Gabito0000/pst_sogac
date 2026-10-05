<?php

use App\Models\TipoDocumento;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

beforeEach(function () {
    TipoDocumento::firstOrCreate(
        ['tdo_abreviatura' => 'V'],
        ['tdo_nombre_documento' => 'Cédula de identidad']
    );
});

function loginUsuario(string $rol = 'administrador', string $documento = 'V-00000001'): Usuario
{
    return Usuario::create([
        'usu_rol' => $rol,
        'usu_tdo_id' => TipoDocumento::where('tdo_abreviatura', 'V')->value('tdo_id'),
        'usu_primer_nombre' => 'Usuario',
        'usu_primer_apellido' => $rol,
        'usu_numero_documento' => $documento,
        'usu_correo_electronico' => $documento.'@ejemplo.com',
        'usu_contrasena_hash' => Hash::make('secreta123'),
        'usu_estado_cuenta' => 'activo',
    ]);
}

test('el formulario de login ofrece el campo identificador y no email', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee('name="identificador"', false)
        ->assertDontSee('name="email"', false);
});

test('el administrador entra con su correo y aterriza en el panel administrativo', function () {
    $admin = loginUsuario();

    $this->post('/login', [
        'identificador' => $admin->usu_correo_electronico,
        'password' => 'secreta123',
    ])->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($admin);
});

test('tambien se puede entrar con el numero de documento', function () {
    $admin = loginUsuario();

    $this->post('/login', [
        'identificador' => $admin->usu_numero_documento,
        'password' => 'secreta123',
    ])->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($admin);
});

test('el taquillero entra a la cola de solicitudes y el estudiante a su panel', function () {
    $taquillero = loginUsuario('taquillero', 'V-00000002');
    $estudiante = loginUsuario('estudiante', 'V-00000003');

    $this->post('/login', [
        'identificador' => $taquillero->usu_correo_electronico,
        'password' => 'secreta123',
    ])->assertRedirect(route('admin.solicitudes.index'));

    $this->post('/logout');

    $this->post('/login', [
        'identificador' => $estudiante->usu_correo_electronico,
        'password' => 'secreta123',
    ])->assertRedirect(route('dashboard'));
});

test('una clave incorrecta se muestra como error en el campo identificador', function () {
    $admin = loginUsuario();

    $this->from('/login')
        ->post('/login', [
            'identificador' => $admin->usu_correo_electronico,
            'password' => 'no-es-la-clave',
        ])
        ->assertRedirect('/login')
        ->assertSessionHasErrors('identificador');

    $this->assertGuest();
});

test('sin identificador la validacion responde con un error visible', function () {
    $this->from('/login')
        ->post('/login', ['identificador' => '', 'password' => ''])
        ->assertRedirect('/login')
        ->assertSessionHasErrors(['identificador', 'password']);

    $this->assertGuest();
});

test('la cuenta bloqueada temporalmente no puede entrar', function () {
    $admin = loginUsuario();

    RateLimiter::hit('login_attempts:'.$admin->usu_id, 3600);
    RateLimiter::hit('login_attempts:'.$admin->usu_id, 3600);
    RateLimiter::hit('login_attempts:'.$admin->usu_id, 3600);

    $this->from('/login')
        ->post('/login', [
            'identificador' => $admin->usu_correo_electronico,
            'password' => 'secreta123',
        ])
        ->assertSessionHasErrors('identificador');

    $this->assertGuest();
});
