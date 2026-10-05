@extends('layouts.plantilla_admin')

@section('title', 'Gestión de Usuarios')

@section('content')
    @include('partials.avisos')

    <div class="pagina-head">
        <div>
            <h1 class="pagina-head__titulo">Gestión de usuarios</h1>
            <p class="pagina-head__desc">
                Agrega personal al sistema y ajusta su rol. Las personas deben estar
                registradas antes (pueden haberse registrado como estudiantes).
            </p>
        </div>
    </div>

    {{-- Agregar a un usuario al personal administrativo --}}
    <div class="card" style="margin-bottom: 24px;">
        <h2 class="card__title">Agregar a un usuario</h2>
        <p class="card__sub">Escribe el correo electrónico de la persona y asígnale un rol.</p>

        <form method="POST" action="{{ route('admin.usuarios.agregar') }}" style="margin-top: 18px;">
            @csrf
            <div class="form__row">
                <div class="field">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" required value="{{ old('email') }}"
                           placeholder="ejemplo: gabriel@uptp.edu.ve" />
                </div>
                <div class="field">
                    <label for="rol">Rol que tendrá</label>
                    <select id="rol" name="rol" required>
                        @foreach ($roles as $opcion)
                            <option value="{{ $opcion }}" @selected(old('rol') === $opcion)>{{ \App\Models\Rol::etiqueta($opcion) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn btn--primary">Agregar</button>
            </div>
        </form>
    </div>

    {{-- Jerarquía de roles --}}
    <details class="card card--plana" style="margin-bottom: 24px;">
        <summary class="card__title" style="cursor:pointer;">Jerarquía de roles y permisos</summary>

        <div class="table-wrap" style="margin-top: 16px;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Rol</th>
                        <th>Capacidades</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (\App\Models\Rol::administrativos() as $rol)
                        <tr>
                            <td><strong>{{ \App\Models\Rol::etiqueta($rol) }}</strong></td>
                            <td>{{ \App\Models\Rol::descripcion($rol) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td><strong>{{ \App\Models\Rol::etiqueta(\App\Models\Rol::ESTUDIANTE) }}</strong></td>
                        <td>{{ \App\Models\Rol::descripcion(\App\Models\Rol::ESTUDIANTE) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </details>

    {{-- Buscador --}}
    <div class="buscador-caja">
        <input type="text" id="searchInput" placeholder="Buscar por nombre, apellido, correo o rol..." autocomplete="off" />
        <p class="card__sub" style="flex-basis: 100%;">La tabla se llena a medida que escribes: al menos 2 caracteres.</p>
    </div>

    {{-- Resultados --}}
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Documento</th>
                    <th>Nombre completo</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th class="table__acciones">Acciones</th>
                </tr>
            </thead>
            <tbody id="resultsTable">
                <tr>
                    <td colspan="5">
                        <p class="estado-vacio">Escribe en el buscador para ver usuarios.</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Modales: usan el sistema comun (partials/modal) con .modal-overlay y
         data-abierto, en vez de la clase .hidden de Tailwind. --}}

    <div id="modal-ver" class="modal-overlay" data-abierto="0">
        <div class="modal">
            <h2 class="card__title">Información del usuario</h2>
            <div class="datos" id="ver-content" style="margin-top: 16px;"></div>
            <div class="actions">
                <button type="button" onclick="cerrarModal('modal-ver')" class="btn btn--ghost">Cerrar</button>
            </div>
        </div>
    </div>

    <div id="modal-editar" class="modal-overlay" data-abierto="0">
        <div class="modal">
            <h2 class="card__title">Editar usuario</h2>

            <form id="form-editar" method="POST" style="margin-top: 16px;">
                @csrf
                @method('PUT')

                <div class="form__row">
                    <div class="field">
                        <label for="edit_primer_nombre">Primer nombre</label>
                        <input type="text" name="usu_primer_nombre" id="edit_primer_nombre" required />
                    </div>
                    <div class="field">
                        <label for="edit_primer_apellido">Primer apellido</label>
                        <input type="text" name="usu_primer_apellido" id="edit_primer_apellido" required />
                    </div>
                </div>

                <div class="form__row">
                    <div class="field">
                        <label for="edit_correo">Correo electrónico</label>
                        <input type="email" name="usu_correo_electronico" id="edit_correo" required />
                    </div>
                    <div class="field">
                        <label for="edit_telefono">Teléfono</label>
                        <input type="tel" name="usu_numero_telefono" id="edit_telefono" />
                    </div>
                </div>

                <div class="field">
                    <label for="edit_rol">Rol del usuario</label>
                    <select name="usu_rol" id="edit_rol">
                        @foreach (\App\Models\Rol::todos() as $opcion)
                            <option value="{{ $opcion }}">{{ \App\Models\Rol::etiqueta($opcion) }}</option>
                        @endforeach
                    </select>
                    <span class="field__hint">Cambiar el rol altera lo que la persona puede ver y hacer.</span>
                </div>

                <div class="actions">
                    <button type="button" onclick="cerrarModal('modal-editar')" class="btn btn--ghost">Cancelar</button>
                    <button type="submit" class="btn btn--primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-advertencia-rol" class="modal-overlay" data-abierto="0">
        <div class="modal">
            <h2 class="card__title">Atención: cambio de privilegios</h2>
            <p class="card__sub" style="margin-top: 10px;">
                Estás a punto de modificar el nivel de acceso de este usuario en el sistema.
                Asegúrate de que esta acción está autorizada.
            </p>
            <div class="actions">
                <button type="button" onclick="cerrarModal('modal-advertencia-rol')" class="btn btn--danger">Entendido</button>
            </div>
        </div>
    </div>

    <div id="modal-desbloquear" class="modal-overlay" data-abierto="0">
        <div class="modal">
            <h2 class="card__title">Desbloquear usuario</h2>
            <p class="card__sub" style="margin-top: 10px;">
                ¿Seguro que deseas reactivar el acceso de <strong id="unlock_nombre"></strong>?
            </p>

            <form id="form-desbloquear" method="POST">
                @csrf
                <div class="actions">
                    <button type="button" onclick="cerrarModal('modal-desbloquear')" class="btn btn--ghost">Cancelar</button>
                    <button type="submit" class="btn btn--primary">Desbloquear</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-eliminar" class="modal-overlay" data-abierto="0">
        <div class="modal">
            <h2 class="card__title">Eliminar usuario definitivamente</h2>
            <p class="card__sub" style="margin-top: 10px;">
                Esta acción no se puede deshacer. Todos los datos, hilos de soporte y registros
                de <strong id="delete_nombre"></strong> podrían verse afectados.
            </p>

            <form id="form-eliminar" method="POST">
                @csrf
                @method('DELETE')
                <div class="actions">
                    <button type="button" onclick="cerrarModal('modal-eliminar')" class="btn btn--ghost">Cancelar</button>
                    <button type="submit" class="btn btn--danger">Eliminar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const input = document.getElementById('searchInput');
            const tabla = document.getElementById('resultsTable');
            const selectRol = document.getElementById('edit_rol');
            let temporizador;

            // Iconos de las acciones. Los trazos van completos: al cortarlos
            // el SVG dibuja solo un fragmento y el boton sale deformado.
            const ICONOS = {
                ver: 'M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
                editar: 'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z',
                desbloquear: 'M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z',
                eliminar: 'M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0',
            };

            const icono = (nombre) =>
                `<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="${ICONOS[nombre]}"/></svg>`;

            const aviso = (colspan, texto) =>
                `<tr><td colspan="${colspan}"><p class="estado-vacio">${texto}</p></td></tr>`;

            const chipRol = (rol) => {
                const clase = {
                    administrador: 'badge--rechazada',
                    analista: 'badge--pendiente',
                    taquillero: 'badge--aprobada',
                }[rol] || 'badge--activo';

                return `<span class="badge ${clase}">${rol}</span>`;
            };

            input.addEventListener('keyup', function () {
                clearTimeout(temporizador);
                const consulta = this.value.trim();

                if (consulta.length < 2) {
                    tabla.innerHTML = aviso(5, 'Escribe al menos 2 caracteres...');
                    return;
                }

                temporizador = setTimeout(function () {
                    fetch(`/admin/usuarios/buscar?q=${encodeURIComponent(consulta)}`)
                        .then((respuesta) => respuesta.json())
                        .then((usuarios) => {
                            if (usuarios.length === 0) {
                                tabla.innerHTML = aviso(5, 'No se encontraron usuarios.');
                                return;
                            }

                            tabla.innerHTML = usuarios.map(function (usuario) {
                                const nombre = `${usuario.usu_primer_nombre} ${usuario.usu_primer_apellido}`;

                                // Se serializa el usuario para pasarlo a los modales
                                const datos = JSON.stringify(usuario).replace(/"/g, '&quot;');

                                return `
                                    <tr>
                                        <td>${usuario.usu_numero_documento}</td>
                                        <td><strong>${nombre}</strong></td>
                                        <td>${usuario.usu_correo_electronico}</td>
                                        <td>${chipRol(usuario.usu_rol)}</td>
                                        <td class="table__acciones">
                                            <button type="button" onclick="abrirModalVer('${datos}')" class="btn btn--sm" title="Ver datos" aria-label="Ver datos de ${nombre}">${icono('ver')}</button>
                                            <button type="button" onclick="abrirModalEditar('${datos}')" class="btn btn--sm" title="Editar" aria-label="Editar a ${nombre}">${icono('editar')}</button>
                                            <button type="button" onclick="abrirModalDesbloquear('${usuario.usu_id}', '${nombre}')" class="btn btn--sm" title="Desbloquear" aria-label="Desbloquear a ${nombre}">${icono('desbloquear')}</button>
                                            <button type="button" onclick="abrirModalEliminar('${usuario.usu_id}', '${nombre}')" class="btn btn--sm btn--danger" title="Eliminar" aria-label="Eliminar a ${nombre}">${icono('eliminar')}</button>
                                        </td>
                                    </tr>`;
                            }).join('');
                        });
                }, 300);
            });

            window.abrirModalVer = function (usuarioJson) {
                const usuario = JSON.parse(usuarioJson);

                document.getElementById('ver-content').innerHTML = [
                    ['Documento', usuario.usu_numero_documento],
                    ['Estado', usuario.usu_estado_cuenta],
                    ['Primer nombre', usuario.usu_primer_nombre],
                    ['Segundo nombre', usuario.usu_segundo_nombre || 'N/A'],
                    ['Primer apellido', usuario.usu_primer_apellido],
                    ['Segundo apellido', usuario.usu_segundo_apellido || 'N/A'],
                    ['Correo', usuario.usu_correo_electronico],
                    ['Teléfono', usuario.usu_numero_telefono || 'N/A'],
                    ['Rol', usuario.usu_rol],
                    ['Creado el', usuario.usu_fecha_registro ? new Date(usuario.usu_fecha_registro).toLocaleString() : 'N/A'],
                    ['Último acceso', usuario.usu_ultimo_acceso ? new Date(usuario.usu_ultimo_acceso).toLocaleString() : 'Nunca'],
                ].map(([etiqueta, valor]) => `
                    <div class="datos__fila">
                        <strong>${etiqueta}</strong>
                        <span>${valor ?? 'N/A'}</span>
                    </div>`).join('');

                abrirModal('modal-ver');
            };

            window.abrirModalEditar = function (usuarioJson) {
                const usuario = JSON.parse(usuarioJson);

                document.getElementById('form-editar').action = `/admin/usuarios/${usuario.usu_id}`;
                document.getElementById('edit_primer_nombre').value = usuario.usu_primer_nombre;
                document.getElementById('edit_primer_apellido').value = usuario.usu_primer_apellido;
                document.getElementById('edit_correo').value = usuario.usu_correo_electronico;
                document.getElementById('edit_telefono').value = usuario.usu_numero_telefono || '';

                selectRol.value = usuario.usu_rol;
                selectRol.dataset.rolOriginal = usuario.usu_rol;

                abrirModal('modal-editar');
            };

            window.abrirModalDesbloquear = function (id, nombre) {
                document.getElementById('form-desbloquear').action = `/admin/usuarios/${id}/desbloquear`;
                document.getElementById('unlock_nombre').textContent = nombre;

                abrirModal('modal-desbloquear');
            };

            window.abrirModalEliminar = function (id, nombre) {
                document.getElementById('form-eliminar').action = `/admin/usuarios/${id}`;
                document.getElementById('delete_nombre').textContent = nombre;

                abrirModal('modal-eliminar');
            };

            selectRol.addEventListener('change', function () {
                if (this.value !== this.dataset.rolOriginal) {
                    abrirModal('modal-advertencia-rol');
                }
            });
        })();
    </script>
@endsection