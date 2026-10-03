@extends('layouts.plantilla_user')

@section('content')
<main class="main">
    <div class="container">

        <div class="card" style="margin-bottom: 32px; padding: 24px;">
            <h2 class="card__title" style="font-size: 1.25rem; margin-bottom: 4px;">Realizar Nuevos Trámites</h2>
            <p class="card__sub" style="color: var(--gray-700); font-size: 0.95rem; margin-bottom: 20px;">Selecciona el trámite de control de estudio que deseas iniciar.</p>

            @if($tramites->isEmpty())
                <p style="color: var(--gray-700);">No hay trámites disponibles por ahora. Vuelve a revisar más adelante.</p>
            @else
                <div class="table-wrap" style="overflow-x: auto;">
                    <table class="table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--gray-200); text-align: left;">
                                <th style="padding: 12px; font-size: 0.85rem; text-transform: uppercase; color: var(--gray-900);">Trámite</th>
                                <th style="padding: 12px; font-size: 0.85rem; text-transform: uppercase; color: var(--gray-900);">Disponible hasta</th>
                                <th style="padding: 12px; font-size: 0.85rem; text-transform: uppercase; color: var(--gray-900); text-align:right;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tramites as $tramite)
                                <tr style="border-bottom: 1px solid var(--gray-200);">
                                    <td style="padding: 14px 12px;">
                                        <strong style="color: var(--black);">{{ $tramite->tsi_nombre_tipo }}</strong>
                                        @if($tramite->tsi_descripcion)
                                            <div style="color: var(--gray-700); font-size: 0.85rem; margin-top: 2px;">{{ $tramite->tsi_descripcion }}</div>
                                        @endif
                                    </td>
                                    <td style="padding: 14px 12px; color: var(--gray-700); font-size: 0.9rem;">
                                        {{ $tramite->tsi_fecha_fin?->format('d/m/Y') ?? 'Sin fecha límite' }}
                                    </td>
                                    <td style="padding: 14px 12px; text-align:right; position: relative; z-index: 2;">
                                        <a href="{{ route('user.tramites.solicitar', $tramite->tsi_id) }}" class="btn btn--primary btn--sm" style="display: inline-block;">
                                            Solicitar
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>
</main>
@endsection