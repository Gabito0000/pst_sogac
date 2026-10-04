<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminRequisitoController;
use App\Http\Controllers\AdminTipoSolicitudController;
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

Route::prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/dashboard/estado/{id}/{accion}', [AdminDashboardController::class, 'cambiarEstado'])->name('admin.dashboard.estado');

    Route::resource('requisitos', AdminRequisitoController::class)->names('admin.requisitos');

    Route::resource('tipos-solicitud', AdminTipoSolicitudController::class)
        ->except(['show'])
        ->names('admin.tipos-solicitud');

    Route::patch('/tipos-solicitud/{id}/alternar-estado', [AdminTipoSolicitudController::class, 'alternarEstado'])
        ->name('admin.tipos-solicitud.alternar-estado');

    Route::resource('soporte', PreguntasFrecuentesController::class)
        ->except(['show'])
        ->names('admin.soporte');

    Route::middleware('auth')->prefix('soporte/chat')->name('admin.chat.')->group(function () {
        Route::get('/', [SoporteController::class, 'verHistorial'])->name('index');
        Route::get('/{id}', [SoporteController::class, 'mostrarChat'])->name('mostrar');
        Route::post('/{id}/mensaje', [SoporteController::class, 'enviarMensaje'])->name('enviar');
        Route::post('/{id}/reclamar', [SoporteController::class, 'reclamarChat'])->name('reclamar');
        Route::post('/{id}/proponer-cierre', [SoporteController::class, 'proponerCierre'])->name('proponer_cierre');
    });
});
