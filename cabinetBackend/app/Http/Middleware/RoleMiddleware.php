<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(
        Request $request,
        Closure $next,
        string ...$roles
    ): Response {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Non authentifié.'
            ], 401);
        }

        // L'administrateur a accès à toutes les routes
        // qui utilisent ce middleware.
        if ($user->role === 'administrateur') {
            return $next($request);
        }

        // Vérifier le rôle requis pour la route
        if (!in_array($user->role, $roles, true)) {
            return response()->json([
                'message' => 'Accès interdit : permissions insuffisantes.'
            ], 403);
        }

        return $next($request);
    }
}
