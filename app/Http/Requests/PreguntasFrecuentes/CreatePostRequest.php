<?php

namespace App\Http\Requests\PreguntasFrecuentes;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreatePostRequest extends FormRequest
{
    /**
     * La autorización la resuelve el middleware 'admin' del grupo /admin.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // La columna es varchar(255): 200 deja margen para un título legible.
            'pregunta' => 'required|string|max:200',
            // La columna es TEXT: el límite anterior de 255 recortaba respuestas
            // que la base de datos habría aceptado sin problema.
            'respuesta' => 'required|string|max:4000',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'pregunta.required' => 'Escribe la pregunta que verá el estudiante.',
            'pregunta.max' => 'La pregunta no puede superar los 200 caracteres.',
            'respuesta.required' => 'Escribe la respuesta que leyó el estudiante.',
            'respuesta.max' => 'La respuesta no puede superar los 4000 caracteres.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'pregunta' => 'pregunta',
            'respuesta' => 'respuesta',
        ];
    }
}
