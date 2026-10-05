<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminRequisitoController;
use App\Http\Controllers\AdminSolicitudController;
use App\Http\Controllers\AdminTipoSolicitudController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PreguntasFrecuentesController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SoporteController;
use App\Http\Controllers\UserSolicitudController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ============================================================
// RAÍZ
// Lleva a cada rol a su página: el taquillero a la cola de
// solicitudes, el personal administrativo al panel estadístico y
// el estudiante a su panel. Sin sesión, al login.
// ============================================================
Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    $usuario = Auth::user();

    if ($usuario->esTaquillero()) {
        return redirect()->route('admin.solicitudes.index');
    }

    if ($usuario->esAdministrativo()) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('dashboard');
});

// ============================================================
// LOGIN / LOGOUT / REGISTRO
// ============================================================
Route::get('/login', [LoginController::class, 'mostrarFormulario'])->name('login');
Route::post('/login', [LoginController::class, 'procesarLogin'])->name('login.post');
Route::post('/logout', [LoginController::class, 'cerrarSesion'])->name('logout')->middleware('auth');

Route::get('/register', [RegisterController::class, 'mostrarFormulario'])->name('register');
Route::post('/register', [RegisterController::class, 'registrar'])->name('register.post');

// ============================================================
// RECUPERACIÓN DE CONTRASEÑA
// ============================================================
Route::get('/olvide-mi-contrasena', [PasswordResetController::class, 'requestForm'])->name('password.request');
Route::post('/olvide-mi-contrasena', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
Route::get('/restablecer-contrasena/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
Route::post('/restablecer-contrasena', [PasswordResetController::class, 'updatePassword'])->name('password.update');

// ============================================================
// GRUPO ESTUDIANTE
// ============================================================
Route::middleware('auth')->prefix('user')->group(function () {
    Route::get('/dashboard', [UserSolicitudController::class, 'index'])->name('dashboard');
    Route::get('/citas', [UserSolicitudController::class, 'misCitas'])->name('user.citas');

    Route::get('/tramites', [UserSolicitudController::class, 'listarTramites'])->name('user.tramites.index');
    Route::get('/historial', [UserSolicitudController::class, 'historial'])->name('user.solicitudes.historial');

    Route::get('/tramites/{id}/solicitar', [UserSolicitudController::class, 'create'])->name('user.tramites.solicitar');
    Route::post('/tramites/{id}/solicitar', [UserSolicitudController::class, 'store'])->name('user.tramites.store');

    // Módulo de Soporte (Solo Lectura para usuarios)
    Route::get('/soporte', [PreguntasFrecuentesController::class, 'index'])->name('soporte.index');

    // Módulo Chat Estudiante
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
// GRUPO ADMIN
// El panel exige sesión y cada módulo exige un rol concreto de la
// jerarquía (administrador > analista > taquillero).
// Los roles y sus permisos están centralizados en App\Models\Rol.
// ============================================================
Route::middleware('auth')->prefix('admin')->group(function () {

    // Panel estadístico e historial: administrador y analista
    // (el taquillero procesa solicitudes, pero no ve estadísticas)
    Route::middleware('rol:administrador,analista')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    });

    // Aprobar / rechazar solicitudes: los tres roles administrativos.
    // La cola de solicitudes es la página de inicio del taquillero.
    Route::middleware('rol:administrador,analista,taquillero')->group(function () {
        Route::get('/solicitudes', [AdminSolicitudController::class, 'index'])->name('admin.solicitudes.index');
        Route::get('/dashboard/estado/{id}/{accion}', [AdminDashboardController::class, 'cambiarEstado'])->name('admin.dashboard.estado');
    });

    // Catálogos (requisitos, trámites y preguntas frecuentes):
    // administrador y analista
    Route::middleware('rol:administrador,analista')->group(function () {
        // CRUD Requisitos
        Route::resource('requisitos', AdminRequisitoController::class)->names('admin.requisitos');

        // CRUD Trámites
        Route::resource('tipos-solicitud', AdminTipoSolicitudController::class)
            ->except(['show'])
            ->names('admin.tipos-solicitud');

        Route::patch('/tipos-solicitud/{id}/alternar-estado', [AdminTipoSolicitudController::class, 'alternarEstado'])
            ->name('admin.tipos-solicitud.alternar-estado');

        // Módulo Soporte (Preguntas Frecuentes)
        Route::resource('soporte', PreguntasFrecuentesController::class)
            ->except(['show'])
            ->names('admin.soporte');
    });

    // Módulo Chat Admin: los tres roles administrativos
    Route::middleware('rol:administrador,analista,taquillero')->prefix('soporte/chat')->name('admin.chat.')->group(function () {
        Route::get('/', [SoporteController::class, 'verHistorial'])->name('index');
        Route::get('/{id}', [SoporteController::class, 'mostrarChat'])->name('mostrar');
        Route::post('/{id}/mensaje', [SoporteController::class, 'enviarMensaje'])->name('enviar');
        Route::post('/{id}/reclamar', [SoporteController::class, 'reclamarChat'])->name('reclamar');
        Route::post('/{id}/proponer-cierre', [SoporteController::class, 'proponerCierre'])->name('proponer_cierre');
    });

    // Gestión de Usuarios: solo administrador
    Route::middleware('rol:administrador')->prefix('usuarios')->name('admin.usuarios.')->group(function () {
        Route::get('/', [AdminUserController::class, 'index'])->name('index');
        Route::get('/buscar', [AdminUserController::class, 'search'])->name('search'); // AJAX
        // Agrega a una persona al personal administrativo buscándola por correo
        Route::post('/agregar', [AdminUserController::class, 'agregarPorCorreo'])->name('agregar');
        Route::put('/{usuario}', [AdminUserController::class, 'update'])->name('update');
        Route::post('/{usuario}/desbloquear', [AdminUserController::class, 'unlock'])->name('unlock');
        Route::delete('/{usuario}', [AdminUserController::class, 'destroy'])->name('destroy');
    });
});
