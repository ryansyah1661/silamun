<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to restrict access based on user role.
 *
 * Accepts comma-separated role values as parameter.
 * Usage in routes: middleware('role:super_admin,verifikator')
 *
 * Must be registered in app/Http/Kernel.php as 'role' alias:
 *   'role' => \App\Http\Middleware\RoleMiddleware::class,
 */
class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $roles  Comma-separated list of allowed role values
     */
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Autentikasi diperlukan.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $allowedRoles = array_map('trim', explode(',', $roles));

        if (! in_array($user->role?->value, $allowedRoles)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk melakukan tindakan ini.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
