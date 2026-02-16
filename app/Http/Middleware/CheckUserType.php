<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$types
     */
    public function handle(Request $request, Closure $next, ...$types): Response
    {
        // Verificar si el usuario está autenticado
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para acceder.');
        }

        $user = Auth::user();
        
        // Verificar si el tipo de usuario está permitido
        if (in_array($user->tipo, $types)) {
            return $next($request);
        }

        // Si no tiene permisos, devolver error 403
        abort(403, 'No tienes permisos para acceder a esta sección. Acceso restringido a: ' . implode(', ', $types));
    }
}   