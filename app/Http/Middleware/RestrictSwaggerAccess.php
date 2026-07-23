<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictSwaggerAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isProduction = config('app.env') === 'production';
        $swaggerEnabled = filter_var(env('SWAGGER_ENABLE', !$isProduction), FILTER_VALIDATE_BOOLEAN);

        // Security Rule 1: Disable Swagger in Production by default unless SWAGGER_ENABLE=true
        if ($isProduction && !$swaggerEnabled) {
            abort(404);
        }

        // Security Rule 2: Require Super Admin role if SWAGGER_REQUIRE_AUTH=true
        $requireAuth = filter_var(env('SWAGGER_REQUIRE_AUTH', false), FILTER_VALIDATE_BOOLEAN);
        if ($requireAuth) {
            $user = $request->user();
            if (!$user || !in_array($user->role, ['super_admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ke dokumentasi API Swagger dibatasi hanya untuk Super Admin.'
                ], 403);
            }
        }

        return $next($request);
    }
}
