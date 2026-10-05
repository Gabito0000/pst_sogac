<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Recuperacion de contrasena para el modelo Usuario.
 *
 * El broker de Laravel se apoya en el metodo getEmailForPasswordReset() del
 * modelo, que devuelve usu_correo_electronico, y guarda el token en la tabla
 * password_reset_tokens.
 */
class PasswordResetController extends Controller
{
    /** Muestra el formulario donde se pide el correo con la cuenta. */
    public function requestForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Genera el token y notifica al correo indicado.
     *
     * No revela si el correo existe o no: siempre responde con el mismo
     * aviso para no filtrar que correos estan registrados.
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email']);

        Password::broker()->sendResetLink([
            'usu_correo_electronico' => $request->email,
        ]);

        return back()->with(
            'status',
            'Si ese correo tiene una cuenta registrada, te enviamos el enlace para restablecer tu contraseña.'
        );
    }

    /** Muestra el formulario para escribir la nueva contraseña. */
    public function resetForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /** Guarda la nueva contraseña cuando el token sigue vigente. */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $estado = Password::broker()->reset(
            [
                'usu_correo_electronico' => $request->email,
                'password' => $request->password,
                'password_confirmation' => $request->password_confirmation,
                'token' => $request->token,
            ],
            function (Usuario $usuario, string $contrasena): void {
                $usuario->usu_contrasena_hash = Hash::make($contrasena);
                $usuario->setRememberToken(Str::random(60));
                $usuario->save();

                event(new PasswordReset($usuario));
            }
        );

        if ($estado === Password::PASSWORD_RESET) {
            return redirect()
                ->route('login')
                ->with('status', 'Tu contraseña ha sido restablecida. Ya puedes iniciar sesión.');
        }

        return back()->withErrors(['email' => 'El enlace es inválido o ya expiró.']);
    }
}
