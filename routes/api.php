<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\PublicoController;

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('login', [\App\Http\Controllers\Api\V1\AuthController::class, 'login'])->name('login');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [\App\Http\Controllers\Api\V1\AuthController::class, 'logout'])->name('logout');
            Route::get('me', [\App\Http\Controllers\Api\V1\AuthController::class, 'me'])->name('me');
            Route::post('refresh', [\App\Http\Controllers\Api\V1\AuthController::class, 'refreshToken'])->name('refresh');
        });
    });

    // Portal público
    Route::prefix('publico')->name('publico.')->group(function () {
        Route::get('mapa/catalogos', [PublicoController::class, 'mapaCatalogos'])->name('mapa.catalogos');
        Route::get('mapa/filtrar', [PublicoController::class, 'mapaFiltrar'])->name('mapa.filtrar');
        Route::get('conteos', [PublicoController::class, 'conteos'])->name('conteos');
        Route::get('estadisticas', [PublicoController::class, 'estadisticas'])->name('estadisticas');
        Route::get('proyectos/{id}', [PublicoController::class, 'showProyecto'])->name('proyectos.show');
        Route::get('emblematicos', [PublicoController::class, 'emblematicos'])->name('emblematicos');
        Route::get('buenas-practicas', [PublicoController::class, 'buenasPracticas'])->name('buenas-practicas');

        // Catalogo Ubicaciones
        Route::get('provincias', [\App\Http\Controllers\Api\V1\ProvinciaController::class, 'index'])->name('provincias.index');
        Route::get('cantones', [\App\Http\Controllers\Api\V1\CantonController::class, 'index'])->name('cantones.index');
        Route::get('parroquias', [\App\Http\Controllers\Api\V1\ParroquiaController::class, 'index'])->name('parroquias.index');
        // Catálogo beneficiarios (público, solo lectura agrupada para el formulario)
        Route::get('categorias-beneficiario', [\App\Http\Controllers\Api\V1\CategoriaBeneficiarioController::class, 'agrupadas'])->name('publico.categorias-beneficiario');
    });

    // Rutas protegidas genéricas
    Route::middleware('auth:sanctum')->group(function () {
        // Usuarios
        Route::apiResource('usuarios', \App\Http\Controllers\Api\V1\UsuarioController::class);
        Route::patch('usuarios/{usuario}/estado', [\App\Http\Controllers\Api\V1\UsuarioController::class, 'cambiarEstado'])->name('usuarios.estado');
        Route::post('usuarios/{usuario}/reset-password', [\App\Http\Controllers\Api\V1\UsuarioController::class, 'resetPassword'])->name('usuarios.reset_password');
        Route::patch('usuarios/{usuario}/rol', [\App\Http\Controllers\Api\V1\UsuarioController::class, 'asignarRol'])->name('usuarios.rol');
        Route::patch('usuarios/{usuario}/provincias', [\App\Http\Controllers\Api\V1\UsuarioController::class, 'asignarProvincias'])->name('usuarios.provincias');
        Route::post('usuarios/me/password', [\App\Http\Controllers\Api\V1\UsuarioController::class, 'updatePassword'])->name('usuarios.update_password');
        Route::get('auditoria', [\App\Http\Controllers\Api\V1\UsuarioController::class, 'auditoria'])->name('auditoria.index');

        // Provincias (catálogo, solo lectura + consultas)
        Route::get('provincias/{provincia}', [\App\Http\Controllers\Api\V1\ProvinciaController::class, 'show'])->name('provincias.show');
        Route::get('provincias/{provincia}/usuarios', [\App\Http\Controllers\Api\V1\ProvinciaController::class, 'usuariosAsignados'])->name('provincias.usuarios');

        // Cantones y Parroquias
        Route::apiResource('cantones', \App\Http\Controllers\Api\V1\CantonController::class)->parameters(['cantones' => 'cantone']);
        Route::apiResource('parroquias', \App\Http\Controllers\Api\V1\ParroquiaController::class);

        // ODS (catálogo, solo lectura)
        Route::get('ods', [\App\Http\Controllers\Api\V1\OdsController::class, 'index'])->name('ods.index');
        Route::get('ods/{od}', [\App\Http\Controllers\Api\V1\OdsController::class, 'show'])->name('ods.show');
        Route::get('ods/{od}/proyectos', [\App\Http\Controllers\Api\V1\OdsController::class, 'proyectosPorOds'])->name('ods.proyectos');

        // Actores de Cooperación
        Route::get('actores/exportar', [\App\Http\Controllers\Api\V1\ActorCooperacionController::class, 'exportar'])->name('actores.exportar');
        Route::apiResource('actores', \App\Http\Controllers\Api\V1\ActorCooperacionController::class);

        // Proyectos
        Route::get('proyectos/exportar', [\App\Http\Controllers\Api\V1\ProyectoController::class, 'exportar'])->name('proyectos.exportar');
        Route::get('proyectos/{id}/historial', [\App\Http\Controllers\Api\V1\ProyectoController::class, 'historial'])->name('proyectos.historial');
        Route::patch('proyectos/{proyecto}/estado', [\App\Http\Controllers\Api\V1\ProyectoController::class, 'cambiarEstado'])->name('proyectos.cambiar_estado');
        Route::apiResource('proyectos', \App\Http\Controllers\Api\V1\ProyectoController::class);

        // Categorías de Beneficiarios
        Route::get('categorias-beneficiario/agrupadas', [\App\Http\Controllers\Api\V1\CategoriaBeneficiarioController::class, 'agrupadas'])->name('categorias-beneficiario.agrupadas');
        Route::apiResource('categorias-beneficiario', \App\Http\Controllers\Api\V1\CategoriaBeneficiarioController::class);

        // Hitos de Proyecto
        Route::prefix('proyectos/{proyecto}')->group(function () {
            Route::apiResource('hitos', \App\Http\Controllers\Api\V1\HitoProyectoController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::patch('hitos/{hito}/completar', [\App\Http\Controllers\Api\V1\HitoProyectoController::class, 'completar'])->name('hitos.completar');
        });

        // Buenas Prácticas
        Route::get('buenas-practicas/exportar', [\App\Http\Controllers\Api\V1\BuenaPracticaController::class, 'exportar'])->name('buenas-practicas.exportar');
        Route::apiResource('buenas-practicas', \App\Http\Controllers\Api\V1\BuenaPracticaController::class)->parameters(['buenas-practicas' => 'buena_practica']);
        Route::patch('buenas-practicas/{buena_practica}/destacar', [\App\Http\Controllers\Api\V1\BuenaPracticaController::class, 'destacar'])->name('buenas-practicas.destacar');

        // Valoraciones (sub-recurso)
        Route::prefix('buenas-practicas/{buena_practica}')->name('buenas-practicas.')->group(function () {
            Route::post('valoraciones', [\App\Http\Controllers\Api\V1\ValoracionPracticaController::class, 'store'])->name('valoraciones.store');
            Route::delete('valoraciones', [\App\Http\Controllers\Api\V1\ValoracionPracticaController::class, 'destroy'])->name('valoraciones.destroy');
        });
        // Redes
        Route::apiResource('redes', \App\Http\Controllers\Api\V1\RedController::class)->parameters(['redes' => 'red']);
        Route::post('redes/{red}/miembros', [\App\Http\Controllers\Api\V1\RedController::class, 'gestionarMiembros'])->name('redes.miembros');

        // Proyectos Emblemáticos
        Route::apiResource('emblematicos', \App\Http\Controllers\Api\V1\ProyectoEmblematicoController::class);
        Route::patch('emblematicos/{emblematico}/publicar', [\App\Http\Controllers\Api\V1\ProyectoEmblematicoController::class, 'publicar'])
            ->name('emblematicos.publicar');

        Route::prefix('emblematicos/{emblematico}')->name('emblematicos.')->group(function () {
            Route::apiResource('reconocimientos', \App\Http\Controllers\Api\V1\ReconocimientoController::class)
                ->only(['index', 'store', 'update', 'destroy']);
        });

        // Documentos
        Route::post('documentos/buscar', [\App\Http\Controllers\Api\V1\DocumentoController::class, 'index'])->name('documentos.index');
        Route::post('documentos', [\App\Http\Controllers\Api\V1\DocumentoController::class, 'store'])->name('documentos.store');
        Route::get('documentos/{documento}', [\App\Http\Controllers\Api\V1\DocumentoController::class, 'show'])->name('documentos.show');
        Route::put('documentos/{documento}', [\App\Http\Controllers\Api\V1\DocumentoController::class, 'update'])->name('documentos.update');
        Route::delete('documentos/{documento}', [\App\Http\Controllers\Api\V1\DocumentoController::class, 'destroy'])->name('documentos.destroy');
        Route::get('documentos/{documento}/descargar', [\App\Http\Controllers\Api\V1\DocumentoController::class, 'descargar'])->name('documentos.descargar');
        Route::patch('documentos/{documento}/publicar', [\App\Http\Controllers\Api\V1\DocumentoController::class, 'publicar'])->name('documentos.publicar');

        // Eventos
        Route::apiResource('eventos', \App\Http\Controllers\Api\V1\EventoController::class);

        Route::post('eventos/{evento}/participantes', [\App\Http\Controllers\Api\V1\EventoController::class, 'gestionarParticipantes'])
            ->name('eventos.participantes');

        // Compromisos como sub-recurso de eventos
        Route::prefix('eventos/{evento}')->name('eventos.')->group(function () {
            Route::get('compromisos', [\App\Http\Controllers\Api\V1\CompromisoEventoController::class, 'index'])
                ->name('compromisos.index');
            Route::post('compromisos', [\App\Http\Controllers\Api\V1\CompromisoEventoController::class, 'store'])
                ->name('compromisos.store');
            Route::patch('compromisos/{compromiso}/resolver', [\App\Http\Controllers\Api\V1\CompromisoEventoController::class, 'resolver'])
                ->name('compromisos.resolver');
        });

        // Mis compromisos pendientes (para el dashboard)
        Route::get('mis-compromisos-pendientes', [\App\Http\Controllers\Api\V1\CompromisoEventoController::class, 'misPendientes'])
            ->name('compromisos.mis-pendientes');

        // Dashboard
        Route::prefix('dashboard')->name('dashboard.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\V1\DashboardController::class, 'index'])->name('index');
            Route::get('grafica-anual', [\App\Http\Controllers\Api\V1\DashboardController::class, 'graficaAnual'])->name('grafica-anual');
            Route::get('grafica-ods', [\App\Http\Controllers\Api\V1\DashboardController::class, 'graficaOds'])->name('grafica-ods');
        });

        // Reportes
        Route::prefix('reportes')->name('reportes.')->group(function () {
            Route::post('provincia', [\App\Http\Controllers\Api\V1\ReporteController::class, 'provincia'])->name('provincia');
            Route::post('ods', [\App\Http\Controllers\Api\V1\ReporteController::class, 'ods'])->name('ods');
            Route::post('cooperante', [\App\Http\Controllers\Api\V1\ReporteController::class, 'cooperante'])->name('cooperante');
            Route::post('anual', [\App\Http\Controllers\Api\V1\ReporteController::class, 'anual'])->name('anual');
            Route::post('global', [\App\Http\Controllers\Api\V1\ReporteController::class, 'global'])->name('global');
        });

    });

});

