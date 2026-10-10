<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    protected $table = 'citas';

    protected $primaryKey = 'cit_id';

    public $timestamps = false;

    protected $fillable = [
        'cit_usu_id',
        'cit_sol_id',
        'cit_motivo',
        'cit_fecha_hora',
        'cit_estado',
    ];

    protected $casts = [
        'cit_fecha_hora' => 'datetime',
    ];

    // Una cita siempre pertenece a un estudiante
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'cit_usu_id', 'usu_id');
    }

    // Una cita PUEDE pertenecer a una solicitud
    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class, 'cit_sol_id', 'sol_id');
    }
}