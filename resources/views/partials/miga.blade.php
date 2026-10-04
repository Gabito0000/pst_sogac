{{--
    Migas de pan.

    Uso:
        @include('partials.miga', ['miga' => [
            ['texto' => 'Preguntas frecuentes', 'ruta' => route('admin.preguntas.index')],
            ['texto' => 'Editar'],
        ]])

    El último elemento se pinta como texto plano porque es la página actual:
    un enlace a la propia URL no aporta nada y confunde al lector de pantalla.
--}}
@isset($miga)
    @if (count($miga) > 0)
        <nav class="miga" aria-label="Ruta de navegación">
            @foreach ($miga as $nivel => $paso)
                @if (! $loop->first)
                    <span class="miga__sep" aria-hidden="true">/</span>
                @endif

                @if (! $loop->last && ! empty($paso['ruta']))
                    <a href="{{ $paso['ruta'] }}">{{ $paso['texto'] }}</a>
                @else
                    <span class="miga__actual" @if ($loop->last) aria-current="page" @endif>{{ $paso['texto'] }}</span>
                @endif
            @endforeach
        </nav>
    @endif
@endisset
