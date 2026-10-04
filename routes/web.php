<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminEstadisticasController;
use App\Http\Controllers\AdminHistorialCambioController;
use App\Http\Controllers\AdminPreguntaFrecuenteController;
use App\Http\Controllers\AdminRequisitoController;
use App\Http\Controllers\AdminTipoSolicitudController;
use App\Http\Controllers\AyudaController;
use App\Http\Controllers\HistorialSolicitudController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SoporteController;
use App\Http\Controllers\UserSolicitudController;
use Illuminate\Support\Facades\Route;

// El homepage institucional es la pagina principal y se muestra siempre,
// haya sesion o no: quien ya esta dentro del sistema tiene su acceso directo
// en el boton "Mi panel" de la barra del homepage. El sistema en si vive en
// /login, /user y /admin.
Route::view('/', 'portada')->name('portada');

Route::get('/login', [LoginController::class, 'mostrarFormulario'])->name('login');
Route::post('/login', [LoginController::class, 'procesarLogin'])->name('login.post');

Route::get('/register', [RegisterController::class, 'mostrarFormulario'])->name('register');
Route::post('/register', [RegisterController::class, 'registrar'])->name('register.post');

Route::post('/logout', [LoginController::class, 'cerrarSesion'])->name('logout')->middleware('auth');

Route::middleware('auth')->prefix('user')->group(function () {
    Route::get('/dashboard', [UserSolicitudController::class, 'index'])->name('dashboard');
    Route::get('/citas', [UserSolicitudController::class, 'misCitas'])->name('user.citas');

    Route::get('/tramites', [UserSolicitudController::class, 'listarTramites'])->name('user.tramites.index');

    // Historial de solicitudes del estudiante. Antes era una sola pagina plana
    // (user.solicitudes.historial, en /user/historial) con cuatro columnas; ahora
    // es un sector con listado filtrable y ficha de detalle.
    Route::prefix('historial')->name('user.historial.')->group(function () {
        Route::get('/', [HistorialSolicitudController::class, 'index'])->name('index');
        Route::get('/{solicitud}', [HistorialSolicitudController::class, 'show'])->name('show');
    });

    Route::get('/tramites/{id}/solicitar', [UserSolicitudController::class, 'create'])->name('user.tramites.solicitar');
    Route::post('/tramites/{id}/solicitar', [UserSolicitudController::class, 'store'])->name('user.tramites.store');

    // Ayuda: las preguntas frecuentes y el chat son dos sectores distintos.
    // Antes ambos vivian bajo "soporte", lo que hacia que /user/soporte
    // mostrara las FAQ y el chat quedara escondido debajo del mismo nombre.
    Route::prefix('ayuda')->name('user.ayuda.')->group(function () {
        Route::get('/preguntas', [AyudaController::class, 'preguntas'])->name('preguntas');

        Route::prefix('chat')->name('chat.')->group(function () {
            Route::get('/', [SoporteController::class, 'verHistorial'])->name('index');
            Route::post('/iniciar', [SoporteController::class, 'iniciarChat'])->name('iniciar');
            Route::get('/{id}', [SoporteController::class, 'mostrarChat'])->name('mostrar');
            Route::post('/{id}/mensaje', [SoporteController::class, 'enviarMensaje'])->name('enviar');
            Route::post('/{id}/confirmar', [SoporteController::class, 'confirmarCierre'])->name('confirmar');
            Route::post('/{id}/rechazar', [SoporteController::class, 'desconfirmarCierre'])->name('rechazar');
        });
    });
});

// El prefijo admin exige rol 'admin' + usuario autenticado (middleware EsAdmin).
Route::middleware('admin')->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/dashboard/estado/{id}/{accion}', [AdminDashboardController::class, 'cambiarEstado'])->name('admin.dashboard.estado');

    // Panel estadístico: métricas precisas de todo el proceso de las solicitudes.
    Route::get('/estadisticas', [AdminEstadisticasController::class, 'index'])->name('admin.estadisticas');
    Route::get('/estadisticas/exportar', [AdminEstadisticasController::class, 'exportar'])->name('admin.estadisticas.exportar');

    // Bitácora de cambios: qué se tocó en el sistema, quién y con qué valores.
    // Es un sector aparte y no una pestaña del panel estadístico porque mide
    // cosas distintas: el panel resume los números del proceso de solicitudes y
    // la bitácora deja constancia de cada modificación, con su autor.
    Route::prefix('cambios')->name('admin.cambios.')->group(function () {
        Route::get('/', [AdminHistorialCambioController::class, 'index'])->name('index');
        Route::get('/exportar', [AdminHistorialCambioController::class, 'exportar'])->name('exportar');
    });

    Route::resource('requisitos', AdminRequisitoController::class)->names('admin.requisitos');

    Route::resource('tipos-solicitud', AdminTipoSolicitudController::class)
        ->except(['show'])
        ->names('admin.tipos-solicitud');

    Route::patch('/tipos-solicitud/{id}/alternar-estado', [AdminTipoSolicitudController::class, 'alternarEstado'])
        ->name('admin.tipos-solicitud.alternar-estado');

    // Preguntas frecuentes: CRUD propio del administrador. Antes compartia una
    // vista con el estudiante que se bifurcaba por rol, y create()/edit()
    // apuntaban a vistas que no existen.
    // Sin 'show': la pregunta se lee dentro del listado o del portal del
    // estudiante, no en una pagina propia.
    Route::resource('preguntas', AdminPreguntaFrecuenteController::class)
        ->except(['show'])
        ->names('admin.preguntas');

    // Chats de soporte. El middleware 'admin' ya exige sesion, asi que no hace
    // falta volver a anadir 'auth' aqui.
    Route::prefix('chat')->name('admin.chat.')->group(function () {
        Route::get('/', [SoporteController::class, 'verHistorial'])->name('index');
        Route::get('/{id}', [SoporteController::class, 'mostrarChat'])->name('mostrar');
        Route::post('/{id}/mensaje', [SoporteController::class, 'enviarMensaje'])->name('enviar');
        Route::post('/{id}/reclamar', [SoporteController::class, 'reclamarChat'])->name('reclamar');
        // Con guion, igual que admin.tipos-solicitud.alternar-estado: era el
        // único nombre de ruta con guion bajo y se colaba con la referencia.
        Route::post('/{id}/proponer-cierre', [SoporteController::class, 'proponerCierre'])->name('proponer-cierre');
    });
});
