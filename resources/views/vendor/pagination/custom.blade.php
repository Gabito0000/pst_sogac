{{-- Vista de paginación simple, usando las mismas clases .btn que ya existen en style_admin.css --}}
@if ($paginator->hasPages())
    <nav style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
        <span style="font-size: 0.85rem; color: var(--gray-700);">
            Mostrando {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} de {{ $paginator->total() }} solicitudes
        </span>

        <div style="display: flex; gap: 6px;">
            {{-- Botón "Anterior" --}}
            @if ($paginator->onFirstPage())
                <span class="btn btn--sm" style="background: var(--gray-200); color: var(--gray-400); cursor: not-allowed;">&laquo; Anterior</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="btn btn--sm pagina-link" style="background: var(--gray-200); color: var(--black);">&laquo; Anterior</a>
            @endif

            {{-- Números de página --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="btn btn--sm" style="background: none; color: var(--gray-400);">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="btn btn--sm" style="background: var(--red); color: white;">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="btn btn--sm pagina-link" style="background: var(--gray-200); color: var(--black);">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Botón "Siguiente" --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="btn btn--sm pagina-link" style="background: var(--gray-200); color: var(--black);">Siguiente &raquo;</a>
            @else
                <span class="btn btn--sm" style="background: var(--gray-200); color: var(--gray-400); cursor: not-allowed;">Siguiente &raquo;</span>
            @endif
        </div>
    </nav>
@endif
