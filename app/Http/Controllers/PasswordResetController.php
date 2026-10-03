<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    // Muestra la vista pidiendo el correo
    public function requestForm()
    {
        return view('auth.forgot-password');
    }

    // Envía el correo con el token
    // Envía el correo con el token
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::broker()->sendResetLink([
            'usu_correo_electronico' => $request->email
        ]);

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', '¡Te hemos enviado el enlace de recuperación por correo!');
        }

        return back()->withErrors(['email' => 'No encontramos un usuario con ese correo.']);
    }

    // Muestra el formulario para escribir la nueva clave
    public function resetForm(Request $request, $token)
    {
        return view('auth.reset-password', [
            'token' => $token, 
            'email' => $request->email
        ]);
    }
    
    // Actualiza la contraseña
    public function updatePassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        // SOLUCIÓN: Hacemos el mismo mapeo aquí
        $status = Password::broker()->reset(
            [
                'usu_correo_electronico' => $request->email,
                'password'              => $request->password,
                'password_confirmation' => $request->password_confirmation,
                'token'                 => $request->token
            ],
            function ($user, $password) {
                // Laravel ejecuta esto si el token es válido
                $user->usu_contrasena_hash = Hash::make($password);
                $user->setRememberToken(Str::random(60));
                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'Tu contraseña ha sido restablecida. Ya puedes iniciar sesión.');
        }

        return back()->withErrors(['email' => 'El token es inválido o ha expirado.']);
    }
}