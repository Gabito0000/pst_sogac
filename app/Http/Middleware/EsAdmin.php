<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restringe el acceso al personal administrativo.
 *
 * Originally solo aceptaba el rol 'admin'. Con la jerarquia de roles
 * (administrador > analista > taquillero) se admiten los tres, que son
 * los que pueden entrar al panel. El estudiante sigue fuera.
 *
 * Para exigir un rol concreto se usa el middleware 'rol':
 *   ->middleware('rol:administrador')   // solo el administrador
 */
class EsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if (! $request->user()->esAdministrativo()) {
            abort(403, 'Acceso restringido al personal administrativo.');
        }

        return $next($request);
    }
}
