<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\TipoDocumento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function mostrarFormulario()
    {
        return view('auth.register');
    }

    public function registrar(Request $request)
    {
<<<<<<< HEAD
        // 1. Reglas de validación ajustadas a los nombres EXACTOS de los inputs de tu formulario Blade
        $rules = [
            'primer_nombre'    => ['required', 'string', 'max:50'],
            'segundo_nombre'   => ['nullable', 'string', 'max:50'],
            'primer_apellido'  => ['required', 'string', 'max:50'],
            'segundo_apellido' => ['nullable', 'string', 'max:50'],
            'nacionalidad'     => ['required', 'string', 'in:V,E'],
            'cedula'           => ['required', 'string', 'max:8', 'unique:usuarios,usu_numero_documento'],
            'telefono'         => ['required', 'string', 'max:11'],
            'pnf'              => ['required', 'string'],
            'trayecto'         => ['required', 'string'],
            'email'            => ['required', 'string', 'email', 'max:255', 'unique:usuarios,usu_correo_electronico'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ];

        // 2. Mensajes de error personalizados en español
        $messages = [
            'primer_nombre.required'    => 'El primer nombre es obligatorio.',
            'primer_apellido.required'  => 'El primer apellido es obligatorio.',
            'nacionalidad.required'     => 'Selecciona la nacionalidad.',
            'cedula.required'           => 'El número de cédula es obligatorio.',
            'cedula.unique'             => 'Este número de cédula ya se encuentra registrado.',
            'telefono.required'         => 'El teléfono móvil es obligatorio.',
            'pnf.required'              => 'Debe seleccionar un PNF.',
            'trayecto.required'         => 'Debe seleccionar el trayecto actual.',
            'email.required'            => 'El correo electrónico es obligatorio.',
            'email.email'               => 'Debes ingresar un correo electrónico válido.',
            'email.unique'              => 'Este correo electrónico ya se encuentra registrado.',
            'password.required'         => 'La contraseña es obligatoria.',
            'password.min'              => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed'        => 'La confirmación de la contraseña no coincide.',
        ];

        $request->validate($rules, $messages);

        // 3. Obtención o creación del TipoDocumento según la nacionalidad seleccionada (V o E)
        $tipoCedula = TipoDocumento::firstOrCreate(
            ['tdo_abreviatura' => $request->nacionalidad],
            ['tdo_nombre_documento' => $request->nacionalidad == 'V' ? 'Cédula de Identidad' : 'Extranjero']
        );

        // 4. Creación del registro en la base de datos mapeando todos los campos
        $usuario = Usuario::create([
            'usu_rol'                => 'estudiante',
            'usu_tdo_id'             => $tipoCedula->tdo_id,
            'usu_primer_nombre'      => $request->primer_nombre,
            'usu_segundo_nombre'     => $request->segundo_nombre,
            'usu_primer_apellido'    => $request->primer_apellido,
            'usu_segundo_apellido'   => $request->segundo_apellido,
            'usu_numero_documento'   => $request->cedula,
            'usu_telefono'           => $request->telefono,
            'usu_pnf'                => $request->pnf,
            'usu_trayecto'           => $request->trayecto,
            'usu_correo_electronico' => $request->email,
            'usu_contrasena_hash'    => Hash::make($request->password),
            'usu_estado_cuenta'      => 'activo'
        ]);

        // 5. Autenticación automática e ingreso al dashboard
=======
        // Validamos que los campos obligatorios cumplan con los límites de la base de datos
        $request->validate([
            'nombre'    => 'required|string|max:50',
            'apellido'  => 'required|string|max:50',
            'documento' => 'required|string|max:20|unique:usuarios,usu_numero_documento',
            'email'     => 'required|email|max:100|unique:usuarios,usu_correo_electronico',
            'password'  => 'required|min:6'
        ]);

        // Antes: 'usu_tdo_id' => 1 (fijo). Eso asumía que el ID 1 de
        // tipo_documentos SIEMPRE es "Cédula" — pero ese número puede
        // cambiar entre entornos (SQLite vs PostgreSQL, orden del seeder,
        // etc.). Buscamos por NOMBRE, y si no existe todavía, lo creamos
        // (así el registro nunca se rompe por esto).
        $tipoCedula = TipoDocumento::firstOrCreate(
            ['tdo_abreviatura' => 'V'],
            ['tdo_nombre_documento' => 'Cédula de Identidad']
        );

        // Creamos el usuario mapeando los inputs del formulario con las columnas de tu tabla
        $usuario = Usuario::create([
            'usu_rol'              => 'estudiante', // Rol por defecto para nuevos registros
            'usu_tdo_id'           => $tipoCedula->tdo_id,
            'usu_primer_nombre'    => $request->nombre,
            'usu_primer_apellido'  => $request->apellido,
            'usu_numero_documento' => $request->documento,
            'usu_correo_electronico' => $request->email,
            'usu_contrasena_hash'  => Hash::make($request->password), // Encriptación segura de contraseña
            'usu_estado_cuenta'    => 'activo'
        ]);

        // Iniciamos sesión automáticamente tras el registro exitoso
>>>>>>> 7d0685b4379ba4764a11a7f976b77bb0be3b5bb1
        Auth::login($usuario);

        return redirect()->route('dashboard');
    }
}