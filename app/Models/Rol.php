<?php

namespace App\Models;

/**
 * Roles del sistema y permisos asociados.
 *
 * Jerarquia administrativa (de mayor a menor):
 *   administrador -> analista -> taquillero
 *
 * Los estudiantes no pertenecen a la jerarquia: usan el panel
 * de usuario (/user/dashboard), no el panel administrativo.
 */
class Rol
{
    public const ADMINISTRADOR = 'administrador';

    public const ANALISTA = 'analista';

    public const TAQUILLERO = 'taquillero';

    public const ESTUDIANTE = 'estudiante';

    /**
     * Roles con acceso al panel administrativo, de mayor a menor jerarquia.
     *
     * @return array<int, string>
     */
    public static function administrativos(): array
    {
        return [self::ADMINISTRADOR, self::ANALISTA, self::TAQUILLERO];
    }

    /**
     * Roles que pueden ver el panel estadistico y el historial de cambios.
     *
     * El taquillo queda fuera: procesa tramites y chat, no lleva lavision
     * general del sistema. El menu y las rutas leen de aqui, para que no se
     * desmienten entre si.
     *
     * @return array<int, string>
     */
    public static function conEstadisticas(): array
    {
        return [self::ADMINISTRADOR];
    }

    /**
     * Roles que ven las solicitudes de escalado de los chats.
     *
     * Entran el administrador y el analista porque son los niveles que pueden
     * atender un caso que el taquillo no resuelve. El taquillo queda fuera a
     * proposito: si el estudiante pidio que lo atienda alguien de mas arriba,
     * no tiene sentido que se lo muestre a quien pidio superarse.
     *
     * @return array<int, string>
     */
    public static function venEscaladosDeChat(): array
    {
        return [self::ADMINISTRADOR, self::ANALISTA];
    }

    /**
     * Roles que pueden aprobar o rechazar solicitudes.
     *
     * @return array<int, string>
     */
    public static function conAprobacion(): array
    {
        return [self::ADMINISTRADOR, self::ANALISTA, self::TAQUILLERO];
    }

    /**
     * Roles que pueden ver la bitacora de cambios.
     *
     * Misma lista que conEstadisticas(): ambos son lecturas reserved del
     * administrador. Se deja como metodo aparte para que la intencion se lea
     * en el sitio donde se usa.
     *
     * @return array<int, string>
     */
    public static function conBitacora(): array
    {
        return [self::ADMINISTRADOR];
    }

    /**
     * Roles que pueden crear y editar catalogos: tramites y requisitos, y las
     * preguntas frecuentes que el personal publica.
     *
     * @return array<int, string>
     */
    public static function conCatalogos(): array
    {
        return [self::ADMINISTRADOR, self::ANALISTA];
    }

    /**
     * Roles con acceso al portal del estudiante (/user/*).
     *
     * El personal usa el panel administrativo. Sin esta separacion, un
     * administrador que entra a /user/dashboard ve el portal estudiantil con
     * datos vacios, porque no tiene solicitudes propias: la ruta respondia 200
     * y la pantalla no significaba nada.
     *
     * @return array<int, string>
     */
    public static function studentPortal(): array
    {
        return [self::ESTUDIANTE];
    }

    /**
     * Todos los roles existentes, para los selectores de la UI.
     *
     * @return array<int, string>
     */
    public static function todos(): array
    {
        return [self::ADMINISTRADOR, self::ANALISTA, self::TAQUILLERO, self::ESTUDIANTE];
    }

    /**
     * Nombres legibles de los roles, en el orden en que se listan.
     *
     * @return array<string, string>
     */
    public static function etiquetaDeTodos(): array
    {
        $mapa = [];

        foreach (self::todos() as $rol) {
            $mapa[$rol] = self::etiqueta($rol);
        }

        return $mapa;
    }

    /**
     * Nombres legibles de una lista de roles, para pasarlos al middleware
     * 'rol:...' y al menu sin escribir la lista a mano en cada sitio.
     *
     * @param  array<int, string>  $roles
     */
    public static function implode(array $roles): string
    {
        return implode(',', $roles);
    }

    /**
     * Etiqueta legible para mostrar en pantalla.
     */
    public static function etiqueta(string $rol): string
    {
        return match ($rol) {
            self::ADMINISTRADOR => 'Administrador',
            self::ANALISTA => 'Analista',
            self::TAQUILLERO => 'Taquillero',
            self::ESTUDIANTE => 'Estudiante',
            default => ucfirst($rol),
        };
    }

    /**
     * Descripcion de las capacidades del rol (pantalla de roles).
     */
    public static function descripcion(string $rol): string
    {
        return match ($rol) {
            self::ADMINISTRADOR => 'Acceso total: estadisticas, bitacora de cambios, catalogos, aprobaciones, chat y gestion de usuarios.',
            self::ANALISTA => 'Solicitudes, solicitudes tratadas, catalogos, aprobaciones y chat.',
            self::TAQUILLERO => 'Procesa solicitudes: ver, aprobar o rechazar, y atiende el chat de soporte.',
            self::ESTUDIANTE => 'Panel estudiantil: Realizar y consultar sus propias solicitudes y citas.',
            default => '',
        };
    }
}
