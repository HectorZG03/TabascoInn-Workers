<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermiso
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $modulo
     * @param  string  $accion
     */
    public function handle(Request $request, Closure $next, $modulo, $accion = 'ver')
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para acceder.');
        }
        
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Los administradores tienen acceso total
        if ($user->esAdministrador()) {
            return $next($request);
        }
        
        // Verificar permisos para usuarios operativos
        if (!$user->tienePermiso($modulo, $accion)) {
            abort(403, 'No tienes permisos para realizar esta acción.');
        }
        
        return $next($request);
    }
}