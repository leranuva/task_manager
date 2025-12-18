<?php

namespace App\Providers;

use App\Services\PermissionService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PermissionService::class, function ($app) {
            return new PermissionService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar Gates para permisos
        $this->registerGates();
    }

    /**
     * Registrar Gates para permisos
     */
    protected function registerGates(): void
    {
        Gate::define('manage-teams', function ($user) {
            // Super Admin o cualquier usuario autenticado puede crear equipos
            return $user->isSuperAdmin() || true; // Ajusta según tus reglas de negocio
        });

        Gate::define('manage-projects', function ($user, $project = null) {
            if ($project) {
                return app(PermissionService::class)->hasProjectPermission($user, $project, 'projects.update');
            }
            // Super Admin o cualquier usuario autenticado puede crear proyectos
            return $user->isSuperAdmin() || true; // Ajusta según tus reglas de negocio
        });

        Gate::define('manage-tasks', function ($user, $project) {
            return app(PermissionService::class)->hasProjectPermission($user, $project, 'tasks.create');
        });

        Gate::define('move-task', function ($user, $task, $newStatusId) {
            return app(PermissionService::class)->canMoveTask($user, $task, $newStatusId);
        });
    }
}
