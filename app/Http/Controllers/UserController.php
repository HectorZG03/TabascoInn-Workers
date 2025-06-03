<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UserController extends Controller
{
    /**
     * Mostrar el menú de configuración del usuario
     */
    public function configMenu()
    {
        $user = Auth::user();
        
        // Datos adicionales para la vista
        $data = [
            'user' => $user,
            'lastLogin' => $user->last_login_at ? Carbon::parse($user->last_login_at)->format('d/m/Y H:i') : 'Primera vez',
            'memberSince' => $user->created_at->format('d/m/Y'),
            'systemVersion' => '1.0.0',
            'accountStatus' => 'Activa'
        ];
        
        return view('users.config_menu', $data);
    }
    
    /**
     * Mostrar formulario de perfil (placeholder)
     */
    public function profile()
    {
        return view('users.profile');
    }
    
    /**
     * Mostrar formulario de cambio de contraseña (placeholder)
     */
    public function changePassword()
    {
        return view('users.change-password');
    }
    
    /**
     * Mostrar preferencias del usuario (placeholder)
     */
    public function preferences()
    {
        return view('users.preferences');
    }
    
    /**
     * Mostrar actividad reciente (placeholder)
     */
    public function activity()
    {
        return view('users.activity');
    }
}