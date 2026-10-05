<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';

    protected $primaryKey = 'usu_id';

    public $timestamps = false;

    protected $fillable = [
        'usu_rol',
        'usu_tdo_id',
        'usu_primer_nombre',
        'usu_segundo_nombre',
        'usu_primer_apellido',
        'usu_segundo_apellido',
        'usu_numero_documento',
        'usu_correo_electronico',
        'usu_numero_telefono',
        'usu_contrasena_hash',
        'usu_estado_cuenta',
        'usu_fecha_registro',
        'usu_ultimo_acceso',
    ];

    public function getEmailForPasswordReset()
    {
        return $this->usu_correo_electronico;
    }

    public function hilosComoUsuario()
    {
        return $this->hasMany(HiloChat::class, 'hch_id_usuario', 'usu_id');
    }

    public function hilosComoAdmin()
    {
        return $this->hasMany(HiloChat::class, 'hch_id_admin', 'usu_id');
    }

    // Un Usuario pertenece a un Tipo de Documento
    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class, 'usu_tdo_id', 'tdo_id');
    }

    // Un Usuario (Estudiante) tiene muchas Solicitudes
    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class, 'sol_usu_id', 'usu_id');
    }

    // Un Usuario (Admin) registra/modifica muchos historiales de estados
    public function historialesModificados()
    {
        return $this->hasMany(HistorialEstadoSolicitud::class, 'hes_usu_id_responsable', 'usu_id');
    }

    /**
     * Nombre y apellidos en un solo valor.
     *
     * Las cuatro columnas de nombre estan separadas en la tabla porque cada
     * parte se usa por separado (buscar por primer nombre, ordenar por
     * apellido), asi que para mostrarlas juntas hay que unirlas en algun lado.
     * Si estan vacias se cae al correo, que es el dato con el que siempre se
     * puede identificar a una persona.
     */
    public function getNombreCompletoAttribute(): string
    {
        $nombre = collect([
            $this->usu_primer_nombre,
            $this->usu_segundo_nombre,
            $this->usu_primer_apellido,
            $this->usu_segundo_apellido,
        ])->filter()->implode(' ');

        return $nombre !== '' ? $nombre : (string) $this->usu_correo_electronico;
    }

    // ¡CRUCIAL! Le decimos a Laravel qué columna guarda la contraseña encriptada
    public function getAuthPassword()
    {
        return $this->usu_contrasena_hash;
    }

    // Columna del token "recuerdame" (convencion usu_ del proyecto)
    public function getRememberTokenName()
    {
        return 'usu_remember_token';
    }

    // Le decimos a Laravel cuál es tu clave primaria personalizada
    public function getAuthIdentifierName()
    {
        return 'usu_id';
    }

    // Esto le dice al cartero de Laravel a qué dirección exacta enviar el mensaje
    public function routeNotificationForMail($notification = null)
    {
        return $this->usu_correo_electronico;
    }

    // ============================================================
    // Jerarquia de roles
    // Roles: administrador > analista > taquillero, mas estudiante.
    // Solo los tres primeros entran al panel administrativo.
    // Definidos en App\Models\Rol.
    // ============================================================

    public function esAdministrador(): bool
    {
        return $this->usu_rol === Rol::ADMINISTRADOR;
    }

    public function esAnalista(): bool
    {
        return $this->usu_rol === Rol::ANALISTA;
    }

    public function esTaquillero(): bool
    {
        return $this->usu_rol === Rol::TAQUILLERO;
    }

    public function esEstudiante(): bool
    {
        return $this->usu_rol === Rol::ESTUDIANTE;
    }

    /**
     * Pertenece a la jerarquia administrativa (administrador, analista
     * o taquillero). Sustituye al antiguo chequeo "usu_rol === 'admin'".
     */
    public function esAdministrativo(): bool
    {
        return in_array($this->usu_rol, Rol::administrativos(), true);
    }
}
