{{--
    Hilo de mensajes compartido por el chat del estudiante y el del
    administrador. Antes estaba duplicado en chat_usuario y chat_admin con
    estilos inline idénticos, así que cualquier ajuste hadía que hacerse dos
    veces.

    Espera: $hilo (App\Models\ChatSoporte\HiloChat con 'mensajes' cargados).
--}}
<div class="chat-hilo" role="log" aria-live="polite" aria-label="Conversación">
    @forelse ($hilo->mensajes as $mensaje)
        @php $propio = $mensaje->mch_id_remitente === auth()->id(); @endphp

        <div class="chat-burbuja {{ $propio ? 'chat-burbuja--mia' : 'chat-burbuja--suya' }}">
            <span class="chat-remitente">
                {{ $propio ? 'Tú' : ($mensaje->remitente->usu_primer_nombre ?? 'Usuario') }}
            </span>

            {{-- whitespace-pre-line conserva los saltos de línea del mensaje --}}
            <span style="white-space: pre-line;">{{ $mensaje->mch_cuerpo }}</span>

            @if ($mensaje->mch_ruta_imagen)
                <img class="chat-burbuja__imagen"
                     src="{{ asset('storage/'.$mensaje->mch_ruta_imagen) }}"
                     alt="Evidencia adjunta por {{ $propio ? 'el estudiante' : 'el administrador' }}">
            @endif

            <span class="chat-burbuja__meta">
                {{ $mensaje->created_at->format('d/m/Y H:i') }}
            </span>
        </div>
    @empty
        <p class="estado-vacio" style="margin: auto 0;">
            Todavía no hay mensajes en este chat.
        </p>
    @endforelse
</div>
