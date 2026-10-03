<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function mostrarFormulario()
    {
        if (Auth::check()) {
            return $this->redireccionarSegunRol(Auth::user());
        }
        return view('auth.login');
    }

    public function procesarLogin(Request $request)
    {
        $request->validate([
            'identificador' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($this->authService->attemptLogin($request->identificador, $request->password, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return $this->redireccionarSegunRol(Auth::user());
        }

        return back()->withErrors([
            'identificador' => 'Credenciales incorrectas o cuenta inactiva.',
        ])->onlyInput('identificador');
    }

    public function cerrarSesion(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    private function redireccionarSegunRol($usuario)
    {
        return $usuario->usu_rol === 'admin' 
            ? redirect()->route('admin.dashboard') 
            : redirect()->route('dashboard');
    }
}