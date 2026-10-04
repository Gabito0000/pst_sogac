<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EsAdmin
{
    /**
     * Solo deja pasar a usuarios con el rol 'admin'.
     *
     * Si no hay nadie autenticado lo mandamos al login; si hay alguien
     * autenticado pero no es admin, le negamos el acceso con un 403.
     * Esto reemplaza los abort(403) sueltos que había en algunos controllers.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if ($request->user()->usu_rol !== 'admin') {
            abort(403, 'Acceso restringido a administradores.');
        }

        return $next($request);
    }
}
