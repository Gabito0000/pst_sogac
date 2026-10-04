<?php

namespace App\Services;

use App\Models\PreguntasFrecuentes;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PreguntasFrecuentesService
{
    /**
     * Pagina las preguntas frecuentes, opcionalmente filtradas por un término.
     *
     * La comparación sin distinguir mayúsculas usa ILIKE en PostgreSQL, que es
     * el motor de la aplicación, y LIKE en cualquier otro (SQLite en los
     * tests), porque ILIKE no existe fuera de Postgres.
     */
    public function paginar(?string $busqueda = null): LengthAwarePaginator
    {
        $consulta = PreguntasFrecuentes::query()->oldest();

        $termino = $busqueda !== null ? trim($busqueda) : '';

        if ($termino !== '') {
            // Sin escapar los comodines: el carácter de escape de LIKE no es el
            // mismo en todos los motores (Postgres usa la barra invertida y
            // SQLite no tiene ninguno por defecto), así que escaparlos obligaría
            // a escribir SQL en crudo por motor. El coste de no hacerlo es que
            // un % en la búsqueda ensancha algo el resultado.
            $patron = '%'.$termino.'%';
            $operador = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

            $consulta->where(function ($query) use ($patron, $operador) {
                $query->where('pregunta', $operador, $patron)
                    ->orWhere('respuesta', $operador, $patron);
            });
        }

        // withQueryString() mantiene el término de búsqueda al cambiar de página.
        return $consulta->paginate(PreguntasFrecuentes::PAGINATE)->withQueryString();
    }

    /**
     * @param  array{pregunta: string, respuesta: string}  $data
     */
    public function create(array $data): PreguntasFrecuentes
    {
        return PreguntasFrecuentes::create($data);
    }

    /**
     * @param  array{pregunta: string, respuesta: string}  $data
     */
    public function update(int $id, array $data): bool
    {
        return PreguntasFrecuentes::where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        return PreguntasFrecuentes::where('id', $id)->delete();
    }
}
