<?php

namespace App\Mail;

use App\Models\Cita;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RecordatorioCitaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $cita;

    public function __construct(Cita $cita)
    {
        $this->cita = $cita;
    }

    public function build()
    {
        return $this->subject('⏰ Recordatorio: Tu cita en Control de Estudios es en 1 hora')
                    ->view('emails.citas.recordatorio');
    }
}