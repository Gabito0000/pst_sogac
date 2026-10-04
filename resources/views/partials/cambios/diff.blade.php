{{--
    Diferencias de un cambio de la bitacora, en tabla "campo: antes → después".

    Es un parcial y no un bucle en cada vista porque la misma lista se pinta en
    dos sitios: el listado del administrador y la ficha de la solicitud.

    Espera: $cambios (lista de {campo, anterior, nuevo}), y opcionalmente
    $etiquetaDiferencias (texto del encabezado, por defecto "Qué cambió").

    Las diferencias ya llegan traducidas desde App\Services\RegistroCambios, asi
    que aquí no hay que resolver identificadores: lo que se muestra es
    "Estado: pendiente → aprobada" y no "sol_eso_id: 1 → 2".
--}}
@php
    $lista = $cambios ?? [];
    $titulo = $etiquetaDiferencias ?? 'Qué cambió';
@endphp

@if (count($lista) > 0)
    <div class="diff">
        <p class="diff__titulo">{{ $titulo }}</p>

        <table class="diff__tabla">
            <thead>
                <tr>
                    <th class="diff__col-campo">Campo</th>
                    <th class="diff__col-valor">Valor anterior</th>
                    <th class="diff__col-flecha" aria-hidden="true"></th>
                    <th class="diff__col-valor">Valor nuevo</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($lista as $diferencia)
                    <tr>
                        <td class="diff__campo">{{ $diferencia['campo'] }}</td>
                        <td class="diff__valor diff__valor--anterior">{{ $diferencia['anterior'] }}</td>
                        <td class="diff__flecha" aria-hidden="true">&rarr;</td>
                        <td class="diff__valor diff__valor--nuevo">{{ $diferencia['nuevo'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif