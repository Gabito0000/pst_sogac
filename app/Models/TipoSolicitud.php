<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoSolicitud extends Model
{
    protected $table = 'tipo_solicitudes';
    protected $primaryKey = 'tsi_id';
    public $timestamps = false;

    protected $fillable = [
        'tsi_nombre_tipo', 
        'tsi_descripcion', 
        'tsi_tiempo_estimado_dias', 
        'tsi_requiere_aprobacion_especial', 
        'tsi_estado_tipo',
        'tsi_fecha_inicio',
        'tsi_fecha_fin',
    ];

    protected $casts = [
        'tsi_fecha_inicio' => 'date',
        'tsi_fecha_fin' => 'date',
    ];

    /**
     * Un trámite está "disponible para el estudiante" solo si:
     * 1. El admin lo marcó como activo, Y
     * 2. Hoy cae dentro de su ventana de fechas (si tiene fechas configuradas)
     * Esto es lo que usará la pantalla del estudiante para decidir qué mostrar.
     */
    public function estaDisponible(): bool
    {
        if ($this->tsi_estado_tipo !== 'activo') {
            return false;
        }

        $hoy = now()->startOfDay();

        if ($this->tsi_fecha_inicio && $hoy->lt($this->tsi_fecha_inicio)) {
            return false; // Todavía no empieza
        }

        if ($this->tsi_fecha_fin && $hoy->gt($this->tsi_fecha_fin)) {
            return false; // Ya cerró
        }

        return true;
    }
    // Un Tipo de Solicitud tiene muchos Requisitos (Muchos a Muchos)
    public function requisitos()
    {
        return $this->belongsToMany(Requisito::class, 'tipo_solicitud_requisitos', 'tsr_tsi_id', 'tsr_req_id')
                    ->withPivot('tsr_es_obligatorio');
    }

    // Un Tipo de Solicitud tiene muchas Solicitudes hechas por los usuarios
    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class, 'sol_tsi_id', 'tsi_id');
    }
}