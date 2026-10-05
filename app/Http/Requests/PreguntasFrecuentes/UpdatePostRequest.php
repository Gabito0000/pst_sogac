<?php

namespace App\Http\Requests\PreguntasFrecuentes;

class UpdatePostRequest extends CreatePostRequest
{
    /*
     * Las reglas y los mensajes se heredan de CreatePostRequest: validar un
     * alta y una edición con el mismo criterio evita que ambos formularios
     * se desincronicen.
     */
}
