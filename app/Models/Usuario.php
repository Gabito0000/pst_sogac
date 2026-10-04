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

    // ============================================================
    // Jerarquia de roles
    // Los roles posibles son: administrador, analista, taquillero
    // y estudiante. Solo los tres primeros entran al panel admin.
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
