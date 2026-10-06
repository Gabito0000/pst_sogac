<x-mail::message>
# ¡Nueva respuesta en tu ticket!

Hola,

Un administrador acaba de responder a tu solicitud en el **Ticket #{{ $hiloId }}**. 

Ingresa a la plataforma para leer el mensaje, enviar nueva evidencia o continuar con la conversación.

{{-- El nombre de la ruta es 'user.ayuda.chat.mostrar': el chat del estudiante
     vive bajo el prefijo 'ayuda'. Con 'user.chat.mostrar', que no existe, la
     plantilla reventaba al renderizarse y, como el correo se envia al
     responder, la respuesta del administrador tumbaba la peticion entera. --}}
<x-mail::button :url="route('user.ayuda.chat.mostrar', $hiloId)">
    Ver respuesta
</x-mail::button>

*Si tu problema ya fue resuelto, recuerda que puedes confirmar el cierre del ticket desde la plataforma.*

Saludos,<br>
El equipo de Soporte Técnico
</x-mail::message>