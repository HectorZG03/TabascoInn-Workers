<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Credenciales del Superadministrador hardcodeado
     */
    private const SUPER_ADMIN = [
        'email' => 'superadmin@sistema.com',
        'password' => 'SuperAdmin2024!',
        'nombre' => 'Super Administrador'
    ];

    /**
     * Mostrar el formulario de login
     */
    public function showLogin()
    {
        return view('auth.login');   
    }

    /**
     * Procesar el login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'El email es obligatorio',
            'email.email' => 'Ingrese un email institucional válido',
            'password.required' => 'La contraseña es obligatoria',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // ✅ VERIFICAR SI ES EL SUPERADMIN HARDCODEADO
        if ($request->email === self::SUPER_ADMIN['email'] && 
            $request->password === self::SUPER_ADMIN['password']) {
            
            // Buscar o crear el usuario superadmin en la BD
            $superAdmin = User::firstOrCreate(
                ['email' => self::SUPER_ADMIN['email']],
                [
                    'nombre' => self::SUPER_ADMIN['nombre'],
                    'password' => Hash::make(self::SUPER_ADMIN['password']),
                    'tipo' => 'Gerencia', // Le damos tipo Gerencia para tener todos los permisos
                    'activo' => true
                ]
            );

            // Si ya existe, actualizar para asegurar que tenga los permisos correctos
            if (!$superAdmin->wasRecentlyCreated) {
                $superAdmin->update([
                    'nombre' => self::SUPER_ADMIN['nombre'],
                    'password' => Hash::make(self::SUPER_ADMIN['password']),
                    'tipo' => 'Gerencia',
                    'activo' => true
                ]);
            }

            // Autenticar al superadmin
            Auth::login($superAdmin);
            $request->session()->regenerate();
            
            return redirect()->intended('/dashboard')->with('success', 
                '🔐 Bienvenido Super Administrador - Acceso Total al Sistema');
        }

        // ✅ CONTINUAR CON EL PROCESO NORMAL DE LOGIN
        $credentials = $request->only('email', 'password');

        // Verificar credenciales y que el usuario esté activo
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Verificar si el usuario está activo (solo para operativos)
            if ($user->tipo === 'Operativo' && !$user->activo) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Tu cuenta está desactivada. Contacta al administrador.',
                ])->withInput();
            }
            
            $request->session()->regenerate();
            
            return redirect()->intended('/dashboard')->with('success', 'Bienvenido al sistema');
        }

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->withInput();
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login')->with('success', 'Sesión cerrada correctamente');
    }
}