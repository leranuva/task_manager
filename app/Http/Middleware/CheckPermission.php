<?php

namespace App\Http\Middleware;

use App\Services\PermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function __construct(
        protected PermissionService $permissionService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission
     * @param  string|null  $resourceType  'team', 'project', 'global'
     * @param  string|null  $resourceId
     */
    public function handle(Request $request, Closure $next, string $permission, ?string $resourceType = null, ?string $resourceId = null): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        // Super Admin tiene acceso a todo
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $hasPermission = false;

        // Verificar permiso según el tipo de recurso
        switch ($resourceType) {
            case 'team':
                $team = $request->route('team') ?? \App\Models\Team::find($resourceId);
                if ($team) {
                    $hasPermission = $this->permissionService->hasTeamPermission($user, $team, $permission);
                }
                break;

            case 'project':
                $project = $request->route('project') ?? \App\Models\Project::find($resourceId);
                if ($project) {
                    $hasPermission = $this->permissionService->hasProjectPermission($user, $project, $permission);
                }
                break;

            default:
                // Para permisos "globales", solo Super Admin tiene acceso
                // O puedes definir lógica específica aquí
                $hasPermission = $user->isSuperAdmin();
                break;
        }

        if (!$hasPermission) {
            abort(403, 'You do not have permission to perform this action.');
        }

        return $next($request);
    }
}
