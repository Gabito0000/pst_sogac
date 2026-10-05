<?php

use App\Models\TipoDocumento;
use App\Models\Usuario;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

beforeEach(function () {
    TipoDocumento::firstOrCreate(
        ['tdo_abreviatura' => 'V'],
        ['tdo_nombre_documento' => 'Cédula de identidad']
    );

    Notification::fake();
});

function resetUsuario(string $rol = 'administrador', string $documento = 'V-50000001'): Usuario
{
    return Usuario::create([
        'usu_rol' => $rol,
        'usu_tdo_id' => TipoDocumento::where('tdo_abreviatura', 'V')->value('tdo_id'),
        'usu_primer_nombre' => 'Persona',
        'usu_primer_apellido' => $rol,
        'usu_numero_documento' => $documento,
        'usu_correo_electronico' => $documento.'@ejemplo.com',
        'usu_contrasena_hash' => Hash::make('contrasenaVieja'),
        'usu_estado_cuenta' => 'activo',
    ]);
}

test('el login ofrece el enlace para recuperar la contrasena', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee(route('password.request'), false);
});

test('el formulario de recuperacion se abre y pide el correo', function () {
    $this->get('/olvide-mi-contrasena')
        ->assertOk()
        ->assertSee('name="email"', false);
});

test('un correo registrado dispara el correo con el enlace', function () {
    $usuario = resetUsuario();

    $this->from('/olvide-mi-contrasena')
        ->post('/olvide-mi-contrasena', ['email' => $usuario->usu_correo_electronico])
        ->assertRedirect('/olvide-mi-contrasena')
        ->assertSessionHas('status');

    Notification::assertSentTo($usuario, ResetPassword::class);
});

test('un correo sin cuenta no filtra información ni lanza error', function () {
    $this->from('/olvide-mi-contrasena')
        ->post('/olvide-mi-contrasena', ['email' => 'nadie@ejemplo.com'])
        ->assertRedirect('/olvide-mi-contrasena')
        ->assertSessionHas('status');

    Notification::assertNothingSent();
});

test('un correo con formato invalido se rechaza', function () {
    $this->from('/olvide-mi-contrasena')
        ->post('/olvide-mi-contrasena', ['email' => 'esto-no-es-un-correo'])
        ->assertSessionHasErrors('email');

    Notification::assertNothingSent();
});

test('el enlace recibido lleva al formulario de nueva contrasena', function () {
    $usuario = resetUsuario();
    $token = Password::broker()->createToken($usuario);

    $this->get(route('password.reset', ['token' => $token, 'email' => $usuario->usu_correo_electronico]))
        ->assertOk()
        ->assertSee('name="token"', false)
        ->assertSee($token, false);
});

test('la contrasena se restablece y permite entrar con la nueva', function () {
    $usuario = resetUsuario();
    $token = Password::broker()->createToken($usuario);

    $this->post('/restablecer-contrasena', [
        'token' => $token,
        'email' => $usuario->usu_correo_electronico,
        'password' => 'nuevaClave123',
        'password_confirmation' => 'nuevaClave123',
    ])->assertRedirect(route('login'));

    $usuario->refresh();

    expect(Hash::check('nuevaClave123', $usuario->usu_contrasena_hash))->toBeTrue();

    $this->post('/login', [
        'identificador' => $usuario->usu_correo_electronico,
        'password' => 'nuevaClave123',
    ])->assertRedirect(route('admin.dashboard'));
});

test('la nueva contrasena debe coincidir y tener minimo 8 caracteres', function () {
    $usuario = resetUsuario();
    $token = Password::broker()->createToken($usuario);

    $this->from(route('password.reset', ['token' => $token, 'email' => $usuario->usu_correo_electronico]))
        ->post('/restablecer-contrasena', [
            'token' => $token,
            'email' => $usuario->usu_correo_electronico,
            'password' => 'corta',
            'password_confirmation' => 'otra-distinta',
        ])
        ->assertSessionHasErrors('password');

    expect(Hash::check('contrasenaVieja', $usuario->fresh()->usu_contrasena_hash))->toBeTrue();
});

test('un token invalido no cambia la contrasena', function () {
    $usuario = resetUsuario();

    $this->from(route('password.reset', ['token' => 'token-falso', 'email' => $usuario->usu_correo_electronico]))
        ->post('/restablecer-contrasena', [
            'token' => 'token-falso',
            'email' => $usuario->usu_correo_electronico,
            'password' => 'nuevaClave123',
            'password_confirmation' => 'nuevaClave123',
        ])
        ->assertSessionHasErrors('email');

    expect(Hash::check('contrasenaVieja', $usuario->fresh()->usu_contrasena_hash))->toBeTrue();
});
