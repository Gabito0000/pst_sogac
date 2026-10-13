@extends('layouts.plantilla_user')

@section('title', 'Mis datos')

@section('content')
    @include('partials.avisos')

    @include('partials.miga', [
        'miga' => [
            ['texto' => 'Inicio', 'ruta' => route('dashboard')],
            ['texto' => 'Mis datos'],
        ],
    ])

    <div class="pagina-head">
        <div>
            <h1 class="pagina-head__titulo">Mis datos</h1>
            <p class="pagina-head__desc">
                Cárgalos una sola vez y se adjuntarán solos a tus trámites:
                no volverás a escribirlos en cada solicitud.
            </p>
        </div>

        <div class="pagina-head__acciones">
            <a href="{{ route('dashboard') }}" class="btn btn--ghost">Volver al panel</a>
        </div>
    </div>

    {{-- Aviso de vigencia: los datos que el admin marcó con caducidad hay que
         refrescarlos de vez en cuando; los que nunca actualizó, llenarlos. --}}
    @if ($ultimaActualizacion === null)
        <div class="alert alert--warning">
            Todavía no has cargado tus datos de precarga. Complétalos aquí para que tus
            próximos trámites los adjunten automáticamente.
        </div>
    @elseif ($vencidos->isNotEmpty())
        <div class="alert alert--warning">
            Según la vigencia definida por la administración, estos datos ya perdieron validez:
            <strong>{{ $vencidos->pluck('dpc_etiqueta')->implode(', ') }}</strong>.
            Actualízalos para que tus trámites no salgan con datos desactualizados.
        </div>
    @endif

    <form action="{{ route('user.datos-perfil.update') }}" method="POST">
        @csrf

        {{-- 1. Identificacion: lo que la oficina usa para reconocerte. Se abre
             con la cedula porque es el dato con el que te buscan, y la carrera
             va aparte porque no la escribes tu. --}}
        <div class="card" style="margin-bottom: 20px;">
            <h2 class="card__title">Tu identificación</h2>
            <p class="card__sub">Con estos datos la oficina te ubica en el sistema.</p>

            <div class="form__row" style="margin-top: 16px;">
                <div class="field">
                    <label for="cedula">Cédula</label>
                    <input type="text" id="cedula" value="{{ $usuario->usu_numero_documento }}" disabled>
                    <span class="field__hint">Es con lo que te buscan. Si está mal, pide el cambio en el chat.</span>
                </div>
                <div class="field">
                    <label for="pnf">Carrera y trayecto</label>
                    <input type="text" id="pnf"
                           value="{{ trim(($usuario->usu_pnf ?: 'Sin PNF asignado').' · '.($usuario->usu_trayecto ?: 'sin trayecto')) }}"
                           disabled>
                    <span class="field__hint">Los asigna la coordinación. Si están mal, escribe por el chat.</span>
                </div>
            </div>
        </div>

        {{-- 2. Nombre: primero los nombres, luego los apellidos, en el mismo
             orden en que aparecen en la cédula. --}}
        <div class="card" style="margin-bottom: 20px;">
            <h2 class="card__title">Tu nombre</h2>
            <p class="card__sub">Tal como aparece en tu cédula.</p>

            <div class="form__row" style="margin-top: 16px;">
                <div class="field">
                    <label for="usu_primer_nombre">Primer nombre</label>
                    <input type="text" id="usu_primer_nombre" name="usu_primer_nombre" required
                           data-solo="letras" pattern="(?=.*\p{L})[\p{L}\s'\-]+" title="Solo letras."
                           maxlength="50" value="{{ old('usu_primer_nombre', $usuario->usu_primer_nombre) }}">
                </div>
                <div class="field">
                    <label for="usu_segundo_nombre">Segundo nombre</label>
                    <input type="text" id="usu_segundo_nombre" name="usu_segundo_nombre"
                           data-solo="letras" pattern="(?=.*\p{L})[\p{L}\s'\-]+" title="Solo letras."
                           maxlength="50" value="{{ old('usu_segundo_nombre', $usuario->usu_segundo_nombre) }}">
                </div>
            </div>

            <div class="form__row">
                <div class="field">
                    <label for="usu_primer_apellido">Primer apellido</label>
                    <input type="text" id="usu_primer_apellido" name="usu_primer_apellido" required
                           data-solo="letras" pattern="(?=.*\p{L})[\p{L}\s'\-]+" title="Solo letras."
                           maxlength="50" value="{{ old('usu_primer_apellido', $usuario->usu_primer_apellido) }}">
                </div>
                <div class="field">
                    <label for="usu_segundo_apellido">Segundo apellido</label>
                    <input type="text" id="usu_segundo_apellido" name="usu_segundo_apellido"
                           data-solo="letras" pattern="(?=.*\p{L})[\p{L}\s'\-]+" title="Solo letras."
                           maxlength="50" value="{{ old('usu_segundo_apellido', $usuario->usu_segundo_apellido) }}">
                </div>
            </div>

            <div class="form__row">
                <div class="field">
                    <label for="usu_correo_electronico">Correo electrónico</label>
                    <input type="email" id="usu_correo_electronico" name="usu_correo_electronico" required
                           maxlength="100" value="{{ old('usu_correo_electronico', $usuario->usu_correo_electronico) }}">
                    <span class="field__hint">Es tu usuario de acceso: si lo cambias, entra con el nuevo la próxima vez.</span>
                </div>
            </div>
        </div>

        {{-- 3. Y 4. Los campos de precarga, repartidos en bloques: primero los
             personales y despues los de contacto. El administrador decide
             cuales estan habilitados; ver App\Models\DatosPrecargaConfig::BLOQUES. --}}
        @if (! empty($bloques))
            @foreach ($bloques as $titulo => $bloque)
                <div class="card" style="margin-bottom: 20px;">
                    <h2 class="card__title">{{ $titulo }}</h2>
                    <p class="card__sub">{{ $bloque['descripcion'] }}</p>

                    <div class="form__row" style="margin-top: 16px;">
                        @foreach ($bloque['campos'] as $config)
                            <div class="field">
                                <label for="{{ $config->dpc_campo }}">
                                    {{ $config->dpc_etiqueta }}
                                    @if ($config->dpc_obligatorio)
                                        <span class="badge badge--rechazada">Obligatorio</span>
                                    @else
                                        <span class="badge badge--activo">Opcional</span>
                                    @endif
                                </label>

                                @if ($config->dpc_campo === 'usu_fecha_nacimiento')
                                    <input type="date" id="{{ $config->dpc_campo }}" name="{{ $config->dpc_campo }}"
                                           @required($config->dpc_obligatorio)
                                           value="{{ old($config->dpc_campo, $usuario->{$config->dpc_campo}) }}">
                                @else
                                    <input type="text" id="{{ $config->dpc_campo }}" name="{{ $config->dpc_campo }}"
                                           @required($config->dpc_obligatorio) maxlength="255"
                                           @if ($config->dpc_campo === 'usu_numero_telefono')
                                               data-solo="digitos" pattern="[0-9]*" title="Solo números."
                                           @endif
                                           value="{{ old($config->dpc_campo, $usuario->{$config->dpc_campo}) }}">
                                @endif

                                <span class="field__hint">
                                    @if ($config->dpc_vigencia_meses !== null)
                                        Vigente durante {{ $config->dpc_vigencia_meses }} meses tras cada actualización.
                                    @else
                                        No pierde vigencia.
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @else
            <div class="card" style="margin-bottom: 20px;">
                <h2 class="card__title">Datos que se adjuntan a tus trámites</h2>
                <p class="estado-vacio" style="text-align: left; margin-top: 12px;">
                    La administración todavía no ha habilitado ningún dato de precarga.
                </p>
            </div>
        @endif

        <div class="actions">
            <button type="submit" class="btn btn--primary">Guardar cambios</button>
            <a href="{{ route('dashboard') }}" class="btn btn--ghost">Cancelar</a>
        </div>
    </form>
@endsection

@push('scripts')
    <script defer src="{{ asset('js/campos-tipo.js') }}?v={{ filemtime(public_path('js/campos-tipo.js')) }}"></script>
@endpush