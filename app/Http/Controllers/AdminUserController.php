<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    // Carga la vista con la tabla vacía
    public function index()
    {
        return view('admin.gestion_usuarios.index');
    }

    // Procesa la búsqueda desde el frontend (AJAX)
    public function search(Request $request)
    {
        $query = $request->get('q');

        if (empty($query)) {
            return response()->json([]);
        }

        // Seleccionamos solo los datos públicos, NUNCA la contraseña[cite: 4, 5]
        $usuarios = Usuario::select([
                'usu_id', 'usu_rol', 'usu_primer_nombre', 'usu_segundo_nombre', 
                'usu_primer_apellido', 'usu_segundo_apellido', 'usu_numero_documento', 
                'usu_correo_electronico', 'usu_numero_telefono', 'usu_estado_cuenta'
            ])
            ->where('usu_primer_nombre', 'LIKE', "%{$query}%")
            ->orWhere('usu_primer_apellido', 'LIKE', "%{$query}%")
            ->orWhere('usu_correo_electronico', 'LIKE', "%{$query}%")
            ->orWhere('usu_rol', 'LIKE', "%{$query}%")
            ->limit(50) // Límite de seguridad por si la búsqueda es muy general
            ->get();

        return response()->json($usuarios);
    }

    // Actualiza los datos públicos del usuario
    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);
        
        $request->validate([
            'usu_primer_nombre' => 'required|string|max:50',
            'usu_primer_apellido' => 'required|string|max:50',
            'usu_correo_electronico' => 'required|email|max:100|unique:usuarios,usu_correo_electronico,'.$id.',usu_id',
            'usu_rol' => 'required|in:estudiante,admin',
            // Agrega más validaciones según necesites basándote en la migración
        ]);

        $usuario->update($request->only([
            'usu_primer_nombre', 'usu_segundo_nombre', 'usu_primer_apellido', 
            'usu_segundo_apellido', 'usu_numero_telefono', 'usu_correo_electronico', 'usu_rol'
        ]));

        return redirect()->route('admin.gestion_usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    // Desbloquea la cuenta
    public function unlock($id)
    {
        $usuario = Usuario::findOrFail($id);
        // Ajusta 'activo' según los estados exactos que manejes en tu BD
        $usuario->usu_estado_cuenta = 'activo'; 
        $usuario->save();

        return redirect()->route('admin.gestion_usuarios.index')->with('success', 'Usuario desbloqueado.');
    }

    // Elimina el usuario
    public function destroy($id)
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->delete();

        return redirect()->route('admin.gestion_usuarios.index')->with('success', 'Usuario eliminado de forma permanente.');
    }
}