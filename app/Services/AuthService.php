<?php

namespace App\Services;

use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Procesa el inicio de sesión con bloqueo y último acceso.
     */
    public function attemptLogin(string $identificador, string $password, bool $remember): bool
    {
        // 1. Determinar si el identificador es correo o documento[cite: 5]
        $campoAuth = filter_var($identificador, FILTER_VALIDATE_EMAIL) 
            ? 'usu_correo_electronico' 
            : 'usu_numero_documento';

        // 2. Buscar al usuario
        $usuario = Usuario::where($campoAuth, $identificador)->first();

        if (!$usuario) {
            return false;
        }

        // 3. Verificar si la cuenta está bloqueada permanentemente[cite: 4, 5]
        if ($usuario->usu_estado_cuenta === 'bloqueado_permanente') {
            throw ValidationException::withMessages([
                'identificador' => 'Tu cuenta ha sido bloqueada permanentemente por múltiples intentos fallidos. Contacta a soporte.',
            ]);
        }

        // 4. Lógica de Rate Limiting (Bloqueos)
        $throttleKey = 'login_attempts:' . $usuario->usu_id;
        $strikesKey = 'login_strikes:' . $usuario->usu_id;

        // Si ya superó los 3 intentos (Bloqueo temporal de 1 hora / 3600 segundos)
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $strikes = Cache::increment($strikesKey);

            // Si es la segunda vez que se le bloquea temporalmente, pasa a bloqueo permanente
            if ($strikes >= 2) {
                $usuario->update(['usu_estado_cuenta' => 'bloqueado_permanente']);
                Cache::forget($throttleKey);
                Cache::forget($strikesKey);
                
                throw ValidationException::withMessages([
                    'identificador' => 'Cuenta bloqueada permanentemente por reincidencia.',
                ]);
            }

            $segundos = RateLimiter::availableIn($throttleKey);
            $minutos = ceil($segundos / 60);
            throw ValidationException::withMessages([
                'identificador' => "Cuenta bloqueada temporalmente. Intenta en {$minutos} minutos.",
            ]);
        }

        // 5. Intentar autenticar
        $credenciales = [
            $campoAuth => $identificador,
            'password' => $password,
        ];

        if (Auth::attempt($credenciales, $remember)) {
            // Éxito: Limpiar bloqueos y actualizar último acceso[cite: 4, 5]
            RateLimiter::clear($throttleKey);
            Cache::forget($strikesKey);
            
            $usuario->update(['usu_ultimo_acceso' => now()]);
            
            return true;
        }

        // Fallo: Registrar intento fallido
        RateLimiter::hit($throttleKey, 3600); // 3600s = 1 hora de castigo si llega a 3
        return false;
    }

    /**
     * Registra un nuevo usuario.
     */
    public function registerUser(array $data): Usuario
    {
        return Usuario::create([
            'usu_rol'                => 'estudiante', //[cite: 5]
            'usu_tdo_id'             => $data['tipo_documento'],
            'usu_primer_nombre'      => $data['nombre'],
            'usu_segundo_nombre'     => $data['segundo_nombre'] ?? null, //[cite: 5]
            'usu_primer_apellido'    => $data['apellido'],
            'usu_segundo_apellido'   => $data['segundo_apellido'] ?? null, //[cite: 5]
            'usu_numero_documento'   => $data['documento'],
            'usu_correo_electronico' => $data['email'],
            'usu_numero_telefono'    => $data['telefono'] ?? null, //[cite: 5]
            'usu_contrasena_hash'    => Hash::make($data['password']), //[cite: 4, 5]
            'usu_estado_cuenta'      => 'activo' //[cite: 5]
        ]);
    }
}