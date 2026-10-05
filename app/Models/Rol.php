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
     * Roles que pueden ver el panel estadistico y el historial.
     * El taquillo queda fuera: solo procesa tramites.
     *
     * @return array<int, string>
     */
    public static function conEstadisticas(): array
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
     * Roles que pueden crear/editar requisitos y tramites.
     *
     * @return array<int, string>
     */
    public static function conCatalogos(): array
    {
        return [self::ADMINISTRADOR, self::ANALISTA];
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
            self::ADMINISTRADOR => 'Acceso total: estadisticas, historial, catalogos, aprobaciones, chat y asignacion de roles.',
            self::ANALISTA => 'Estadisticas, historial, solicitudes, requisitos, tramites, aprobaciones y chat.',
            self::TAQUILLERO => 'Procesa solicitudes: ver, aprobar o rechazar, atender el chat de soporte y agendar citas.',
            self::ESTUDIANTE => 'Panel estudiantil: Realizar y consultar sus propias solicitudes y citas.',
            default => '',
        };
    }
}
