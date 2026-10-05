<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use RuntimeException;

class AdminUserController extends Controller
{
    // Carga la vista con la tabla vacía
    public function index()
    {
        $roles = Rol::administrativos();

        return view('admin.gestion_usuarios.index', compact('roles'));
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
            'usu_correo_electronico', 'usu_numero_telefono', 'usu_estado_cuenta',
        ])
            ->where('usu_primer_nombre', 'LIKE', "%{$query}%")
            ->orWhere('usu_primer_apellido', 'LIKE', "%{$query}%")
            ->orWhere('usu_correo_electronico', 'LIKE', "%{$query}%")
            ->orWhere('usu_rol', 'LIKE', "%{$query}%")
            ->limit(50) // Límite de seguridad por si la búsqueda es muy general
            ->get();

        return response()->json($usuarios);
    }

    /**
     * Agrega a una persona al personal administrativo.
     *
     * Solo se busca por CORREO ELECTRONICO: es un dato unico y
     * verificado por cada persona, a diferencia de la cédula, que
     * puede estar mal escrita.
     */
    public function agregarPorCorreo(Request $request)
    {
        $datos = $request->validate(
            [
                'email' => ['required', 'string', 'email', 'max:100'],
                'rol' => ['required', 'string', 'in:'.implode(',', Rol::administrativos())],
            ],
            [
                'email.required' => 'Escribe el correo electrónico de la persona.',
                'email.email' => 'El correo electrónico no tiene un formato válido.',
            ]
        );

        $email = trim($datos['email']);

        $usuario = Usuario::where('usu_correo_electronico', $email)->first();

        if (! $usuario) {
            return back()->with(
                'error',
                "No se encontró ningún usuario registrado con el correo «{$email}». Verifica que esté bien escrito."
            );
        }

        $this->evitarDegradarUltimoAdministrador($usuario, $datos['rol']);

        $usuario->update(['usu_rol' => $datos['rol']]);

        return back()->with(
            'success',
            "{$usuario->usu_correo_electronico} ahora tiene el rol de ".Rol::etiqueta($datos['rol']).'.'
        );
    }

    // Actualiza los datos públicos del usuario
    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);

        $request->validate([
            'usu_primer_nombre' => 'required|string|max:50',
            'usu_primer_apellido' => 'required|string|max:50',
            'usu_correo_electronico' => 'required|email|max:100|unique:usuarios,usu_correo_electronico,'.$id.',usu_id',
            // Jerarquía completa: administrador, analista, taquillero y estudiante
            'usu_rol' => 'required|in:'.implode(',', Rol::todos()),
        ]);

        $this->evitarDegradarUltimoAdministrador($usuario, $request->input('usu_rol'));

        $usuario->update($request->only([
            'usu_primer_nombre', 'usu_segundo_nombre', 'usu_primer_apellido',
            'usu_segundo_apellido', 'usu_numero_telefono', 'usu_correo_electronico', 'usu_rol',
        ]));

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    // Desbloquea la cuenta
    public function unlock($id)
    {
        $usuario = Usuario::findOrFail($id);
        // Ajusta 'activo' según los estados exactos que manejes en tu BD
        $usuario->usu_estado_cuenta = 'activo';
        $usuario->save();

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario desbloqueado.');
    }

    // Elimina el usuario
    public function destroy($id)
    {
        $usuario = Usuario::findOrFail($id);

        // No dejar el sistema sin ningún administrador.
        if ($usuario->esAdministrador() && $this->otrosAdministradores() === 0) {
            return back()->with('error', 'No puedes eliminar al único administrador del sistema.');
        }

        $usuario->delete();

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario eliminado de forma permanente.');
    }

    /**
     * Impide que el sistema se quede sin administrador: nadie puede
     * quitarse el rol ni ser eliminado si es el único que lo tiene.
     */
    private function evitarDegradarUltimoAdministrador(Usuario $usuario, string $nuevoRol): void
    {
        if (! $usuario->esAdministrador() || $nuevoRol === Rol::ADMINISTRADOR) {
            return;
        }

        if ($this->otrosAdministradores($usuario->usu_id) === 0) {
            throw new RuntimeException(
                'No puedes quitarle el rol de administrador: eres el único que lo tiene.'
            );
        }
    }

    private function otrosAdministradores(?int $exceptoId = null): int
    {
        return Usuario::where('usu_rol', Rol::ADMINISTRADOR)
            ->when($exceptoId, fn ($q) => $q->where('usu_id', '!=', $exceptoId))
            ->count();
    }
}
