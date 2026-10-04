<?php

namespace App\Http\Controllers;

use App\Models\TipoDocumento;
use App\Models\Usuario; // Importación necesaria para crear el registro
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; // Importación necesaria para encriptar la clave

class RegisterController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function mostrarFormulario()
    {
        // Traemos los tipos de documentos para el <select>
        $tiposDocumentos = TipoDocumento::all();
        return view('auth.register', compact('tiposDocumentos'));
    }

    public function registrar(Request $request)
    {
        // 1. Fusión: Tus validaciones estrictas (regex) adaptadas a los nuevos campos del equipo
        $rules = [
            'primer_nombre'    => ['required', 'string', 'max:50', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/'],
            'segundo_nombre'   => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/'],
            'primer_apellido'  => ['required', 'string', 'max:50', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/'],
            'segundo_apellido' => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/'],
            'nacionalidad'     => ['required', 'string', 'in:V,E'],
            'cedula'           => ['required', 'string', 'min:6', 'max:20', 'unique:usuarios,usu_numero_documento', 'regex:/^[0-9]+$/'],
            'telefono'         => ['required', 'string', 'max:20', 'regex:/^[0-9\-\+\s]+$/'],
            'pnf'              => ['required', 'string'],
            'trayecto'         => ['required', 'string'],
            'email'            => ['required', 'string', 'email:rfc,dns', 'max:100', 'unique:usuarios,usu_correo_electronico'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ];

        // 2. Mensajes de error personalizados (incluyendo tus advertencias de formato)
        $messages = [
            'primer_nombre.regex'       => 'El primer nombre solo debe contener letras.',
            'primer_apellido.regex'     => 'El primer apellido solo debe contener letras.',
            'cedula.regex'              => 'La cédula solo debe contener números.',
            'primer_nombre.required'    => 'El primer nombre es obligatorio.',
            'primer_apellido.required'  => 'El primer apellido es obligatorio.',
            'nacionalidad.required'     => 'Selecciona la nacionalidad.',
            'cedula.required'           => 'El número de cédula es obligatorio.',
            'cedula.unique'             => 'Este número de cédula ya se encuentra registrado.',
            'telefono.required'         => 'El teléfono móvil es obligatorio.',
            'telefono.regex'            => 'El teléfono contiene caracteres no válidos.',
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

        // 3. Obtención o creación del TipoDocumento según la nacionalidad seleccionada
        $tipoCedula = TipoDocumento::firstOrCreate(
            ['tdo_abreviatura' => $request->nacionalidad],
            ['tdo_nombre_documento' => $request->nacionalidad == 'V' ? 'Cédula de Identidad' : 'Extranjero']
        );

        // 4. Creación del registro integrando todos los campos requeridos
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
        Auth::login($usuario);

        return redirect()->route('dashboard');
    }
}