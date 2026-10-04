<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminRequisitoController;
use App\Http\Controllers\AdminSolicitudController;
use App\Http\Controllers\AdminTipoSolicitudController;
use App\Http\Controllers\AdminUsuarioController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PreguntasFrecuentesController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SoporteController;
use App\Http\Controllers\UserSolicitudController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

Route::get('/login', [LoginController::class, 'mostrarFormulario'])->name('login');
Route::post('/login', [LoginController::class, 'procesarLogin'])->name('login.post');

Route::get('/register', [RegisterController::class, 'mostrarFormulario'])->name('register');
Route::post('/register', [RegisterController::class, 'registrar'])->name('register.post');

Route::post('/logout', [LoginController::class, 'cerrarSesion'])->name('logout')->middleware('auth');

Route::middleware('auth')->prefix('user')->group(function () {
    Route::get('/dashboard', [UserSolicitudController::class, 'index'])->name('dashboard');
    Route::get('/citas', [UserSolicitudController::class, 'misCitas'])->name('user.citas');

    Route::get('/tramites', [UserSolicitudController::class, 'listarTramites'])->name('user.tramites.index');
    Route::get('/historial', [UserSolicitudController::class, 'historial'])->name('user.solicitudes.historial');

    Route::get('/tramites/{id}/solicitar', [UserSolicitudController::class, 'create'])->name('user.tramites.solicitar');
    Route::post('/tramites/{id}/solicitar', [UserSolicitudController::class, 'store'])->name('user.tramites.store');

    Route::get('/soporte', [PreguntasFrecuentesController::class, 'index'])->name('soporte.index');

    Route::prefix('soporte/chat')->name('user.chat.')->group(function () {
        Route::get('/', [SoporteController::class, 'verHistorial'])->name('index');
        Route::get('/{id}', [SoporteController::class, 'mostrarChat'])->name('mostrar');
        Route::post('/{id}/mensaje', [SoporteController::class, 'enviarMensaje'])->name('enviar');
        Route::post('/iniciar', [SoporteController::class, 'iniciarChat'])->name('iniciar');
        Route::post('/{id}/confirmar', [SoporteController::class, 'confirmarCierre'])->name('confirmar');
        Route::post('/{id}/rechazar', [SoporteController::class, 'desconfirmarCierre'])->name('rechazar');
    });
});

// ============================================================
// MÓDULO ADMIN
// El panel exige sesión y cada módulo exige un rol concreto de la
// jerarquía (administrador > analista > taquillero).
// Los roles y sus permisos están centralizados en App\Models\Rol.
// ============================================================
Route::middleware('auth')->prefix('admin')->group(function () {

    // Gestión de usuarios y asignación de roles: solo administrador
    Route::middleware('rol:administrador')->group(function () {
        Route::get('/usuarios', [AdminUsuarioController::class, 'index'])->name('admin.usuarios.index');
        Route::post('/usuarios/agregar', [AdminUsuarioController::class, 'agregarUsuario'])->name('admin.usuarios.agregar');
        Route::patch('/usuarios/{id}/rol', [AdminUsuarioController::class, 'actualizarRol'])->name('admin.usuarios.rol');
    });

    // Panel estadístico e historial: administrador y analista
    // (el taquillero procesa solicitudes, pero no ve estadísticas)
    Route::middleware('rol:administrador,analista')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    });

    // Aprobar / rechazar solicitudes: los tres roles administrativos
    Route::middleware('rol:administrador,analista,taquillero')->group(function () {
        // Cola de solicitudes (página de inicio del taquillero: sin estadísticas)
        Route::get('/solicitudes', [AdminSolicitudController::class, 'index'])->name('admin.solicitudes.index');
        Route::get('/dashboard/estado/{id}/{accion}', [AdminDashboardController::class, 'cambiarEstado'])->name('admin.dashboard.estado');
    });

    // Catálogos (requisitos, trámites y preguntas frecuentes):
    // administrador y analista
    Route::middleware('rol:administrador,analista')->group(function () {
        Route::resource('requisitos', AdminRequisitoController::class)->names('admin.requisitos');
        Route::resource('tipos-solicitud', AdminTipoSolicitudController::class)
            ->except(['show'])
            ->names('admin.tipos-solicitud');
        Route::patch('/tipos-solicitud/{id}/alternar-estado', [AdminTipoSolicitudController::class, 'alternarEstado'])
            ->name('admin.tipos-solicitud.alternar-estado');
        Route::resource('soporte', PreguntasFrecuentesController::class)
            ->except(['show'])
            ->names('admin.soporte');
    });

    // Chat de soporte: los tres roles administrativos
    Route::middleware('rol:administrador,analista,taquillero')->prefix('soporte/chat')->name('admin.chat.')->group(function () {
        Route::get('/', [SoporteController::class, 'verHistorial'])->name('index');
        Route::get('/{id}', [SoporteController::class, 'mostrarChat'])->name('mostrar');
        Route::post('/{id}/mensaje', [SoporteController::class, 'enviarMensaje'])->name('enviar');
        Route::post('/{id}/reclamar', [SoporteController::class, 'reclamarChat'])->name('reclamar');
        Route::post('/{id}/proponer-cierre', [SoporteController::class, 'proponerCierre'])->name('proponer_cierre');
    });
});
