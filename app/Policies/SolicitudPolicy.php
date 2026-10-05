<?php

namespace App\Policies;

use App\Models\Solicitud;
use App\Models\Usuario;

/**
 * Quién puede ver el historial de una solicitud.
 *
 * El estudiante solo ve las suyas; el administrador ve todas, y esa segunda
 * vía es la que usa la bitacora de cambios para llevar al detalle de un tramite
 * concreto aunque no sea suyo.
 */
class SolicitudPolicy
{
    public function view(Usuario $usuario, Solicitud $solicitud): bool
    {
        return $usuario->esAdministrativo()
            || (int) $solicitud->sol_usu_id === (int) $usuario->usu_id;
    }
}
