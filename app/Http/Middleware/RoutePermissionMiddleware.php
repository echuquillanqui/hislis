<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class RoutePermissionMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        if (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
            return $next($request);
        }

        $routeName = (string) optional($request->route())->getName();

        if ($routeName === '') {
            return $next($request);
        }

        $permissionNames = $this->buildCandidates($routeName);

        $existingPermissions = Permission::whereIn('name', $permissionNames)->pluck('name')->all();

        if (empty($existingPermissions)) {
            return $next($request);
        }

        foreach ($existingPermissions as $permission) {
            if ($user->can($permission)) {
                return $next($request);
            }
        }

        abort(403, 'No tienes permisos para acceder a este recurso.');
    }

    private function buildCandidates(string $routeName): array
    {
        $candidates = [
            $routeName,
            str_replace('.', '_', $routeName),
        ];

        $parts = explode('.', $routeName);
        $resource = $parts[0] ?? null;
        $action = $parts[1] ?? null;

        if ($resource && $action) {
            $normalizedAction = $this->normalizeAction($action);
            $candidates[] = "{$resource}.{$normalizedAction}";
            $candidates[] = "{$resource}_{$normalizedAction}";
        }

        return array_values(array_unique(array_filter($candidates)));
    }

    private function normalizeAction(string $action): string
    {
        return match ($action) {
            'index', 'show' => 'view',
            'create', 'store' => 'create',
            'edit', 'update' => 'edit',
            'destroy' => 'delete',
            default => $action,
        };
    }
}
