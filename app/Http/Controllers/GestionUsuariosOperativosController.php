<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PermisoUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class GestionUsuariosOperativosController extends Controller
{
    /**
     * Mostrar lista de usuarios operativos
     */
    public function listaUsuarios()
    {
        $usuarios = User::where('tipo', 'Operativo')
            ->with('permisos')
            ->orderBy('nombre')
            ->get();
            
        return view('users.configuracion.usuarios_operativos.lista_usuarios', compact('usuarios'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function formularioCrear()
    {
        $modulos = PermisoUsuario::getModulosDisponibles();
        return view('users.configuracion.usuarios_operativos.crear_usuarios', compact('modulos'));
    }

    /**
     * Crear nuevo usuario operativo
     */
    public function crearUsuario(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'permisos' => 'array'
        ]);

        DB::transaction(function () use ($request) {
            // Crear usuario
            $usuario = User::create([
                'nombre' => $request->nombre,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'tipo' => 'Operativo',
                'activo' => true
            ]);

            // Crear permisos
            if ($request->has('permisos')) {
                foreach ($request->permisos as $modulo => $acciones) {
                    PermisoUsuario::create([
                        'user_id' => $usuario->id,
                        'modulo' => $modulo,
                        'ver' => isset($acciones['ver']),
                        'crear' => isset($acciones['crear']),
                        'editar' => isset($acciones['editar']),
                        'eliminar' => isset($acciones['eliminar'])
                    ]);
                }
            }
        });

        return redirect()->route('users.configuracionusuarios.operativos.lista_usuarios')
            ->with('success', 'Usuario operativo creado correctamente');
    }

    /**
     * Mostrar formulario de edición
     */
    public function formularioEditar($id)
    {
        $usuario = User::with('permisos')->findOrFail($id);
        
        // Verificar que sea un usuario operativo
        if (!$usuario->esOperativo()) {
            abort(403, 'Solo se pueden editar usuarios operativos');
        }
        
        $modulos = PermisoUsuario::getModulosDisponibles();
        
        // Organizar permisos existentes
        $permisosActuales = [];
        foreach ($usuario->permisos as $permiso) {
            $permisosActuales[$permiso->modulo] = [
                'ver' => $permiso->ver,
                'crear' => $permiso->crear,
                'editar' => $permiso->editar,
                'eliminar' => $permiso->eliminar
            ];
        }
        
        return view('users.configuracion.usuarios_operativos.editar_usuario', compact('usuario', 'modulos', 'permisosActuales'));
    }

    /**
     * Actualizar usuario operativo
     */
    public function actualizarUsuario(Request $request, $id)
    {
        $usuario = User::findOrFail($id);
        
        if (!$usuario->esOperativo()) {
            abort(403, 'Solo se pueden editar usuarios operativos');
        }
        
        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
            'permisos' => 'array'
        ]);

        DB::transaction(function () use ($request, $usuario) {
            // Actualizar datos básicos
            $usuario->update([
                'nombre' => $request->nombre,
                'email' => $request->email
            ]);
            
            // Actualizar contraseña si se proporciona
            if ($request->filled('password')) {
                $usuario->update(['password' => Hash::make($request->password)]);
            }

            // Eliminar permisos anteriores
            $usuario->permisos()->delete();

            // Crear nuevos permisos
            if ($request->has('permisos')) {
                foreach ($request->permisos as $modulo => $acciones) {
                    PermisoUsuario::create([
                        'user_id' => $usuario->id,
                        'modulo' => $modulo,
                        'ver' => isset($acciones['ver']),
                        'crear' => isset($acciones['crear']),
                        'editar' => isset($acciones['editar']),
                        'eliminar' => isset($acciones['eliminar'])
                    ]);
                }
            }
        });

        return redirect()->route('usuarios.operativos.lista')
            ->with('success', 'Usuario actualizado correctamente');
    }

    /**
     * Cambiar estado activo/inactivo
     */
    public function cambiarEstado($id)
    {
        $usuario = User::findOrFail($id);
        
        if (!$usuario->esOperativo()) {
            abort(403, 'Solo se puede cambiar el estado de usuarios operativos');
        }
        
        $usuario->activo = !$usuario->activo;
        $usuario->save();
        
        $mensaje = $usuario->activo ? 'Usuario activado' : 'Usuario desactivado';
        
        return redirect()->back()->with('success', $mensaje);
    }

    /**
     * Eliminar usuario operativo
     */
    public function eliminarUsuario($id)
    {
        $usuario = User::findOrFail($id);
        
        if (!$usuario->esOperativo()) {
            abort(403, 'Solo se pueden eliminar usuarios operativos');
        }
        
        $usuario->delete();
        
        return redirect()->route('usuarios.operativos.lista')
            ->with('success', 'Usuario eliminado correctamente');
    }
}