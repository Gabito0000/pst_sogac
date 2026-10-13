<?php

use App\Models\DatosPrecargaConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;

/*
 * Orden de los bloques en "Mis datos".
 *
 * Antes todos los campos de precarga salian en una sola lista, en el orden en
 * que se insertaron en la base. Ese orden no significa nada para quien llena
 * el formulario: el telefono quedaba debajo de la fecha de nacimiento.
 */
uses(RefreshDatabase::class);

test('los campos de precarga se agrupan en el orden en que se leen', function () {
    DatosPrecargaConfig::create([
        'dpc_campo' => 'usu_numero_telefono',
        'dpc_etiqueta' => 'Teléfono',
        'dpc_obligatorio' => false,
        'dpc_activo' => true,
        'dpc_vigencia_meses' => 12,
    ]);
    DatosPrecargaConfig::create([
        'dpc_campo' => 'usu_direccion',
        'dpc_etiqueta' => 'Dirección',
        'dpc_obligatorio' => true,
        'dpc_activo' => true,
        'dpc_vigencia_meses' => 6,
    ]);
    DatosPrecargaConfig::create([
        'dpc_campo' => 'usu_fecha_nacimiento',
        'dpc_etiqueta' => 'Fecha de nacimiento',
        'dpc_obligatorio' => true,
        'dpc_activo' => true,
        'dpc_vigencia_meses' => null,
    ]);
    DatosPrecargaConfig::create([
        'dpc_campo' => 'usu_lugar_nacimiento',
        'dpc_etiqueta' => 'Lugar de nacimiento',
        'dpc_obligatorio' => true,
        'dpc_activo' => true,
        'dpc_vigencia_meses' => null,
    ]);

    $bloques = DatosPrecargaConfig::enBloques(DatosPrecargaConfig::camposActivos());

    $titulos = array_keys($bloques);

    fwrite(STDERR, "\n=== BLOQUES EN ORDEN ===\n");

    foreach ($bloques as $titulo => $bloque) {
        fwrite(STDERR, "  {$titulo}\n");

        foreach ($bloque['campos'] as $campo) {
            fwrite(STDERR, "      {$campo->dpc_campo}\n");
        }
    }

    // Primero lo personal, despues el contacto: es el orden en el que se lee
    // una hoja de datos de una persona.
    expect($titulos)->toBe(['Datos personales', 'Contacto y dirección']);

    // Dentro de cada bloque, el orden tambien es fijo y no el de insercion.
    expect($bloques['Datos personales']['campos']->pluck('dpc_campo')->all())
        ->toBe(['usu_lugar_nacimiento', 'usu_fecha_nacimiento']);

    expect($bloques['Contacto y dirección']['campos']->pluck('dpc_campo')->all())
        ->toBe(['usu_numero_telefono', 'usu_direccion']);
});

test('un campo nuevo que el admin active no se queda oculto', function () {
    // El agrupador no puede tragarse los campos que no conoce: si el admin
    // activa uno nuevo, tiene que salir en el formulario sin tocar codigo.
    DatosPrecargaConfig::create([
        'dpc_campo' => 'usu_numero_telefono',
        'dpc_etiqueta' => 'Teléfono',
        'dpc_obligatorio' => false,
        'dpc_activo' => true,
    ]);

    // Columna que existe en usuarios y todavia no esta en ningun bloque.
    DatosPrecargaConfig::create([
        'dpc_campo' => 'usu_direccion',
        'dpc_etiqueta' => 'Dirección',
        'dpc_obligatorio' => true,
        'dpc_activo' => true,
    ]);

    $bloques = DatosPrecargaConfig::enBloques(DatosPrecargaConfig::camposActivos());

    $todos = [];

    foreach ($bloques as $bloque) {
        foreach ($bloque['campos'] as $campo) {
            $todos[] = $campo->dpc_campo;
        }
    }

    fwrite(STDERR, "\n  campos repartidos: ".implode(', ', $todos)."\n");

    expect(count($todos))->toBe(DatosPrecargaConfig::camposActivos()->count());
});

test('un bloque sin campos habilitados no se dibuja', function () {
    DatosPrecargaConfig::create([
        'dpc_campo' => 'usu_numero_telefono',
        'dpc_etiqueta' => 'Teléfono',
        'dpc_obligatorio' => false,
        'dpc_activo' => true,
    ]);

    $bloques = DatosPrecargaConfig::enBloques(DatosPrecargaConfig::camposActivos());

    fwrite(STDERR, "\n  bloques con solo telefono: ".implode(' | ', array_keys($bloques))."\n");

    expect(array_keys($bloques))->not->toContain('Datos personales');
});

test('la pagina muestra los bloques en el orden y sin el campo de ficha', function () {
    $estudiante = estudiante('V-94000001');

    $html = $this->actingAs($estudiante)->get(route('user.datos-perfil'))
        ->assertOk()
        ->getContent();

    // La cedula abre el bloque de identificacion: es con lo que te buscan.
    expect($html)->toContain('Tu identificación');
    expect($html)->toContain('Tu nombre');
    expect($html)->toContain($estudiante->usu_numero_documento);

    // Los titulos salen en este orden, que es como se leen los datos de una
    // persona: primero quien es, luego como le localizan.
    $posIdentificacion = strpos($html, 'Tu identificación');
    $posNombre = strpos($html, 'Tu nombre');

    fwrite(STDERR, "\n  identificacion en {$posIdentificacion}, nombre en {$posNombre}\n");

    expect($posIdentificacion)->toBeLessThan($posNombre);

    // El campo "Numero de ficha" se quito: la columna no existe en la base y
    // salia siempre vacio con la promesa de que lo generaba el sistema.
    expect($html)->not->toContain('numero_ficha');
});
