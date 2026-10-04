<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use RuntimeException;

class AdminUsuarioController extends Controller
{
    /**
     * Gestion del personal administrativo.
     *
     * Solo se muestra el personal que el administrador ha incorporado
     * (rol distinto de 'estudiante'): la institucion tendra miles de
     * estudiantes y no tiene sentido listarlos aqui.
     *
     * Para sumar a alguien nuevo se usa "agregarUsuario", que busca por
     * correo o cedula y le asigna el rol.
     *
     * Solo accesible por el rol administrador (middleware 'rol:administrador').
     */
    public function index()
    {
        $personal = Usuario::where('usu_rol', '!=', Rol::ESTUDIANTE)
            ->orderBy('usu_id')
            ->paginate(15);

        return view('admin.usuarios.index', compact('personal'));
    }

    /**
     * Agrega a una persona al personal administrativo.
     * Se busca por correo electronico o por cedula.
     */
    public function agregarUsuario(Request $request)
    {
        $datos = $request->validate([
            'busqueda' => ['required', 'string', 'max:100'],
            'rol' => ['required', 'string', 'in:'.implode(',', Rol::administrativos())],
        ]);

        $termino = trim($datos['busqueda']);

        $usuario = Usuario::where('usu_correo_electronico', $termino)
            ->orWhere('usu_numero_documento', $termino)
            ->first();

        if (! $usuario) {
            return back()->with(
                'error',
                "No se encontró ningún usuario con el correo o cédula «{$termino}». Verifica que esté registrado."
            );
        }

        $this->evitarDegradarUltimoAdministrador($usuario, $datos['rol']);

        $usuario->update(['usu_rol' => $datos['rol']]);

        return back()->with(
            'success',
            "{$usuario->usu_correo_electronico} ahora tiene el rol de ".Rol::etiqueta($datos['rol']).'.'
        );
    }

    /**
     * Cambia el rol de un usuario ya agregado al personal administrativo.
     */
    public function actualizarRol(Request $request, $id)
    {
        $datos = $request->validate([
            'rol' => ['required', 'string', 'in:'.implode(',', Rol::administrativos())],
        ]);

        $usuario = Usuario::findOrFail($id);

        $this->evitarDegradarUltimoAdministrador($usuario, $datos['rol']);

        $usuario->update(['usu_rol' => $datos['rol']]);

        return back()->with('success', "Rol de {$usuario->usu_correo_electronico} actualizado a ".Rol::etiqueta($datos['rol']).'.');
    }

    /**
     * Impide que el sistema se quede sin administrador.
     */
    private function evitarDegradarUltimoAdministrador(Usuario $usuario, string $nuevoRol): void
    {
        // Usa las constantes de Rol directamente (y no los helpers del modelo)
        // para que este chequeo no dependa de metodos del modelo.
        if ($usuario->usu_rol === Rol::ESTUDIANTE
            || $usuario->usu_rol !== Rol::ADMINISTRADOR
            || $nuevoRol === Rol::ADMINISTRADOR) {
            return;
        }

        $otros = Usuario::where('usu_rol', Rol::ADMINISTRADOR)
            ->where('usu_id', '!=', $usuario->usu_id)
            ->count();

        if ($otros === 0) {
            throw new RuntimeException(
                'No puedes quitarle el rol de administrador: eres el único que lo tiene.'
            );
        }
    }
}
