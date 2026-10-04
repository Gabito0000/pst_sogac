<?php

namespace App\Models;

use App\Models\Concerns\RegistraCambios;
use Illuminate\Database\Eloquent\Model;

class PreguntasFrecuentes extends Model
{
    use RegistraCambios;

    protected $fillable = ['pregunta', 'respuesta'];

    public const PAGINATE = 10;
}
