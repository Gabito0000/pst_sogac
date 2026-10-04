@extends('layouts.plantilla_admin')

@section('title', 'Usuarios y Roles')

@section('content')

@if(session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert--error">{{ session('error') }}</div>
@endif

<section class="hero">
    <h1>Usuarios y Roles</h1>
    <p>Agrega personal al sistema y define qué puede hacer cada uno dentro del panel administrativo.</p>
</section>

{{-- ============================================================ --}}
{{-- AGREGAR A UN USUARIO --}}
{{-- ============================================================ --}}
<div class="card">
    <h2 class="card__title">Agregar a un usuario</h2>
    <p class="card__sub">Busca a la persona por su correo electrónico o cédula y asígnale un rol.</p>

    <form method="POST" action="{{ route('admin.usuarios.agregar') }}"
          style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
        @csrf

        <div class="field" style="flex: 2; min-width: 260px;">
            <label for="busqueda">Correo electrónico o cédula</label>
            <input type="text" id="busqueda" name="busqueda" required
                   value="{{ old('busqueda') }}"
                   placeholder="ejemplo: gabriel@uptp.edu.ve o V-30123456" />
        </div>

        <div class="field" style="flex: 1; min-width: 190px;">
            <label for="rol">Rol que tendrá</label>
            <select id="rol" name="rol" required>
                @foreach (\App\Models\Rol::administrativos() as $opcion)
                    <option value="{{ $opcion }}" @selected(old('rol') === $opcion)>
                        {{ \App\Models\Rol::etiqueta($opcion) }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn--primary">Agregar</button>
    </form>

    <p style="color: var(--gray-700); font-size: 0.85rem; margin-top: 14px;">
        El usuario debe estar registrado en el sistema (puede haberse registrado como estudiante).
        Al agregarlo cambia su rol y accede al panel administrativo.
    </p>
</div>

{{-- ============================================================ --}}
{{-- PERSONAL ADMINISTRATIVO --}}
{{-- ============================================================ --}}
<div class="card">
    <h2 class="card__title">Personal administrativo</h2>
    <p class="card__sub">
        {{ $personal->total() }} persona(s) con acceso al panel. Los estudiantes no se listan aquí.
    </p>

    @if($personal->isEmpty())
        <p>Aún no hay personal administrativo. Usa el formulario de arriba para agregar al primero.</p>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Cédula</th>
                        <th>Rol</th>
                        <th style="width:230px;">Cambiar rol</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($personal as $usuario)
                        <tr>
                            <td><strong>{{ $usuario->usu_primer_nombre }} {{ $usuario->usu_primer_apellido }}</strong></td>
                            <td style="font-size:0.9rem;">{{ $usuario->usu_correo_electronico }}</td>
                            <td style="font-size:0.9rem;">{{ $usuario->usu_numero_documento }}</td>
                            <td>
                                <span class="badge badge--{{ $usuario->esAdministrador() ? 'aprobada' : 'rechazada' }}">
                                    {{ \App\Models\Rol::etiqueta($usuario->usu_rol) }}
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.usuarios.rol', $usuario->usu_id) }}"
                                      style="display:flex; gap:8px;">
                                    @csrf
                                    @method('PATCH')
                                    <select name="rol" style="flex:1; padding:8px 10px; border:1.5px solid var(--gray-200); border-radius:6px; font-size:0.9rem; font-family:inherit; background:var(--white);">
                                        @foreach (\App\Models\Rol::administrativos() as $opcion)
                                            <option value="{{ $opcion }}" @selected($usuario->usu_rol === $opcion)>
                                                {{ \App\Models\Rol::etiqueta($opcion) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn--primary btn--sm">Guardar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top:20px;">
            {{ $personal->links() }}
        </div>
    @endif
</div>

{{-- ============================================================ --}}
{{-- REFERENCIA DE LA JERARQUIA --}}
{{-- ============================================================ --}}
<div class="card">
    <h2 class="card__title">Jerarquía de roles</h2>
    <p class="card__sub">Permisos de cada rol dentro del sistema.</p>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th style="width:180px;">Rol</th>
                    <th>Capacidades</th>
                </tr>
            </thead>
            <tbody>
                @foreach (\App\Models\Rol::administrativos() as $rol)
                    <tr>
                        <td><strong>{{ \App\Models\Rol::etiqueta($rol) }}</strong></td>
                        <td style="color:var(--gray-700); font-size:0.9rem;">{{ \App\Models\Rol::descripcion($rol) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td><strong>{{ \App\Models\Rol::etiqueta(\App\Models\Rol::ESTUDIANTE) }}</strong></td>
                    <td style="color:var(--gray-700); font-size:0.9rem;">{{ \App\Models\Rol::descripcion(\App\Models\Rol::ESTUDIANTE) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection