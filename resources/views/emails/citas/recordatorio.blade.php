<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <h2 style="color: #b91c1c;">¡Hola, {{ $cita->usuario->usu_nombre }}!</h2>
    <p>Este es un recordatorio automático del sistema. Tu cita presencial en la taquilla de Control de Estudios comenzará en aproximadamente 1 hora.</p>
    
    <div style="background-color: #f9fafb; border-left: 4px solid #b91c1c; padding: 15px; margin: 20px 0;">
        <p style="margin: 0 0 10px 0;"><strong>📅 Fecha:</strong> {{ $cita->cit_fecha_hora->format('d/m/Y') }}</p>
        <p style="margin: 0 0 10px 0;"><strong>⏰ Hora:</strong> {{ $cita->cit_fecha_hora->format('h:i A') }}</p>
        <p style="margin: 0;"><strong>📝 Trámite:</strong> {{ $cita->cit_sol_id ? 'Vincular a Solicitud' : $cita->cit_motivo }}</p>
    </div>

    <p>Por favor, acércate a la taquilla con puntualidad. Si no te presentas a tu bloque de hora, el personal registrará una inasistencia en tu historial.</p>
    <p>Saludos,<br><strong>Universidad Politécnica Territorial "Juan de Jesús Montilla"</strong></p>
</body>
</html>