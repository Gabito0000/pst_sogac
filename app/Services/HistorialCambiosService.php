<?php

namespace App\Services;

use App\Models\HistorialCambio;
use App\Models\Usuario;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Consulta de la bitacora de cambios (AdminHistorialCambioController).
 *
 * La escritura es cosa de App\Services\RegistroCambios; aqui solo se lee.
 * El listado trae joins porque los filtros mas usados son por autor y por
 * entidad, y resolverlos con whereHas obligaria a cargar usuarios y estados
 * de la fila para despues no usarlos.
 */
class HistorialCambiosService
{
    /** Cambios por pagina. */
    public const PAGINATE = 20;

    /**
     * Filtros admitidos, ya saneados:
     *
     * @param  array{q: string, entidad: string, accion: string, autor: int|null, desde: string|null, hasta: string|null}  $filtros
     */
    public function consultar(array $filtros): Builder
    {
        $consulta = HistorialCambio::query()
            ->with('autor')
            // leftJoin y no join: los cambios sin autor (cargas de consola, datos
            // iniciales) se muestran igual que los demás, y con un join interior
            // desaparecían del listado sin dejar rastro.
            ->leftJoin('usuarios', 'usuarios.usu_id', '=', 'historial_cambios.hcm_usu_id')
            ->select('historial_cambios.*');

        $termino = trim($filtros['q'] ?? '');

        if ($termino !== '') {
            $patron = '%'.$termino.'%';
            $operador = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

            // El resumen y el contexto son las dos columnas por las que se
            // busca ("aprobo la 12", "pendiente", "1.2.3.4"), mientras que las
            // diferencias viven en JSON y no se pueden filtrar por motor sin
            // escribir SQL en crudo.
            $consulta->where(function ($query) use ($patron, $operador) {
                $query->where('historial_cambios.hcm_resumen', $operador, $patron)
                    ->orWhere('historial_cambios.hcm_ip', $operador, $patron)
                    ->orWhere('usuarios.usu_primer_nombre', $operador, $patron)
                    ->orWhere('usuarios.usu_primer_apellido', $operador, $patron)
                    ->orWhere('usuarios.usu_correo_electronico', $operador, $patron);
            });
        }

        if (($filtros['entidad'] ?? '') !== '') {
            $consulta->where('historial_cambios.hcm_entidad', $filtros['entidad']);
        }

        if (($filtros['accion'] ?? '') !== '') {
            $consulta->where('historial_cambios.hcm_accion', $filtros['accion']);
        }

        if (! empty($filtros['autor'])) {
            $consulta->where('historial_cambios.hcm_usu_id', $filtros['autor']);
        }

        if (! empty($filtros['desde'])) {
            $consulta->whereDate('historial_cambios.hcm_fecha', '>=', $filtros['desde']);
        }

        if (! empty($filtros['hasta'])) {
            $consulta->whereDate('historial_cambios.hcm_fecha', '<=', $filtros['hasta']);
        }

        return $consulta->orderByDesc('historial_cambios.hcm_fecha')
            ->orderByDesc('historial_cambios.hcm_id');
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    public function paginar(array $filtros): LengthAwarePaginator
    {
        return $this->consultar($filtros)->paginate(self::PAGINATE)->withQueryString();
    }

    /**
     * Recorre los cambios filtrados de uno en uno, para el CSV. Se limita el
     * numero de filas porque una exportacion sin techo puede tumbar el servidor
     * cuando la bitacora lleva meses recogiendo datos.
     *
     * @param  array<string, mixed>  $filtros
     * @return iterable<int, HistorialCambio>
     */
    public function paraExportar(array $filtros, int $limite = 5000): iterable
    {
        return $this->consultar($filtros)->limit($limite)->cursor();
    }

    /**
     * Numero de cambios por accion sobre todo el historico, para las tarjetas
     * de arriba del listado. No dependen del filtro: describen la bitacora
     * entera, igual que las cifras del panel de estadisticas.
     *
     * @return array{total: int, creo: int, actualizo: int, elimino: int}
     */
    public function resumen(): array
    {
        $conteo = HistorialCambio::query()
            ->selectRaw('hcm_accion as accion, count(*) as total')
            ->groupBy('hcm_accion')
            ->pluck('total', 'accion');

        return [
            'total' => (int) $conteo->sum(),
            'creo' => (int) ($conteo['creo'] ?? 0),
            'actualizo' => (int) ($conteo['actualizo'] ?? 0),
            'elimino' => (int) ($conteo['elimino'] ?? 0),
        ];
    }

    /**
     * Entidades que aparecen de verdad en la bitacora, para el desplegable.
     *
     * @return Collection<int, string>
     */
    public function entidadesRegistradas(): Collection
    {
        $registradas = HistorialCambio::query()
            ->distinct()
            ->orderBy('hcm_entidad')
            ->pluck('hcm_entidad');

        // Las entidades conocidas que aun no tienen movimientos se anaden al
        // final, para que el filtro no tenga que ir cambiando segun se use el
        // sistema.
        $conocidas = collect(array_keys(HistorialCambio::ENTIDADES))
            ->reject(fn (string $entidad) => $registradas->contains($entidad));

        return $registradas->concat($conocidas);
    }

    /**
     * Autores que han dejado alguna vez un cambio, para el desplegable.
     *
     * @return Collection<int, Usuario>
     */
    public function autoresRegistrados(): Collection
    {
        return Usuario::query()
            ->whereIn('usu_id', HistorialCambio::query()->distinct()->select('hcm_usu_id'))
            ->orderBy('usu_primer_nombre')
            ->orderBy('usu_primer_apellido')
            ->get();
    }

    /**
     * Sanea los parametros del listado. Una fecha con otro formato se descarta
     * en lugar de pasarsela a la base.
     *
     * @param  array<string, mixed>  $entrada
     * @return array{q: string, entidad: string, accion: string, autor: int|null, desde: string|null, hasta: string|null}
     */
    public static function sanearFiltros(array $entrada): array
    {
        $entidad = trim((string) ($entrada['entidad'] ?? ''));
        $accion = trim((string) ($entrada['accion'] ?? ''));

        return [
            'q' => mb_substr(trim((string) ($entrada['q'] ?? '')), 0, 100),
            'entidad' => array_key_exists($entidad, HistorialCambio::ENTIDADES) ? $entidad : '',
            'accion' => array_key_exists($accion, HistorialCambio::ACCIONES) ? $accion : '',
            'autor' => isset($entrada['autor']) && is_numeric($entrada['autor']) && (int) $entrada['autor'] > 0
                ? (int) $entrada['autor']
                : null,
            'desde' => HistorialSolicitudesService::sanearFecha($entrada['desde'] ?? null),
            'hasta' => HistorialSolicitudesService::sanearFecha($entrada['hasta'] ?? null),
        ];
    }

    /**
     * Comprueba que el rango de fechas no este al reves. Un filtro invertido no
     * da error, simplemente no devuelve nada, y eso desconcierta.
     *
     * @param  array<string, mixed>  $filtros
     */
    public static function rangoInvertido(array $filtros): bool
    {
        if (empty($filtros['desde']) || empty($filtros['hasta'])) {
            return false;
        }

        return Carbon::parse($filtros['desde'])->greaterThan(Carbon::parse($filtros['hasta']));
    }
}
