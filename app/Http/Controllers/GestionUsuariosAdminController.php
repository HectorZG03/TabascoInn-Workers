<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GestionUsuariosAdminController extends Controller
{
    /**
     * Email del superadmin hardcodeado
     */
    private const SUPER_ADMIN_EMAIL = 'superadmin@sistema.com';

    /**
     * Verificar si el usuario actual es el superadmin
     */
    private function verificarAccesoSuperAdmin()
    {
        if (!Auth::check() || Auth::user()->email !== self::SUPER_ADMIN_EMAIL) {
            abort(403, 'Acceso restringido solo al Super Administrador');
        }
    }

    /**
     * Mostrar lista de usuarios administrativos
     */
    public function listaUsuariosAdmin()
    {
        $this->verificarAccesoSuperAdmin();

        $usuarios = User::whereIn('tipo', ['Gerencia', 'Recursos_Humanos'])
            ->where('email', '!=', self::SUPER_ADMIN_EMAIL) // Excluir al superadmin de la lista
            ->orderBy('tipo')
            ->orderBy('nombre')
            ->get();

        // Estadísticas
        $estadisticas = [
            'total_usuarios' => $usuarios->count(),
            'gerencia' => $usuarios->where('tipo', 'Gerencia')->count(),
            'recursos_humanos' => $usuarios->where('tipo', 'Recursos_Humanos')->count(),
            'activos' => $usuarios->where('activo', true)->count(),
            'inactivos' => $usuarios->where('activo', false)->count(),
        ];
            
        return view('users.configuracion.usuarios_admin.lista_usuarios_admin', compact('usuarios', 'estadisticas'));
    }

    /**
     * Mostrar formulario de creación de usuario administrativo
     */
    public function formularioCrearAdmin()
    {
        $this->verificarAccesoSuperAdmin();
        
        return view('users.configuracion.usuarios_admin.crear_usuario_admin');
    }

    /**
     * Crear nuevo usuario administrativo
     */
    public function crearUsuarioAdmin(Request $request)
    {
        $this->verificarAccesoSuperAdmin();

        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'tipo' => 'required|in:Gerencia,Recursos_Humanos',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.max' => 'El nombre no puede tener más de 255 caracteres',
            'email.required' => 'El correo electrónico es obligatorio',
            'email.email' => 'Debe ser un correo electrónico válido',
            'email.unique' => 'Este correo ya está registrado en el sistema',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
            'tipo.required' => 'El tipo de usuario es obligatorio',
            'tipo.in' => 'Tipo de usuario no válido',
        ]);

        DB::transaction(function () use ($request) {
            User::create([
                'nombre' => $request->nombre,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'tipo' => $request->tipo,
                'activo' => true
            ]);
        });

        $tipoUsuario = $request->tipo === 'Gerencia' ? 'Gerente' : 'Usuario de Recursos Humanos';
        
        return redirect()->route('usuarios.admin.lista')
            ->with('success', "$tipoUsuario creado correctamente");
    }

    /**
     * Mostrar formulario de edición de usuario administrativo
     */
    public function formularioEditarAdmin($id)
    {
        $this->verificarAccesoSuperAdmin();

        $usuario = User::findOrFail($id);
        
        // Verificar que no sea el superadmin
        if ($usuario->email === self::SUPER_ADMIN_EMAIL) {
            abort(403, 'No se puede editar al Super Administrador');
        }
        
        // Verificar que sea un usuario administrativo
        if (!in_array($usuario->tipo, ['Gerencia', 'Recursos_Humanos'])) {
            abort(403, 'Solo se pueden editar usuarios administrativos');
        }
        
        return view('users.configuracion.usuarios_admin.editar_usuario_admin', compact('usuario'));
    }

    /**
     * Actualizar usuario administrativo
     */
    public function actualizarUsuarioAdmin(Request $request, $id)
    {
        $this->verificarAccesoSuperAdmin();

        $usuario = User::findOrFail($id);
        
        // Verificar que no sea el superadmin
        if ($usuario->email === self::SUPER_ADMIN_EMAIL) {
            abort(403, 'No se puede editar al Super Administrador');
        }
        
        // Verificar que sea un usuario administrativo
        if (!in_array($usuario->tipo, ['Gerencia', 'Recursos_Humanos'])) {
            abort(403, 'Solo se pueden editar usuarios administrativos');
        }
        
        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:8|confirmed',
            'tipo' => 'required|in:Gerencia,Recursos_Humanos',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.max' => 'El nombre no puede tener más de 255 caracteres',
            'email.required' => 'El correo electrónico es obligatorio',
            'email.email' => 'Debe ser un correo electrónico válido',
            'email.unique' => 'Este correo ya está registrado en el sistema',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
            'tipo.required' => 'El tipo de usuario es obligatorio',
            'tipo.in' => 'Tipo de usuario no válido',
        ]);

        DB::transaction(function () use ($request, $usuario) {
            // Actualizar datos básicos
            $usuario->update([
                'nombre' => $request->nombre,
                'email' => $request->email,
                'tipo' => $request->tipo,
            ]);
            
            // Actualizar contraseña si se proporciona
            if ($request->filled('password')) {
                $usuario->update(['password' => Hash::make($request->password)]);
            }
        });

        return redirect()->route('usuarios.admin.lista')
            ->with('success', 'Usuario actualizado correctamente');
    }

    /**
     * Cambiar estado activo/inactivo de usuario administrativo
     */
    public function cambiarEstadoAdmin($id)
    {
        $this->verificarAccesoSuperAdmin();

        $usuario = User::findOrFail($id);
        
        // Verificar que no sea el superadmin
        if ($usuario->email === self::SUPER_ADMIN_EMAIL) {
            abort(403, 'No se puede cambiar el estado del Super Administrador');
        }
        
        // Verificar que sea un usuario administrativo
        if (!in_array($usuario->tipo, ['Gerencia', 'Recursos_Humanos'])) {
            abort(403, 'Solo se puede cambiar el estado de usuarios administrativos');
        }
        
        $usuario->activo = !$usuario->activo;
        $usuario->save();
        
        $mensaje = $usuario->activo ? 'Usuario activado correctamente' : 'Usuario desactivado correctamente';
        
        return redirect()->back()->with('success', $mensaje);
    }

    /**
     * Eliminar usuario administrativo
     */
    public function eliminarUsuarioAdmin($id)
    {
        $this->verificarAccesoSuperAdmin();

        $usuario = User::findOrFail($id);
        
        // Verificar que no sea el superadmin
        if ($usuario->email === self::SUPER_ADMIN_EMAIL) {
            abort(403, 'No se puede eliminar al Super Administrador');
        }
        
        // Verificar que sea un usuario administrativo
        if (!in_array($usuario->tipo, ['Gerencia', 'Recursos_Humanos'])) {
            abort(403, 'Solo se pueden eliminar usuarios administrativos');
        }
        
        $usuario->delete();
        
        return redirect()->route('usuarios.admin.lista')
            ->with('success', 'Usuario eliminado correctamente');
    }
}