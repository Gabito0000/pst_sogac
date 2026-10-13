@extends('layouts.plantilla_admin')

@section('title', 'Chats de soporte')

@section('content')
    @include('partials.miga', ['miga' => [['texto' => 'Atención'], ['texto' => 'Chats de soporte']]])

    @include('partials.avisos')

    <div class="pagina-head">
        <div>
            <h1 class="pagina-head__titulo">Chats de soporte</h1>
            <p class="pagina-head__desc">
                Bandeja de consultas de los estudiantes. Reclama un ticket para que quede a tu nombre
                y propón el cierre cuando la duda quede resuelta.
            </p>
        </div>

        <div class="pagina-head__acciones">
            <a href="{{ route('admin.preguntas.index') }}" class="btn btn--dark">Preguntas frecuentes</a>
        </div>
    </div>

    {{-- Peticiones de escalado: aquí decide el administrador si la consulta
         sube de nivel. Van antes que la cola porque no son chats para
         reclamar, sino algo que hay que resolver. --}}
    @if ($solicitudesEscalado->isNotEmpty())
        <div class="card" style="margin-bottom: 22px; border-color: var(--red);">
            <div class="card__head">
                <div>
                    <h2 class="card__title">
                        Solicitan un administrador
                        <span class="chip chip--warn">{{ $solicitudesEscalado->count() }}</span>
                    </h2>
                    <p class="card__sub">
                        Un estudiante pidió que atienda su consulta alguien de más arriba.
                        @if (auth()->user()->esAdministrador())
                            Apruebas para que suba de nivel, o rechazas si ya la estás resolviendo.
                        @else
                            Queda a decisión del administrador si se aprueba o se rechaza.
                        @endif
                    </p>
                </div>
            </div>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 90px;">#</th>
                            <th>Estudiante</th>
                            <th style="width: 150px;">Nivel actual</th>
                            <th style="width: 150px;">Abierta</th>
                            <th class="table__acciones" style="width: 300px;">Decisión</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($solicitudesEscalado as $hilo)
                            <tr>
                                <td style="color: var(--gray-400);">#{{ $hilo->hch_id }}</td>

                                <td>
                                    <strong>{{ $hilo->usuario->usu_primer_nombre ?? 'Usuario' }} {{ $hilo->usuario->usu_primer_apellido ?? '' }}</strong>
                                    <div style="font-size: 0.85rem; color: var(--gray-700);">
                                        {{ $hilo->usuario->usu_correo_electronico ?? '' }}
                                    </div>
                                </td>

                                <td style="font-size: 0.85rem;">{{ $hilo->nivel_etiqueta }}</td>

                                <td style="color: var(--gray-700); font-size: 0.85rem; white-space: nowrap;">
                                    {{ $hilo->created_at->format('d/m/Y') }}
                                </td>

                                <td class="table__acciones">
                                    <a href="{{ route('admin.chat.mostrar', $hilo->hch_id) }}" class="btn btn--ghost btn--sm">
                                        Ver chat
                                    </a>

                                    {{-- Solo el administrador aprueba o rechaza: el
                                         analista ve la peticion para estar al tanto,
                                         pero la decision es de arriba. --}}
                                    @if (auth()->user()->esAdministrador())
                                        <form method="POST" action="{{ route('admin.chat.aprobar-escalado', $hilo->hch_id) }}" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn--primary btn--sm">Aprobar</button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.chat.rechazar-escalado', $hilo->hch_id) }}" style="display: inline;">
                                            @csrf
                                            <input type="hidden" name="motivo" value="La consulta se puede resolver en el nivel actual.">
                                            <button type="submit" class="btn btn--ghost btn--sm">Rechazar</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Sin reclamar: la cola de trabajo del equipo. --}}
    <div class="card" style="margin-bottom: 22px;">
        <div class="card__head">
            <div>
                <h2 class="card__title">
                    Sin reclamar
                    @if ($hilosPendientes->isNotEmpty())
                        <span class="chip chip--warn">{{ $hilosPendientes->count() }}</span>
                    @endif
                </h2>
                <p class="card__sub">
                    La primera consulta que reclames queda asignada a tu nombre y deja de aparecer aquí
                    para los demás administradores.
                </p>
            </div>
        </div>

        @if ($hilosPendientes->isEmpty())
            <p class="estado-vacio" style="margin: 0;">
                No hay consultas esperando. La bandeja está al día.
            </p>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 90px;">#</th>
                            <th>Estudiante</th>
                            <th style="width: 160px;">Abierta</th>
                            <th style="width: 130px;">Estado</th>
                            <th class="table__acciones" style="width: 190px;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($hilosPendientes as $hilo)
                            <tr>
                                <td style="color: var(--gray-400);">#{{ $hilo->hch_id }}</td>

                                <td>
                                    <strong>{{ $hilo->usuario->usu_primer_nombre ?? 'Usuario' }} {{ $hilo->usuario->usu_primer_apellido ?? '' }}</strong>
                                    <div style="font-size: 0.85rem; color: var(--gray-700);">
                                        {{ $hilo->usuario->usu_correo_electronico ?? '' }}
                                    </div>
                                </td>

                                <td style="color: var(--gray-700); font-size: 0.85rem; white-space: nowrap;">
                                    {{ $hilo->created_at->format('d/m/Y H:i') }}
                                    <div style="color: var(--gray-400);">{{ $hilo->created_at->diffForHumans() }}</div>
                                </td>

                                <td>
                                    <span class="chip {{ $hilo->estado_chip }}">{{ $hilo->estado_etiqueta }}</span>
                                    <div style="font-size: 0.8rem; color: var(--gray-400); margin-top: 4px;">
                                        Nivel: {{ $hilo->nivel_etiqueta }}
                                    </div>
                                </td>

                                <td class="table__acciones">
                                    <form action="{{ route('admin.chat.reclamar', $hilo->hch_id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn--primary btn--sm">Reclamar y atender</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Chats que ya tiene este administrador. --}}
    <div class="card" style="margin-bottom: 22px;">
        <div class="card__head">
            <div>
                <h2 class="card__title">
                    Chats que estás atendiendo
                    @if ($hilosActivosAdmin->isNotEmpty())
                        <span class="chip chip--ok">{{ $hilosActivosAdmin->count() }}</span>
                    @endif
                </h2>
                <p class="card__sub">Solo los tickets que reclamaste tú.</p>
            </div>
        </div>

        @if ($hilosActivosAdmin->isEmpty())
            <p class="estado-vacio" style="margin: 0;">
                No tienes ningún chat asignado.
            </p>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 90px;">#</th>
                            <th>Estudiante</th>
                            <th style="width: 190px;">Estado</th>
                            <th style="width: 160px;">Última actividad</th>
                            <th class="table__acciones" style="width: 150px;">Conversación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($hilosActivosAdmin as $hilo)
                            <tr>
                                <td style="color: var(--gray-400);">#{{ $hilo->hch_id }}</td>

                                <td>
                                    <strong>{{ $hilo->usuario->usu_primer_nombre ?? 'Usuario' }} {{ $hilo->usuario->usu_primer_apellido ?? '' }}</strong>
                                </td>

                                <td>
                                    <span class="chip {{ $hilo->estado_chip }}">{{ $hilo->estado_etiqueta }}</span>
                                </td>

                                <td style="color: var(--gray-700); font-size: 0.85rem; white-space: nowrap;">
                                    {{ $hilo->updated_at->format('d/m/Y H:i') }}
                                </td>

                                <td class="table__acciones">
                                    <a href="{{ route('admin.chat.mostrar', $hilo->hch_id) }}"
                                       class="btn btn--dark btn--sm">Continuar</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Historial --}}
    <div class="card">
        <div class="card__head">
            <div>
                <h2 class="card__title">Consultas cerradas ({{ $hilos->total() }})</h2>
                <p class="card__sub">Historial completo de la bandeja, de cualquier administrador.</p>
            </div>
        </div>

        @if ($hilos->isEmpty())
            <p class="estado-vacio" style="margin: 0;">Todavía no hay consultas cerradas.</p>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 90px;">#</th>
                            <th style="width: 220px;">Tema</th>
                            <th>Estudiante</th>
                            <th style="width: 170px;">Atendida por</th>
                            <th style="width: 130px;">Cerrada</th>
                            <th class="table__acciones" style="width: 120px;">Conversación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($hilos as $hilo)
                            <tr>
                                <td style="color: var(--gray-400);">#{{ $hilo->hch_id }}</td>

                                <td>
                                    @if ($hilo->hch_etiqueta_tema)
                                        <span class="chip chip--neutro">{{ $hilo->hch_etiqueta_tema }}</span>
                                    @else
                                        <span style="color: var(--gray-400);">Sin etiqueta</span>
                                    @endif
                                </td>

                                <td>{{ $hilo->usuario->usu_primer_nombre ?? 'Usuario' }} {{ $hilo->usuario->usu_primer_apellido ?? '' }}</td>

                                <td>{{ $hilo->admin->usu_primer_nombre ?? 'Sistema' }}</td>

                                <td style="color: var(--gray-700); font-size: 0.85rem; white-space: nowrap;">
                                    {{ $hilo->updated_at->format('d/m/Y') }}
                                </td>

                                <td class="table__acciones">
                                    <a href="{{ route('admin.chat.mostrar', $hilo->hch_id) }}"
                                       class="btn btn--dark btn--sm">Ver</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 18px;">
                {{ $hilos->links('vendor.pagination.custom', ['etiqueta' => 'consultas']) }}
            </div>
        @endif
    </div>
@endsection
