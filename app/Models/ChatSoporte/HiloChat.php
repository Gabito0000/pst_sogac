<?php

namespace App\Models\ChatSoporte;

use App\Models\Concerns\RegistraCambios;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Model;

class HiloChat extends Model
{
    use RegistraCambios;

    protected $table = 'hilos_chat';

    protected $primaryKey = 'hch_id';

    protected $fillable = [
        'hch_id_usuario', 'hch_id_admin', 'hch_estado', 'hch_etiqueta_tema', 'hch_fecha_solicitud_cierre',
    ];

    /**
     * La columna es timestamp pero no estaba casteada, así que llegaba como
     * string y cualquier format() o diffInDays() sobre ella fallaba.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hch_fecha_solicitud_cierre' => 'datetime',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'hch_id_usuario', 'usu_id');
    }

    public function admin()
    {
        return $this->belongsTo(Usuario::class, 'hch_id_admin', 'usu_id');
    }

    public function mensajes()
    {
        return $this->hasMany(MensajeChat::class, 'mch_id_hilo', 'hch_id');
    }

    /**
     * Los estados posibles de un hilo, con su texto para pantalla.
     *
     * Antes cada vista traducía el estado con ucfirst(str_replace('_', ' ', ...)),
     * lo que producía "Pendiente cierre", sin tilde y sin explicar que lo que
     * falta es la confirmación del estudiante.
     *
     * @return array<string, string>
     */
    public static function estadosDisponibles(): array
    {
        return [
            'pendiente' => 'Esperando atención',
            'activo' => 'En atención',
            'pendiente_cierre' => 'Pendiente de confirmación',
            'cerrado' => 'Cerrado',
        ];
    }

    /**
     * Clase de chip asociada a cada estado.
     *
     * @return array<string, string>
     */
    public static function chipsPorEstado(): array
    {
        return [
            'pendiente' => 'chip--warn',
            'activo' => 'chip--ok',
            'pendiente_cierre' => 'chip--neutro',
            'cerrado' => 'chip--neutro',
        ];
    }

    /**
     * Texto del estado, listo para mostrar.
     */
    public function getEstadoEtiquetaAttribute(): string
    {
        return self::estadosDisponibles()[$this->hch_estado]
            ?? ucfirst(str_replace('_', ' ', (string) $this->hch_estado));
    }

    /**
     * Clase CSS del chip para este estado.
     */
    public function getEstadoChipAttribute(): string
    {
        return self::chipsPorEstado()[$this->hch_estado] ?? 'chip--neutro';
    }
}
