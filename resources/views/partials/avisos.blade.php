{{--
    Avisos unificados de la aplicación.

    Antes cada vista repetía su propio bloque y cada una leía una clave de
    sesión distinta: los CRUD de administración usaban 'success' y las
    preguntas frecuentes usaban 'message'. Este parcial acepta las cinco
    claves para que ninguna pantalla se quede sin mostrar su confirmación.

    Espera: session('error'), session('success'), session('info'),
    session('warning'), session('message') y los errores de validación.
--}}
@php
    $avisos = [
        'success' => 'alert--success',
        'error' => 'alert--error',
        'info' => 'alert--info',
        'warning' => 'alert--warning',
        'message' => 'alert--info',
    ];
@endphp

@if ($errors->any())
    <div class="alert alert--error">
        <strong>Revisa estos campos:</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@foreach ($avisos as $clave => $clase)
    @if (session($clave))
        <div class="alert {{ $clase }}">{{ session($clave) }}</div>
    @endif
@endforeach
