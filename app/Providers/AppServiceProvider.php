<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Gate::policy(\App\Models\ActorCooperacion::class, \App\Policies\ActorCooperacionPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Proyecto::class, \App\Policies\ProyectoPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\ProyectoEmblematico::class, \App\Policies\ProyectoEmblematicoPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Reconocimiento::class, \App\Policies\ReconocimientoPolicy::class);

        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });

        $modelos = [
            \App\Models\Proyecto::class,
            \App\Models\ActorCooperacion::class,
            \App\Models\BuenaPractica::class,
            \App\Models\Red::class,
            \App\Models\Evento::class,
            \App\Models\ProyectoEmblematico::class,
            \App\Models\Documento::class,
        ];

        foreach ($modelos as $modelo) {
            if (class_exists($modelo)) {
                $modelo::observe(\App\Observers\AuditoriaObserver::class);
            }
        }
    }
}
