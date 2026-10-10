<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cita;
use App\Mail\RecordatorioCitaMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class NotificarCitas extends Command
{
    protected $signature = 'citas:notificar';
    protected $description = 'Busca citas que ocurran en exactamente 1 hora y envía recordatorios.';

    public function handle()
    {
        // Buscamos la hora exacta que será dentro de 1 hora, cortando los segundos
        $horaObjetivo = Carbon::now()->addHour()->startOfMinute();

        $citas = Cita::where('cit_estado', 'pendiente')
            ->where('cit_fecha_hora', $horaObjetivo)
            ->with('usuario')
            ->get();

        if($citas->isEmpty()) {
            $this->info('No hay citas pendientes para las ' . $horaObjetivo->format('H:i'));
            return;
        }

        foreach ($citas as $cita) {
            if ($cita->usuario->usu_correo_electronico) {
                Mail::to($cita->usuario->usu_correo_electronico)->send(new RecordatorioCitaMail($cita));
                $this->info("Recordatorio enviado a: " . $cita->usuario->usu_correo_electronico);
            }
        }
    }
}