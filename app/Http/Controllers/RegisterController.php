<?php

namespace App\Http\Controllers;

use App\Models\TipoDocumento;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        // Validaciones estrictas
        $validatedData = $request->validate([
            'tipo_documento'   => 'required|exists:tipo_documentos,tdo_id',
            'nombre'           => 'required|string|max:50|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'segundo_nombre'   => 'nullable|string|max:50|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'apellido'         => 'required|string|max:50|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'segundo_apellido' => 'nullable|string|max:50|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'documento'        => 'required|string|min:6|max:20|unique:usuarios,usu_numero_documento|regex:/^[0-9]+$/',
            'email'            => 'required|email:rfc,dns|max:100|unique:usuarios,usu_correo_electronico',
            'telefono'         => 'nullable|string|max:20|regex:/^[0-9\-\+\s]+$/',
            'password'         => 'required|string|min:8|confirmed', // Requiere campo password_confirmation
        ], [
            'documento.regex' => 'El documento solo debe contener números.',
            'nombre.regex' => 'El nombre solo debe contener letras.',
        ]);

        $usuario = $this->authService->registerUser($validatedData);

        Auth::login($usuario);

        return redirect()->route('dashboard');
    }
}