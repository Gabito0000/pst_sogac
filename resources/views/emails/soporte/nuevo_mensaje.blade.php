<x-mail::message>
# ¡Nueva respuesta en tu ticket!

Hola,

Un administrador acaba de responder a tu solicitud en el **Ticket #{{ $hiloId }}**. 

Ingresa a la plataforma para leer el mensaje, enviar nueva evidencia o continuar con la conversación.

<x-mail::button :url="route('user.chat.mostrar', $hiloId)">
Ver respuesta
</x-mail::button>

*Si tu problema ya fue resuelto, recuerda que puedes confirmar el cierre del ticket desde la plataforma.*

Saludos,<br>
El equipo de Soporte Técnico
</x-mail::message>