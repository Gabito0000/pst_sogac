<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Controller base de la aplicación.
 *
 * AutorizaRequests habilita $this->authorize(), que es como se aplican las
 * politicas (ver app/Policies/SolicitudPolicy.php). Antes el control de
 * pertenencia se hacía a mano con abort(403) dentro de cada controller, y por
 * eso era fácil olvidarse en una pantalla nueva.
 */
abstract class Controller
{
    use AuthorizesRequests;
}
