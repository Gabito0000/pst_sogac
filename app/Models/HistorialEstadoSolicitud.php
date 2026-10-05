<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialEstadoSolicitud extends Model
{
    protected $table = 'historial_estado_solicitudes';

    protected $primaryKey = 'hes_id';

    public $timestamps = false;

    protected $fillable = [
        'hes_sol_id',
        'hes_usu_id_responsable',
        'hes_eso_id_anterior',
        'hes_eso_id_nuevo',
        'hes_observaciones_comentarios',
        'hes_fecha_cambio',
    ];

    /**
     * Sin este cast la fecha llega como texto y cualquier ->format() revienta.
     * Los histogramas ya usan Carbon::parse(), asi que castear no les rompe nada.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hes_fecha_cambio' => 'datetime',
        ];
    }

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class, 'hes_sol_id', 'sol_id');
    }

    public function responsable()
    {
        return $this->belongsTo(Usuario::class, 'hes_usu_id_responsable', 'usu_id');
    }

    public function estadoAnterior()
    {
        return $this->belongsTo(EstadoSolicitud::class, 'hes_eso_id_anterior', 'eso_id');
    }

    public function estadoNuevo()
    {
        return $this->belongsTo(EstadoSolicitud::class, 'hes_eso_id_nuevo', 'eso_id');
    }
}
