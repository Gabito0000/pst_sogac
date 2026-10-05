@extends('layouts.plantilla_general')

@section('title', 'Gestión de Usuarios')

@section('content')
<div class="bg-white rounded-lg shadow p-6">

    @if(session('success'))
        <div class="mb-4 rounded border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Gestión de Usuarios</h2>
    </div>

    <!-- ===================================================== -->
    <!-- AGREGAR A UN USUARIO AL PERSONAL ADMINISTRATIVO        -->
    <!-- ===================================================== -->
    <div class="mb-8 rounded-lg border border-gray-200 bg-gray-50 p-5">
        <h3 class="text-lg font-bold text-gray-800 mb-1">Agregar a un usuario</h3>
        <p class="text-sm text-gray-600 mb-4">
            Escribe el correo electrónico de la persona y asígnale un rol.
            La persona debe estar registrada en el sistema (puede haberse registrado como estudiante).
        </p>

        <form method="POST" action="{{ route('admin.usuarios.agregar') }}"
              class="flex flex-wrap items-end gap-3">
            @csrf
            <div class="flex-1" style="min-width: 240px;">
                <label for="email" class="block text-xs font-medium text-gray-600 mb-1">Correo electrónico</label>
                <input type="email" id="email" name="email" required value="{{ old('email') }}"
                       placeholder="ejemplo: gabriel@uptp.edu.ve"
                       class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div style="min-width: 180px;">
                <label for="rol" class="block text-xs font-medium text-gray-600 mb-1">Rol que tendrá</label>
                <select id="rol" name="rol" required
                        class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($roles as $opcion)
                        <option value="{{ $opcion }}" @selected(old('rol') === $opcion)>{{ \App\Models\Rol::etiqueta($opcion) }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700 transition">
                Agregar
            </button>
        </form>
    </div>

    <!-- ===================================================== -->
    <!-- JERARQUÍA DE ROLES                                  -->
    <!-- ===================================================== -->
    <details class="mb-8 rounded-lg border border-gray-200 bg-white">
        <summary class="cursor-pointer px-5 py-3 font-semibold text-gray-800">
            Jerarquía de roles y permisos
        </summary>
        <div class="overflow-x-auto px-5 pb-4">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 border-b text-left text-xs uppercase text-gray-500">Rol</th>
                        <th class="px-4 py-2 border-b text-left text-xs uppercase text-gray-500">Capacidades</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach(\App\Models\Rol::administrativos() as $rol)
                        <tr>
                            <td class="px-4 py-2 font-semibold">{{ \App\Models\Rol::etiqueta($rol) }}</td>
                            <td class="px-4 py-2 text-gray-600">{{ \App\Models\Rol::descripcion($rol) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td class="px-4 py-2 font-semibold">{{ \App\Models\Rol::etiqueta(\App\Models\Rol::ESTUDIANTE) }}</td>
                        <td class="px-4 py-2 text-gray-600">{{ \App\Models\Rol::descripcion(\App\Models\Rol::ESTUDIANTE) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </details>

    <!-- Barra de Búsqueda -->
    <div class="mb-6 relative">
        <input type="text" id="searchInput" placeholder="Buscar por nombre, apellido, correo o rol..."
               class="w-full pl-10 pr-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm"
               autocomplete="off">
        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <p class="text-sm text-gray-500 mt-2">Usa el buscador para encontrar usuarios. La tabla no mostrará resultados hasta que realices una búsqueda.</p>
    </div>

    <!-- Tabla de Resultados -->
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase">Documento</th>
                    <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase">Nombre Completo</th>
                    <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase">Correo</th>
                    <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase">Rol</th>
                    <th class="px-6 py-3 border-b text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody id="resultsTable" class="divide-y divide-gray-200">
                <!-- Se llenará mediante JavaScript -->
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                        Esperando tu búsqueda...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ================= MODALES ================= -->
<div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 hidden z-40"></div>

<!-- 1. Modal: Ver Datos -->
<div id="modal-ver" class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow-xl w-11/12 md:w-1/2 z-50 hidden p-6">
    <h3 class="text-xl font-bold mb-4 border-b pb-2">Información del Usuario</h3>
    <div class="grid grid-cols-2 gap-4 text-sm" id="ver-content">
        <!-- Contenido dinámico -->
    </div>
    <div class="mt-6 text-right">
        <button onclick="cerrarModales()" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300">Cerrar</button>
    </div>
</div>

<!-- 2. Modal: Editar -->
<div id="modal-editar" class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow-xl w-11/12 md:w-1/2 z-50 hidden p-6">
    <h3 class="text-xl font-bold mb-4 border-b pb-2">Editar Usuario</h3>
    <form id="form-editar" method="POST">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Primer Nombre</label>
                <input type="text" name="usu_primer_nombre" id="edit_primer_nombre" class="mt-1 w-full border rounded p-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Primer Apellido</label>
                <input type="text" name="usu_primer_apellido" id="edit_primer_apellido" class="mt-1 w-full border rounded p-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                <input type="email" name="usu_correo_electronico" id="edit_correo" class="mt-1 w-full border rounded p-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                <input type="text" name="usu_numero_telefono" id="edit_telefono" class="mt-1 w-full border rounded p-2">
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700">Rol del Usuario</label>
                <!-- Jerarquía completa definida en App\Models\Rol -->
                <select name="usu_rol" id="edit_rol" class="mt-1 w-full border rounded p-2">
                    @foreach(\App\Models\Rol::todos() as $opcion)
                        <option value="{{ $opcion }}">{{ \App\Models\Rol::etiqueta($opcion) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <button type="button" onclick="cerrarModales()" class="bg-gray-200 px-4 py-2 rounded">Cancelar</button>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Guardar Cambios</button>
        </div>
    </form>
</div>

<!-- Modal: Advertencia de Cambio de Rol (Invocado por el select) -->
<div id="modal-advertencia-rol" class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-red-50 rounded-lg border-l-4 border-red-600 shadow-xl w-11/12 md:w-1/3 z-[60] hidden p-6">
    <h3 class="text-lg font-bold text-red-700 mb-2">¡Atención! Cambio de Privilegios</h3>
    <p class="text-sm text-red-800 mb-4 text-warning-placeholder">
        <!-- Puedes cambiar este texto después -->
        Estás a punto de modificar el nivel de acceso de este usuario en el sistema. Asegúrate de que esta acción está autorizada.
    </p>
    <div class="text-right">
        <button type="button" onclick="document.getElementById('modal-advertencia-rol').classList.add('hidden')" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Entendido</button>
    </div>
</div>

<!-- 3. Modal: Desbloquear -->
<div id="modal-desbloquear" class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow-xl w-11/12 md:w-1/3 z-50 hidden p-6">
    <h3 class="text-xl font-bold mb-2">Desbloquear Usuario</h3>
    <p class="text-gray-600 mb-4">¿Estás seguro de que deseas reactivar el acceso para <span id="unlock_nombre" class="font-bold"></span>?</p>
    
    <form id="form-desbloquear" method="POST">
        @csrf
        <div class="flex justify-end gap-2">
            <button type="button" onclick="cerrarModales()" class="bg-gray-200 px-4 py-2 rounded">Cancelar</button>
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Desbloquear</button>
        </div>
    </form>
</div>

<!-- 4. Modal: Eliminar -->
<div id="modal-eliminar" class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow-xl w-11/12 md:w-1/3 z-50 hidden p-6">
    <h3 class="text-xl font-bold text-red-600 mb-2">Eliminar Usuario Definitivamente</h3>
    <p class="text-gray-600 mb-4">Esta acción no se puede deshacer. Todos los datos, hilos de soporte y registros de <span id="delete_nombre" class="font-bold"></span> podrían verse afectados.</p>
    
    <form id="form-eliminar" method="POST">
        @csrf
        @method('DELETE')
        <div class="flex justify-end gap-2">
            <button type="button" onclick="cerrarModales()" class="bg-gray-200 px-4 py-2 rounded">Cancelar</button>
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Eliminar</button>
        </div>
    </form>
</div>

<!-- ================= JAVASCRIPT ================= -->
<script>
    // Variables globales
    const searchInput = document.getElementById('searchInput');
    const resultsTable = document.getElementById('resultsTable');
    let timerBuscador;

    // 1. Lógica del buscador AJAX
    searchInput.addEventListener('keyup', function() {
        clearTimeout(timerBuscador);
        const query = this.value;

        if (query.length < 2) {
            resultsTable.innerHTML = `<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">Escribe al menos 2 caracteres...</td></tr>`;
            return;
        }

        timerBuscador = setTimeout(() => {
            fetch(`/admin/usuarios/buscar?q=${query}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length === 0) {
                        resultsTable.innerHTML = `<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No se encontraron usuarios.</td></tr>`;
                        return;
                    }

                    resultsTable.innerHTML = '';
                    data.forEach(user => {
                        const nombreCompleto = `${user.usu_primer_nombre} ${user.usu_primer_apellido}`;
                        
                        // Serializar el objeto usuario para pasarlo a los modales
                        const userJSON = JSON.stringify(user).replace(/"/g, '&quot;');

                        resultsTable.innerHTML += `
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">${user.usu_numero_documento}</td>
                                <td class="px-6 py-4 whitespace-nowrap">${nombreCompleto}</td>
                                <td class="px-6 py-4 whitespace-nowrap">${user.usu_correo_electronico}</td>
                                <td class="px-6 py-4 whitespace-nowrap uppercase text-xs font-bold">${user.usu_rol}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center space-x-2">
                                    <button onclick="abrirModalVer('${userJSON}')" class="text-blue-600 hover:text-blue-900" title="Ver"><svg class="w-5 h-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg></button>
                                    
                                    <button onclick="abrirModalEditar('${userJSON}')" class="text-indigo-600 hover:text-indigo-900" title="Editar"><svg class="w-5 h-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg></button>
                                    
                                    <button onclick="abrirModalDesbloquear('${user.usu_id}', '${nombreCompleto}')" class="text-green-600 hover:text-green-900" title="Desbloquear"><svg class="w-5 h-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" /></svg></button>
                                    
                                    <button onclick="abrirModalEliminar('${user.usu_id}', '${nombreCompleto}')" class="text-red-600 hover:text-red-900" title="Eliminar"><svg class="w-5 h-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                </td>
                            </tr>
                        `;
                    });
                });
        }, 300); // 300ms de retraso para no saturar el servidor mientras escribe
    });

    // 2. Funciones de Modales
    const overlay = document.getElementById('overlay');
    
    function cerrarModales() {
        overlay.classList.add('hidden');
        document.querySelectorAll('[id^="modal-"]').forEach(m => m.classList.add('hidden'));
    }

    function abrirModalVer(userStr) {
        const user = JSON.parse(userStr);
        document.getElementById('ver-content').innerHTML = `
            <div><span class="font-bold">Documento:</span> ${user.usu_numero_documento}</div>
            <div><span class="font-bold">Estado:</span> <span class="uppercase">${user.usu_estado_cuenta}</span></div>
            <div><span class="font-bold">Primer Nombre:</span> ${user.usu_primer_nombre}</div>
            <div><span class="font-bold">Segundo Nombre:</span> ${user.usu_segundo_nombre || 'N/A'}</div>
            <div><span class="font-bold">Primer Apellido:</span> ${user.usu_primer_apellido}</div>
            <div><span class="font-bold">Segundo Apellido:</span> ${user.usu_segundo_apellido || 'N/A'}</div>
            <div><span class="font-bold">Correo:</span> ${user.usu_correo_electronico}</div>
            <div><span class="font-bold">Teléfono:</span> ${user.usu_numero_telefono || 'N/A'}</div>
            <div><span class="font-bold">Rol:</span> <span class="uppercase">${user.usu_rol}</span></div>
            <div><span class="font-bold">Creado el:</span> ${user.usu_fecha_registro ? new Date(user.usu_fecha_registro).toLocaleString() : 'N/A'}</div>
            <div class="col-span-2"><span class="font-bold">Último Acceso:</span> ${user.usu_ultimo_acceso ? new Date(user.usu_ultimo_acceso).toLocaleString() : 'Nunca'}</div>
        `;
        overlay.classList.remove('hidden');
        document.getElementById('modal-ver').classList.remove('hidden');
    }

    function abrirModalEditar(userStr) {
        const user = JSON.parse(userStr);
        
        // Asignar ruta de actualización
        document.getElementById('form-editar').action = `/admin/usuarios/${user.usu_id}`;
        
        // Poblar inputs
        document.getElementById('edit_primer_nombre').value = user.usu_primer_nombre;
        document.getElementById('edit_primer_apellido').value = user.usu_primer_apellido;
        document.getElementById('edit_correo').value = user.usu_correo_electronico;
        document.getElementById('edit_telefono').value = user.usu_numero_telefono || '';
        
        const selectRol = document.getElementById('edit_rol');
        selectRol.value = user.usu_rol;
        selectRol.dataset.rolOriginal = user.usu_rol; // Guardar estado inicial para la advertencia

        overlay.classList.remove('hidden');
        document.getElementById('modal-editar').classList.remove('hidden');
    }

    function abrirModalDesbloquear(id, nombre) {
        document.getElementById('form-desbloquear').action = `/admin/usuarios/${id}/desbloquear`;
        document.getElementById('unlock_nombre').innerText = nombre;
        overlay.classList.remove('hidden');
        document.getElementById('modal-desbloquear').classList.remove('hidden');
    }

    function abrirModalEliminar(id, nombre) {
        document.getElementById('form-eliminar').action = `/admin/usuarios/${id}`;
        document.getElementById('delete_nombre').innerText = nombre;
        overlay.classList.remove('hidden');
        document.getElementById('modal-eliminar').classList.remove('hidden');
    }

    // 3. Disparador de Advertencia para el Cambio de Rol
    document.getElementById('edit_rol').addEventListener('change', function() {
        if (this.value !== this.dataset.rolOriginal) {
            document.getElementById('modal-advertencia-rol').classList.remove('hidden');
        } else {
            document.getElementById('modal-advertencia-rol').classList.add('hidden');
        }
    });

</script>
@endsection