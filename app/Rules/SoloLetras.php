<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Texto de persona: letras, espacios y los separadores que usan algunos
 * apellidos compuestos.
 *
 * Sirve para los nombres y apellidos, donde escribir "12345" o "???" no tiene
 * sentido. Acepta cualquier letra del alfabeto (incluidas las tildes y la ñ),
 * por eso trabaja con \p{L} de Unicode y no con A-Z.
 */
class SoloLetras implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Se descarta aquí el caso "solo espacios": required ya lo cubre, pero
        // nullable deja pasar un campo opcional enviado con blancos.
        if (trim((string) $value) === '') {
            return;
        }

        // El mira adelante (?=.*\p{L}) exige al menos una letra de verdad, para
        // que "---" o "   " no cuenten como nombre.
        if (! preg_match('/^(?=.*\p{L})[\p{L}\s\'\-]+$/u', $value)) {
            $fail('El campo :attribute solo puede contener letras.');
        }
    }
}
