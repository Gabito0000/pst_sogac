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
        // Se actualiza sobre la instancia y no con PreguntasFrecuentes::where(...)->update()
        // porque las consultas de masses no disparan los eventos del modelo, y el
        // trait que escribe la bitácora de cambios está escuchando a 'updated':
        // con la consulta en crudo las ediciones de las preguntas quedaban sin
        // registrar.
        return PreguntasFrecuentes::where('id', $id)->firstOrFail()->update($data);
    }

    /**
     * @param  array{pregunta: string, respuesta: string}  $data
     */
    public function delete(int $id): bool
    {
        // Mismo motivo que en update(): el evento 'deleted' solo se dispara al
        // borrar la instancia.
        return PreguntasFrecuentes::where('id', $id)->firstOrFail()->delete();
    }
}
