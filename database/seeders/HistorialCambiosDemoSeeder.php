<?php

namespace Database\Seeders;

use App\Models\HistorialCambio;
use App\Models\PreguntasFrecuentes;
use App\Models\Requisito;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Datos de prueba para la bitacora de cambios (app/Services/RegistroCambios.php).
 *
 * El resto de seeders fabrican solicitudes y sus historiales de estado, pero
 * como DatabaseSeeder usa WithoutModelEvents, el trait que escribe la bitacora
 * no se dispara y la pantalla de historial de cambios salia vacia. Este seeder
 * reconstruye el rastro a partir de lo que ya hay en la base: por cada
 * solicitud, el alta y cada paso de su historial de estados, más un puñado de
 * cambios de catálogo (trámites, requisitos y preguntas frecuentes) para que los
 * filtros por entidad tengan algo que filtrar.
 *
 * Es idempotente, pero a diferencia de los otros NO es una carga que se pueda
 * repetir sobre datos reales: borra todo el contenido de historial_cambios,
 * porque un registro de cambios sin fecha ni orden no se puede reconciliar.
 *
 *   php artisan db:seed --class=HistorialCambiosDemoSeeder
 */
class HistorialCambiosDemoSeeder extends Seeder
{
    /**
     * Cambios de catálogo repartidos por los últimos días.
     *
     * El campo va con el nombre legible que escribe RegistroCambios al registrar
     * un cambio de verdad ("Descripción", no "req_descripcion"): si la pantalla
     * muestra etiquetas, los datos de demostración tienen que hablar el mismo
     * idioma que los reales.
     */
    private const CAMBIOS_DE_CATALOGO = [
        ['entidad' => 'TipoSolicitud', 'resumen' => 'Actualizó el nombre', 'campo' => 'Nombre', 'anterior' => 'Constancia', 'nuevo' => 'Constancia de estudio'],
        ['entidad' => 'TipoSolicitud', 'resumen' => 'Actualizó el tiempo estimado', 'campo' => 'Tiempo estimado (días)', 'anterior' => '10', 'nuevo' => '7'],
        ['entidad' => 'Requisito', 'resumen' => 'Actualizó la descripción', 'campo' => 'Descripción', 'anterior' => 'Cédula de identidad.', 'nuevo' => 'Cédula de identidad vigente, con sello húmedo.'],
        ['entidad' => 'PreguntasFrecuentes', 'resumen' => 'Actualizó la respuesta', 'campo' => 'Respuesta', 'anterior' => 'Puedes pedirla en línea.', 'nuevo' => 'Puedes pedirla en línea desde la sección de trámites.'],
        ['entidad' => 'PreguntasFrecuentes', 'resumen' => 'Actualizó la pregunta', 'campo' => 'Pregunta', 'anterior' => '¿Pido constancias?', 'nuevo' => '¿Cómo pido una constancia de estudio?'],
    ];

    public function run(): void
    {
        $this->command?->info('Limpiando el historial de cambios anterior...');
        HistorialCambio::query()->delete();

        $solicitudes = Solicitud::with(['tipoSolicitud', 'estadoActual'])->get();

        if ($solicitudes->isEmpty()) {
            $this->command?->warn('No hay solicitudes: ejecuta primero SolicitudesPanelEstadisticasSeeder.');

            return;
        }

        $registradas = $this->registrarSolicitudes($solicitudes);
        $registradas += $this->registrarCatalogo();

        $this->command?->info(sprintf('%d cambios registrados en la bitácora.', $registradas));
    }

    /**
     * Un alta por solicitud (con el estudiante como autor) y un cambio por cada
     * paso de su historial de estados (con el administrador responsable).
     *
     * @param  Collection<int, Solicitud>  $solicitudes
     */
    private function registrarSolicitudes($solicitudes): int
    {
        $escritas = 0;

        foreach ($solicitudes as $solicitud) {
            $trámite = $solicitud->tipoSolicitud?->tsi_nombre_tipo ?? 'Trámite eliminado';

            HistorialCambio::create([
                'hcm_usu_id' => $solicitud->sol_usu_id,
                'hcm_entidad' => 'Solicitud',
                'hcm_entidad_id' => $solicitud->sol_id,
                'hcm_sol_id' => $solicitud->sol_id,
                'hcm_accion' => 'creo',
                'hcm_resumen' => 'Alta de Solicitud #'.$solicitud->sol_id,
                'hcm_cambios' => [
                    ['campo' => 'Trámite', 'anterior' => '—', 'nuevo' => $trámite],
                    ['campo' => 'Estado', 'anterior' => '—', 'nuevo' => $solicitud->estadoActual?->eso_nombre_estado ?? '—'],
                    ['campo' => 'Código de seguimiento', 'anterior' => '—', 'nuevo' => $solicitud->sol_id_seguimiento],
                    ['campo' => 'Prioridad', 'anterior' => '—', 'nuevo' => $solicitud->sol_prioridad ?? 'sin prioridad'],
                ],
                'hcm_ruta' => '/user/tramites/'.$solicitud->sol_tsi_id.'/solicitar',
                'hcm_ip' => '190.85.'.(100 + ($solicitud->sol_id % 90)).'.10',
                'hcm_fecha' => Carbon::parse($solicitud->sol_fecha_creacion),
            ]);
            $escritas++;

            $escritas += $this->registrarPasos($solicitud);
        }

        return $escritas;
    }

    /**
     * Los cambios de estado de la solicitud, tomados del historial real.
     */
    private function registrarPasos(Solicitud $solicitud): int
    {
        $pasos = $solicitud->historialEstados()
            ->with(['estadoAnterior', 'estadoNuevo', 'responsable'])
            ->orderBy('hes_fecha_cambio')
            ->get();

        $escritas = 0;

        foreach ($pasos as $paso) {
            $anterior = $paso->estadoAnterior?->eso_nombre_estado;
            $nuevo = $paso->estadoNuevo?->eso_nombre_estado ?? 'desconocido';

            // La primera fila del historial es el alta del trámite en el estado en
            // que ya está, no un movimiento de verdad: repetirla solo duplicaría
            // el alta que ya se registró arriba. Tampoco se anotan los pasos que
            // van de un estado al mismo, porque no son cambios.
            if ($anterior === null || $anterior === $nuevo) {
                continue;
            }

            $campo = 'Estado';

            HistorialCambio::create([
                'hcm_usu_id' => $paso->hes_usu_id_responsable,
                'hcm_entidad' => 'Solicitud',
                'hcm_entidad_id' => $solicitud->sol_id,
                'hcm_sol_id' => $solicitud->sol_id,
                'hcm_accion' => 'actualizo',
                'hcm_resumen' => 'Actualizó '.$campo,
                'hcm_cambios' => [
                    ['campo' => $campo, 'anterior' => $anterior, 'nuevo' => $nuevo],
                ],
                'hcm_ruta' => '/admin/dashboard',
                'hcm_ip' => '10.20.0.'.(10 + ($paso->hes_id % 40)),
                'hcm_fecha' => Carbon::parse($paso->hes_fecha_cambio),
            ]);
            $escritas++;
        }

        return $escritas;
    }

    /**
     * Cambios de catalogo, repartidos por las ultimas dos semanas: cada
     * edicion tiene su dia y su hora, para que el filtro por rango se pueda
     * probar de verdad.
     */
    private function registrarCatalogo(): int
    {
        $escritas = 0;

        foreach (self::CAMBIOS_DE_CATALOGO as $indice => $cambio) {
            $esBaja = $cambio['entidad'] === 'Requisito';
            $dias = count(self::CAMBIOS_DE_CATALOGO) - $indice + ($esBaja ? 20 : 0);

            HistorialCambio::create([
                'hcm_usu_id' => null,
                'hcm_entidad' => $cambio['entidad'],
                'hcm_entidad_id' => $this->primerRegistroDe($cambio['entidad']),
                'hcm_sol_id' => null,
                'hcm_accion' => $esBaja ? 'elimino' : 'actualizo',
                'hcm_resumen' => $cambio['resumen'],
                'hcm_cambios' => [[
                    'campo' => $cambio['campo'],
                    'anterior' => $cambio['anterior'],
                    'nuevo' => $esBaja ? '—' : $cambio['nuevo'],
                ]],
                'hcm_ruta' => $this->rutaDe($cambio['entidad']),
                'hcm_ip' => '10.20.0.5',
                'hcm_fecha' => now()->subDays($dias)->setTime(9 + ($indice % 8), 15),
            ]);
            $escritas++;
        }

        return $escritas;
    }

    /**
     * Primer registro de una entidad del catalogo, para que el cambio apunte a
     * algo que exista de verdad y no a un identificador inventado.
     */
    private function primerRegistroDe(string $entidad): ?int
    {
        return match ($entidad) {
            'TipoSolicitud' => TipoSolicitud::query()->value('tsi_id'),
            'Requisito' => Requisito::query()->value('req_id'),
            default => PreguntasFrecuentes::query()->value('id'),
        };
    }

    /**
     * Pantalla desde la que "se hizo" el cambio de catalogo.
     */
    private function rutaDe(string $entidad): string
    {
        return match ($entidad) {
            'TipoSolicitud' => '/admin/tipos-solicitud',
            'Requisito' => '/admin/requisitos',
            default => '/admin/preguntas',
        };
    }
}
