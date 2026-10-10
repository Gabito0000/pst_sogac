@extends('layouts.plantilla_user')
@section('title', 'Agendar Cita')

@section('content')
<style>
    /* Estilos nativos para el formulario */
    .form-container { background: white; border: 1px solid #f3f4f6; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 30px; }
    .btn-back { color: #6b7280; text-decoration: none; display: inline-flex; align-items: center; font-size: 14px; font-weight: 500; margin-bottom: 16px; transition: color 0.2s; }
    .btn-back:hover { color: #374151; }
    .page-title { font-size: 28px; font-weight: 700; color: #1f2937; margin: 0 0 5px 0; }
    .page-desc { color: #6b7280; margin: 0 0 24px 0; }
    
    .step-title { font-size: 18px; font-weight: 700; color: #1f2937; display: flex; align-items: center; margin-bottom: 20px; }
    .step-badge { background-color: #fee2e2; color: #b91c1c; width: 28px; height: 28px; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-size: 14px; margin-right: 10px; }
    
    .options-grid { display: grid; grid-template-columns: 1fr; gap: 15px; margin-bottom: 25px; padding-left: 38px; }
    @media (min-width: 768px) { .options-grid { grid-template-columns: 1fr 1fr; } }
    
    .option-card { border: 1px solid #e5e7eb; border-radius: 8px; padding: 15px; display: flex; align-items: flex-start; gap: 12px; cursor: pointer; transition: border-color 0.2s, background-color 0.2s; }
    .option-card:hover { background-color: #f9fafb; }
    .option-card input[type="radio"] { margin-top: 4px; accent-color: #b91c1c; }
    .option-content strong { display: block; color: #111827; margin-bottom: 4px; }
    .option-content p { margin: 0; font-size: 12px; color: #6b7280; }
    
    .input-group { padding-left: 38px; margin-bottom: 35px; }
    .input-label { display: block; font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 8px; }
    .form-control { width: 100%; max-width: 500px; padding: 10px 15px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; color: #1f2937; background-color: #f9fafb; transition: border-color 0.2s; }
    .form-control:focus { outline: none; border-color: #b91c1c; background-color: #fff; }
    
    .divider { border: 0; border-top: 1px solid #f3f4f6; margin: 30px 0; }
    
    /* Solución a los botones de horarios (Flexbox en lugar de Grid) */
    .bloques-grid { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 15px; width: 100%; }
    .btn-bloque { flex: 0 1 120px; background: white; border: 1px solid #e5e7eb; border-radius: 10px; padding: 12px 8px; cursor: pointer; text-align: center; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .btn-bloque:hover { border-color: #b91c1c; background-color: #fef2f2; }
    .btn-bloque span.hora { display: block; font-weight: 700; color: #1f2937; font-size: 15px; }
    .btn-bloque:hover span.hora { color: #b91c1c; }
    .btn-bloque span.cupos { display: block; font-size: 11px; text-transform: uppercase; color: #6b7280; margin-top: 4px; }

    /* Modal blindado (Usando display en lugar de opacity) */
    .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: none; align-items: center; justify-content: center; z-index: 999999; }
    .modal-overlay.active { display: flex !important; }
    .modal-content { background: white; padding: 25px; border-radius: 12px; max-width: 400px; width: 90%; box-shadow: 0 10px 25px rgba(0,0,0,0.15); }
    .modal-title { font-size: 20px; font-weight: 700; color: #1f2937; margin: 0 0 12px 0; }
    .modal-text { font-size: 15px; color: #4b5563; margin: 0 0 24px 0; line-height: 1.5; }
    .modal-actions { display: flex; justify-content: flex-end; gap: 12px; }
    .btn-cancel { padding: 10px 16px; border: 1px solid #d1d5db; background: white; border-radius: 8px; cursor: pointer; color: #374151; font-weight: 600; font-size: 14px; transition: background 0.2s; }
    .btn-cancel:hover { background: #f3f4f6; }
    .btn-confirm { padding: 10px 16px; border: none; background: #b91c1c; border-radius: 8px; cursor: pointer; color: white; font-weight: 600; font-size: 14px; transition: background 0.2s; }
    .btn-confirm:hover { background: #991b1b; }
</style>

<!-- Encabezado -->
<div>
    <a href="{{ route('user.citas') }}" class="btn-back">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: 4px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Volver a mis citas
    </a>
    <h1 class="page-title">Agendar Cita Presencial</h1>
    <p class="page-desc">Completa los pasos para reservar tu bloque de tiempo en taquilla.</p>
</div>

@if($errors->any())
<div style="background-color: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 24px; font-size: 14px;">
    <ul style="margin: 0; padding-left: 20px;">
        @foreach($errors->all() as $error)
            <li style="font-weight: 500;">{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="form-container">
    <!-- PASO 1 -->
    <h2 class="step-title"><span class="step-badge">1</span> ¿Qué trámite deseas realizar?</h2>
    <div class="options-grid">
        <label class="option-card">
            <input type="radio" name="tipo_tramite" value="solicitud" checked onchange="cambiarTipoTramite()">
            <div class="option-content">
                <strong>Vincular a una solicitud</strong>
                <p>Agendar cita para continuar un proceso ya iniciado.</p>
            </div>
        </label>
        <label class="option-card">
            <input type="radio" name="tipo_tramite" value="motivo" onchange="cambiarTipoTramite()">
            <div class="option-content">
                <strong>Trámite Libre</strong>
                <p>Consultas rápidas o constancias sin solicitud previa.</p>
            </div>
        </label>
    </div>

    <!-- Campos del Paso 1 -->
    <div class="input-group">
        <div id="div_solicitud">
            <label class="input-label">Selecciona la solicitud pendiente:</label>
            <select id="solicitud_select" class="form-control">
                <option value="">-- Elige una opción --</option>
                @foreach($solicitudesPendientes as $solicitud)
                    <option value="{{ $solicitud->sol_id }}">
                        {{ $solicitud->tipoSolicitud->tsi_nombre_tipo }} (Ref: {{$solicitud->sol_id_seguimiento }})
                    </option>
                @endforeach
            </select>
        </div>
        <div id="div_motivo" style="display: none;">
            <label class="input-label">Escribe brevemente el motivo de tu visita:</label>
            <input type="text" id="motivo_input" placeholder="Ej: Retiro de constancia de notas..." class="form-control">
        </div>
        <p id="error-paso1" style="color: #ef4444; margin-top: 10px; display: none; font-size: 14px; font-weight: 500;"></p>
    </div>

    <hr class="divider">

    <!-- PASO 2 -->
    <h2 class="step-title"><span class="step-badge">2</span> Elige la fecha y bloque de atención</h2>
    
    <div class="input-group">
        <div style="margin-bottom: 24px;">
            @php
                $hoy = date('Y-m-d');$maximo = date('Y-m-d', strtotime('+30 days'));
            @endphp
            <label for="fecha" class="input-label">Selecciona el día (Lunes a Viernes)</label>
            <input type="date" id="fecha" class="form-control" style="max-width: 250px;" min="{{ $hoy }}" max="{{ $maximo }}">
            <p style="font-size: 12px; color: #6b7280; margin-top: 4px;">Puedes agendar citas hasta el {{ date('d/m/Y', strtotime('+30 days')) }}</p>
        </div>
        
        <div id="bloques-container" style="display: none;">
            <p style="font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 12px;">Horarios con disponibilidad:</p>
            <div id="bloques-list" class="bloques-grid">
                <!-- Botones inyectados por JS -->
            </div>
        </div>
        <p id="mensaje-error" style="color: #ef4444; margin-top: 10px; display: none; font-size: 14px; font-weight: 500;"></p>
    </div>
</div>

<!-- MODAL DE CONFIRMACIÓN -->
<div id="modal-confirmacion" class="modal-overlay">
    <div class="modal-content">
        <h3 class="modal-title">Confirmar Cita</h3>
        <p id="modal-texto" class="modal-text"></p>
        <div class="modal-actions">
            <button type="button" class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
            <button type="button" class="btn-confirm" onclick="enviarFormulario()">Sí, agendar cita</button>
        </div>
    </div>
</div>

<!-- FORMULARIO OCULTO -->
<form id="form-agendar" action="{{ route('user.citas.store') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="cit_fecha_hora" id="input_fecha_hora">
    <input type="hidden" name="cit_sol_id" id="input_sol_id">
    <input type="hidden" name="cit_motivo" id="input_motivo">
</form>
@endsection

@push('scripts')
<script>
// 1. Funciones de UI
function cambiarTipoTramite() {
    const tipo = document.querySelector('input[name="tipo_tramite"]:checked');
    if(!tipo) return;

    if (tipo.value === 'solicitud') {
        document.getElementById('div_solicitud').style.display = 'block';
        document.getElementById('div_motivo').style.display = 'none';
    } else {
        document.getElementById('div_solicitud').style.display = 'none';
        document.getElementById('div_motivo').style.display = 'block';
    }
    const err = document.getElementById('error-paso1');
    if (err) err.style.display = 'none';
}

function cerrarModal() {
    document.getElementById('modal-confirmacion').classList.remove('active');
}

function enviarFormulario() {
    const btn = document.querySelector('.btn-confirm');
    btn.innerHTML = 'Guardando...';
    btn.disabled = true;
    document.getElementById('form-agendar').submit();
}

// 2. INICIALIZACIÓN Y DELEGACIÓN DE EVENTOS (Indestructible)
document.addEventListener('DOMContentLoaded', function() {
    cambiarTipoTramite();

    // A. DELEGACIÓN DE CLIC: El contenedor atrapa los clics de los botones dinámicos
    document.getElementById('bloques-list').addEventListener('click', function(e) {
        // Buscar si lo que se clickeó fue un botón (o el texto dentro de él)
        const btn = e.target.closest('.btn-bloque');
        if (!btn) return; // Si clickeó fuera de un botón, ignorar
        
        e.preventDefault();

        const inicio = btn.getAttribute('data-inicio');
        const hora = btn.getAttribute('data-hora');
        const errPaso1 = document.getElementById('error-paso1');
        const tipo = document.querySelector('input[name="tipo_tramite"]:checked');

        errPaso1.style.display = 'none';

        // Validaciones estrictas del Paso 1
        if (tipo && tipo.value === 'solicitud') {
            const sel = document.getElementById('solicitud_select');
            if (!sel || sel.value === '') {
                errPaso1.innerHTML = '<strong>¡Atención!</strong> Selecciona una solicitud de la lista antes de elegir la hora.';
                errPaso1.style.display = 'block';
                errPaso1.scrollIntoView({behavior: 'smooth', block: 'center'});
                return;
            }
            document.getElementById('input_sol_id').value = sel.value;
            document.getElementById('input_sol_id').disabled = false;
            document.getElementById('input_motivo').disabled = true;
        } else {
            const mot = document.getElementById('motivo_input');
            if (!mot || mot.value.trim() === '') {
                errPaso1.innerHTML = '<strong>¡Atención!</strong> Describe el motivo de tu visita antes de elegir la hora.';
                errPaso1.style.display = 'block';
                errPaso1.scrollIntoView({behavior: 'smooth', block: 'center'});
                return;
            }
            document.getElementById('input_motivo').value = mot.value.trim();
            document.getElementById('input_motivo').disabled = false;
            document.getElementById('input_sol_id').disabled = true;
        }

        // Si pasó las validaciones, cargar datos y mostrar modal
        document.getElementById('input_fecha_hora').value = inicio;
        
        const dateObj = new Date(inicio.replace(' ', 'T'));
        let fechaAmigable = isNaN(dateObj) ? inicio.split(' ')[0] : dateObj.toLocaleDateString();
        
        document.getElementById('modal-texto').innerHTML = `¿Confirmas tu cita para el <strong>${fechaAmigable}</strong> a las <strong>${hora}</strong>?`;
        document.getElementById('modal-confirmacion').classList.add('active');
    });

    // B. CARGA DINÁMICA DE LOS HORARIOS
    const fechaInput = document.getElementById('fecha');
    if(fechaInput) {
        fechaInput.addEventListener('change', async function(e) {
            let fecha = e.target.value;
            if (!fecha) return;

            // EL PARCHE: Convertir formato DD/MM/YYYY a YYYY-MM-DD para la URL de la API
            if (fecha.includes('/')) {
                const partes = fecha.split('/');
                if (partes.length === 3) {
                    fecha = `${partes[2]}-${partes[1]}-${partes[0]}`;
                }
            }

            const container = document.getElementById('bloques-container');
            const list = document.getElementById('bloques-list');
            const error = document.getElementById('mensaje-error');
            
            try {
                // Cálculo de límites de fecha
                const hoy = new Date('{{ date("Y-m-d") }}T00:00:00');
                const max = new Date('{{ date("Y-m-d", strtotime("+30 days")) }}T00:00:00');
                const fStr = fecha.includes('T') ? fecha : fecha + 'T00:00:00';
                const fObj = new Date(fStr);

                if (fObj < hoy || fObj > max) throw new Error('Selecciona una fecha válida dentro de los próximos 30 días.');
                if (fObj.getDay() === 0 || fObj.getDay() === 6) throw new Error('Las citas solo están disponibles de Lunes a Viernes.');

                list.innerHTML = '<div style="font-size: 14px; color: #6b7280; font-style: italic;">Cargando disponibilidad...</div>';
                container.style.display = 'block';
                error.style.display = 'none';

                const res = await fetch(`/api/citas/disponibles/${fecha}`);
                const data = await res.json();

                if (!res.ok) throw new Error(data.message || 'Error de conexión con el servidor.');
                if (!data.bloques_disponibles || data.bloques_disponibles.length === 0) throw new Error('No hay bloques disponibles para esta fecha.');

                // Renderizado usando atributos "data-" seguros
                let htmlBotones = '';
                data.bloques_disponibles.forEach(b => {
                    const d = new Date(b.inicio.replace(' ', 'T'));
                    let hStr = b.inicio.split(' ')[1];
                    if(!isNaN(d)) {
                        hStr = d.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
                    }

                    htmlBotones += `
                        <button type="button" class="btn-bloque" data-inicio="${b.inicio}" data-hora="${hStr}">
                            <span class="hora" style="pointer-events: none;">${hStr}</span>
                            <span class="cupos" style="pointer-events: none;">${b.cupos_restantes} cupos</span>
                        </button>
                    `;
                });
                list.innerHTML = htmlBotones;

            } catch (err) {
                error.textContent = err.message;
                error.style.display = 'block';
                container.style.display = 'none';
            }
        });
    }
});
</script>
@endpush