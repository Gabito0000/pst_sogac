<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NuevoMensajeSoporte extends Mailable
{
    use Queueable, SerializesModels;

    public $hiloId;

    public function __construct($hiloId)
    {
        $this->hiloId = $hiloId;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Tienes una nueva respuesta en Soporte!',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.soporte.nuevo_mensaje',
        );
    }
}