<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Campo que solo admite dígitos: cédula, teléfono.
 *
 * Sin esta regla, "llamameya" pasa la validación de un teléfono porque el
 * atributo pattern del HTML solo actúa en el navegador: una petición hecha a
 * mano se lo salta y la base, que es varchar, lo guarda tal cual.
 */
class SoloDigitos implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (trim((string) $value) === '') {
            return;
        }

        if (! preg_match('/^\d+$/u', $value)) {
            $fail('El campo :attribute solo puede contener números.');
        }
    }
}
