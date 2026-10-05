<?php

namespace App\Models;

use App\Models\Concerns\RegistraCambios;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Solicitud extends Model
{
    use RegistraCambios;

    protected $table = 'solicitudes';

    protected $primaryKey = 'sol_id';

    public $timestamps = false;

    protected $fillable = [
        'sol_usu_id',
        'sol_tsi_id',
        'sol_lac_id',
        'sol_eso_id',
        'sol_id_seguimiento',
        'sol_motivo_detallado',
        'sol_prioridad',
        'sol_fecha_creacion',
        'sol_fecha_ultima_actualizacion',
        'sol_fecha_resolucion',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sol_fecha_creacion' => 'datetime',
            'sol_fecha_ultima_actualizacion' => 'datetime',
            'sol_fecha_resolucion' => 'datetime',
        ];
    }

    // Relaciones hacia arriba (Pertenece a...)
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'sol_usu_id', 'usu_id');
    }

    public function tipoSolicitud()
    {
        return $this->belongsTo(TipoSolicitud::class, 'sol_tsi_id', 'tsi_id');
    }

    public function lapsoAcademico()
    {
        return $this->belongsTo(LapsoAcademico::class, 'sol_lac_id', 'lac_id');
    }

    public function estadoActual()
    {
        return $this->belongsTo(EstadoSolicitud::class, 'sol_eso_id', 'eso_id');
    }

    // Relaciones hacia abajo (Tiene muchos...)
    public function historialEstados()
    {
        return $this->hasMany(HistorialEstadoSolicitud::class, 'hes_sol_id', 'sol_id');
    }

    public function documentaciones()
    {
        return $this->hasMany(Documentacion::class, 'doc_sol_id', 'sol_id');
    }

    // Una solicitud tiene como maximo una cita de validacion fisica
    public function cita()
    {
        return $this->hasOne(Cita::class, 'cit_sol_id', 'sol_id');
    }

    /**
     * Bitacora de cambios de esta solicitud: altas, ediciones y cambios de
     * estado. La genera el trait RegistraCambios, asi que aqui solo se declara
     * el enlace para poder leerla.
     */
    public function cambios(): HasMany
    {
        return $this->hasMany(HistorialCambio::class, 'hcm_sol_id', 'sol_id');
    }
}
