<?php

use App\Models\TipoDocumento;
use App\Models\Usuario;
use App\Rules\SoloDigitos;
use App\Rules\SoloLetras;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

/*
 * Cada campo debe aceptar solo lo que le corresponde: letras donde va un
 * nombre, digitos donde va una cedula o un telefono.
 *
 * El atributo pattern del HTML no alcanza como unico filtro: solo actsua en el
 * navegador, asi que estas pruebas atacan la regla del servidor, que es la que
 * decide de verdad.
 */

/** @return array<string, mixed> Datos válidos de registro, para variar uno por uno. */
function datosRegistroValidos(array $cambios = []): array
{
    return array_merge([
        'primer_nombre' => 'Ana',
        'segundo_nombre' => 'María',
        'primer_apellido' => 'Pérez',
        'segundo_apellido' => 'Rivas',
        'nacionalidad' => 'V',
        'cedula' => '27998877',
        'telefono' => '04141234567',
        'pnf' => 'Informatica',
        'trayecto' => '3',
        'email' => 'ana'.uniqid().'@uptp.test',
        'password' => 'claveSegura123',
        'password_confirmation' => 'claveSegura123',
    ], $cambios);
}

function enviarRegistro(array $datos)
{
    return test()->from(route('register'))->post(route('register.post'), $datos);
}

describe('nombres y apellidos: solo letras', function () {
    it('acepta nombres con tilde, ñ y espacios', function () {
        $r = enviarRegistro(datosRegistroValidos([
            'primer_nombre' => 'José',
            'segundo_nombre' => 'María',
            'primer_apellido' => 'Peña',
            'segundo_apellido' => 'de la Cruz',
        ]));

        $r->assertSessionHasNoErrors();
    });

    it('acepta apellidos compuestos con guion y apostrofo', function () {
        $r = enviarRegistro(datosRegistroValidos([
            'primer_apellido' => 'Ruiz-Smith',
            'segundo_apellido' => "O'Brien",
        ]));

        $r->assertSessionHasNoErrors();
    });

    it('rechaza un nombre que solo tiene numeros', function () {
        enviarRegistro(datosRegistroValidos(['primer_nombre' => '12345']))
            ->assertSessionHasErrors('primer_nombre');
    });

    it('rechaza un apellido con numeros pegados a las letras', function () {
        enviarRegistro(datosRegistroValidos(['primer_apellido' => 'Perez123']))
            ->assertSessionHasErrors('primer_apellido');
    });

    it('rechaza simbolos sin letras', function () {
        enviarRegistro(datosRegistroValidos(['primer_nombre' => '---']))
            ->assertSessionHasErrors('primer_nombre');
    });

    it('no guarda el usuario cuando el nombre es invalido', function () {
        $antes = Usuario::count();

        enviarRegistro(datosRegistroValidos(['primer_nombre' => '98765']));

        expect(Usuario::count())->toBe($antes);
    });
});

describe('cedula y telefono: solo digitos', function () {
    it('acepta una cedula de puros digitos', function () {
        enviarRegistro(datosRegistroValidos(['cedula' => '27998877']))
            ->assertSessionHasNoErrors();
    });

    it('rechaza una cedula con el prefijo V-', function () {
        enviarRegistro(datosRegistroValidos(['cedula' => 'V-27998877']))
            ->assertSessionHasErrors('cedula');
    });

    it('rechaza un telefono con letras', function () {
        enviarRegistro(datosRegistroValidos(['telefono' => 'llamameya']))
            ->assertSessionHasErrors('telefono');
    });

    it('rechaza un telefono con simbolos', function () {
        enviarRegistro(datosRegistroValidos(['telefono' => '0414-1234567']))
            ->assertSessionHasErrors('telefono');
    });

    it('rechaza un telefono de mas de 11 digitos', function () {
        enviarRegistro(datosRegistroValidos(['telefono' => '04141234567890']))
            ->assertSessionHasErrors('telefono');
    });
});

describe('los desplegables solo aceptan sus opciones', function () {
    it('rechaza un PNF que no esta en la lista', function () {
        enviarRegistro(datosRegistroValidos(['pnf' => 'Carrera Inventada']))
            ->assertSessionHasErrors('pnf');
    });

    it('rechaza un trayecto que no esta en la lista', function () {
        enviarRegistro(datosRegistroValidos(['trayecto' => '99']))
            ->assertSessionHasErrors('trayecto');
    });
});

describe('el formulario avisa antes de dejar escribir', function () {
    it('el registro no desactiva la validacion del navegador', function () {
        // Con novalidate el navegador ignora pattern y required, y el usuario
        // se entera del error solo al final.
        test()->get(route('register'))
            ->assertOk()
            ->assertDontSee('novalidate', false);
    });

    it('los campos de texto y numero quedan marcados para el filtro del navegador', function () {
        $html = test()->get(route('register'))->getContent();

        expect(substr_count($html, 'data-solo="letras"'))->toBe(4)
            ->and(substr_count($html, 'data-solo="digitos"'))->toBe(2)
            ->and($html)->toContain('js/campos-tipo.js');
    });

    it('el perfil tambien marca sus campos de nombre', function () {
        $tipoDocumento = TipoDocumento::firstOrCreate(
            ['tdo_abreviatura' => 'V'],
            ['tdo_nombre_documento' => 'Cédula de Identidad']
        );

        $usuario = Usuario::create([
            'usu_rol' => 'estudiante',
            'usu_tdo_id' => $tipoDocumento->tdo_id,
            'usu_primer_nombre' => 'Ana',
            'usu_primer_apellido' => 'Pérez',
            'usu_numero_documento' => 'V-40000001',
            'usu_correo_electronico' => 'ana.perfil@ejemplo.com',
            'usu_contrasena_hash' => bcrypt('password'),
            'usu_estado_cuenta' => 'activo',
            'usu_fecha_registro' => now(),
        ]);

        $html = test()->actingAs($usuario)->get(route('user.datos-perfil'))->getContent();

        expect(substr_count($html, 'data-solo="letras"'))->toBe(4)
            ->and($html)->toContain('js/campos-tipo.js');
    });
});

describe('las reglas por separado', function () {
    it('SoloLetras acepta nombres y rechaza numeros', function () {
        $valida = fn ($v) => ! Validator::make(['x' => $v], ['x' => [new SoloLetras]])->fails();

        expect($valida('José'))->toBeTrue()
            ->and($valida('María José'))->toBeTrue()
            ->and($valida('Ñuñez'))->toBeTrue()
            ->and($valida('12345'))->toBeFalse()
            ->and($valida('Perez1'))->toBeFalse()
            ->and($valida('!!!'))->toBeFalse()
            ->and($valida('   '))->toBeTrue();   // lo cubre required
    });

    it('SoloDigitos acepta numeros y rechaza texto', function () {
        $valida = fn ($v) => ! Validator::make(['x' => $v], ['x' => [new SoloDigitos]])->fails();

        expect($valida('04141234567'))->toBeTrue()
            ->and($valida('0'))->toBeTrue()
            ->and($valida('llamameya'))->toBeFalse()
            ->and($valida('0414-1234'))->toBeFalse()
            ->and($valida('1e5'))->toBeFalse();
    });
});
