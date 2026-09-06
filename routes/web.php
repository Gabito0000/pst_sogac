<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminRequisitoController;
use App\Http\Controllers\AdminTipoSolicitudController;
use App\Http\Controllers\PreguntasFrecuentesController;

// ============================================================
// GRUPO ADMIN
// Nota: El panel queda SIN autenticación local porque el acceso
// se controlará con el doble inicio de sesión de la universidad
// (SSO) una vez el sistema esté en producción. Si se necesita
// proteger, basta con añadir ->middleware(['auth', 'admin'])
// al grupo de abajo y crear las rutas de login/logout.
// ============================================================
Route::prefix('admin')->group(function () {

    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/dashboard/estado/{id}/{accion}', [AdminDashboardController::class, 'cambiarEstado'])->name('admin.dashboard.estado');

    // CRUD completo de requisitos (Crear, Leer, Actualizar, Eliminar)
    Route::resource('requisitos', AdminRequisitoController::class)->names('admin.requisitos');

    // CRUD de los TRÁMITES en sí (tipo_solicitudes): aquí es donde el admin
    // crea el trámite, define su ventana de fechas y le asigna requisitos.
    Route::resource('tipos-solicitud', AdminTipoSolicitudController::class)->names('admin.tipos-solicitud');
    Route::patch('/tipos-solicitud/{id}/alternar-estado', [AdminTipoSolicitudController::class, 'alternarEstado'])
        ->name('admin.tipos-solicitud.alternar-estado');
});

// Soporte / Preguntas frecuentes
Route::resource('soporte', PreguntasFrecuentesController::class);

// Rutas de Registro
Route::get('/register', [RegisterController::class, 'mostrarFormulario'])->name('register');
Route::post('/register', [RegisterController::class, 'registrar'])->name('register.post');
// LOGIN

// TEMPORAL VVV
Route::get('/prueba-chat-usuario', function () {
    $hilo = (object) [
        'hch_id' => 1,
        'hch_estado' => 'pendiente_cierre',
        'hch_etiqueta_tema' => null
    ];
    return view('soporte\chat_usuario', compact('hilo'));
});
//LOGOUT RAPIDO DE DEPURACION


Route::get('/prueba-chat-admin', function () {
    $hilo = (object) [
        'hch_id' => 1,
        'hch_estado' => 'activo',
    ];
    return view('soporte\chat_admin', compact('hilo'));
});
// TEMPORAL ^^^

