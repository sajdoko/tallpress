<?php

namespace Sajdoko\TallPress\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTallPressRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        $roleField = config('tallpress.roles.role_field', 'role');
        $userRole = $request->user()->{$roleField};

        // Admin has access to everything
        if ($userRole === 'admin') {
            return $next($request);
        }

        // Check if user has one of the required roles
        if (empty($roles) || in_array($userRole, $roles)) {
            return $next($request);
        }

        abort(403, 'You do not have permission to access this resource.');
    }
}
