<?php

use App\Models\TipoDocumento;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
 * La raiz es el homepage institucional y se sirve siempre, haya sesion o no.
 * Antes redirigia al login, asi que estos casos comprueban justo lo contrario:
 * que la portada se muestre siempre y que offerca la entrada al sistema.
 */

function portadaEstudiante(string $documento = 'V-40000001'): Usuario
{
    $tipo = TipoDocumento::firstOrCreate(
        ['tdo_abreviatura' => 'V'],
        ['tdo_nombre_documento' => 'Cédula de Identidad'],
    );

    return Usuario::create([
        'usu_rol' => 'estudiante',
        'usu_tdo_id' => $tipo->tdo_id,
        'usu_primer_nombre' => 'Persona',
        'usu_primer_apellido' => 'Prueba',
        'usu_numero_documento' => $documento,
        'usu_correo_electronico' => $documento.'@ejemplo.com',
        'usu_contrasena_hash' => bcrypt('password'),
        'usu_estado_cuenta' => 'activo',
        'usu_fecha_registro' => now(),
    ]);
}

test('la raíz muestra el homepage institucional a un visitante', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Juan de Jesús Montilla', escape: false)
        ->assertSee('Control de Estudios', escape: false);
});

test('la raíz ofrece entrar al sistema cuando no hay sesión', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Entrar al sistema', escape: false)
        ->assertSee(route('login'), escape: false);
});

test('la raíz ofrece volver al panel cuando ya hay sesión', function () {
    $this->actingAs(portadaEstudiante())
        ->get('/')
        ->assertOk()
        ->assertSee('Mi panel', escape: false)
        ->assertSee(route('dashboard'), escape: false);
});

test('el homepage carga el logo y los estilos de la portada institucional', function () {
    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->toContain(asset('portada/logo.png'))
        ->toContain(asset('portada/style.css'))
        ->toContain(asset('portada/script.js'));
});
