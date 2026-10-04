<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifica que el usuario autenticado tenga uno de los roles permitidos.
 *
 * Uso: ->middleware('rol:administrador,analista')
 */
class VerificarRol
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();

        // Sin sesion: lo mandamos al login
        if (! $usuario) {
            return redirect()->route('login');
        }

        // Rol no autorizado: 403
        if (! in_array($usuario->usu_rol, $roles, true)) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
