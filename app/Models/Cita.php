<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    protected $table = 'citas';

    protected $primaryKey = 'cit_id';

    public $timestamps = false;

    protected $fillable = [
        'cit_sol_id',
        'cit_fecha_hora',
        'cit_lugar',
        'cit_estado',
    ];

    protected $casts = [
        'cit_fecha_hora' => 'datetime',
    ];

    // Una cita pertenece a una solicitud
    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class, 'cit_sol_id', 'sol_id');
    }
}
