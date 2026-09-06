@extends('layouts.plantilla_user')

@section('content')
<div class="container mt-5">
    <h2 class="mb-4 text-primary">Mi Panel Estudiantil</h2>

    <!-- HEADER / NAVEGACIÓN (Pestañas de Bootstrap) -->
    <ul class="nav nav-tabs" id="panelEstudiantilTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="solicitudes-tab" data-bs-toggle="tab" data-bs-target="#solicitudes" type="button" role="tab" aria-controls="solicitudes" aria-selected="true">Mis Solicitudes</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="documentos-tab" data-bs-toggle="tab" data-bs-target="#documentos" type="button" role="tab" aria-controls="documentos" aria-selected="false">Documentos Predeterminados</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="citas-tab" data-bs-toggle="tab" data-bs-target="#citas" type="button" role="tab" aria-controls="citas" aria-selected="false">Asignar Cita</button>
        </li>
    </ul>

    <!-- CONTENIDO DE LAS PESTAÑAS -->
    <div class="tab-content border border-top-0 bg-white p-4" id="panelEstudiantilTabsContent">
        
        <!-- Pestaña 1: Solicitudes Pendientes -->
        <div class="tab-pane fade show active" id="solicitudes" role="tabpanel" aria-labelledby="solicitudes-tab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="text-secondary">Estado de mis trámites</h4>
                <!-- El enlace que llevará a tu método create() del controlador -->
                <a href="{{ url('/user/solicitudes/crear') }}" class="btn btn-success">+ Nueva Solicitud</a>
            </div>

            <table class="table table-hover table-bordered mt-3">
                <thead class="table-dark">
                    <tr>
                        <th>Código de Seguimiento</th>
                        <th>Motivo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Ciclo de Blade para imprimir las solicitudes del controlador -->
                    @forelse($misSolicitudes as $solicitud)
                        <tr>
                            <td class="font-monospace fw-bold">{{ $solicitud->sol_id_seguimiento }}</td>
                            <td>{{ $solicitud->sol_motivo_detallado }}</td>
                            <td><span class="badge bg-warning text-dark">Pendiente</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">Aún no tienes solicitudes en curso.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pestaña 2: Documentos Predeterminados -->
        <div class="tab-pane fade" id="documentos" role="tabpanel" aria-labelledby="documentos-tab">
            <h4 class="text-secondary">Mi Bóveda de Documentos</h4>
            <p class="text-muted">Carga aquí tus documentos personales (Cédula, Carnet) una sola vez. El sistema los tomará automáticamente al hacer una nueva solicitud.</p>
            
            <!-- Aquí agregaremos el formulario de subida múltiple más adelante -->
            <div class="alert alert-info">Formulario en construcción...</div>
        </div>

        <!-- Pestaña 3: Asignar Cita -->
        <div class="tab-pane fade" id="citas" role="tabpanel" aria-labelledby="citas-tab">
            <h4 class="text-secondary">Agendar Cita Presencial</h4>
            <p class="text-muted">Selecciona el departamento y la fecha en la que deseas asistir a la universidad.</p>
            
            <!-- Aquí agregaremos el calendario y el formulario de fecha -->
            <div class="alert alert-info">Calendario en construcción...</div>
        </div>

    </div>
</div>

<!-- Script necesario para que las pestañas de Bootstrap funcionen (si no lo tienes en tu layout) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection