<?php

namespace Database\Seeders;

use App\Models\TipoDocumento;
use App\Models\Usuario;
use App\Models\TipoSolicitud;
use App\Models\EstadoSolicitud;
use App\Models\LapsoAcademico;
use App\Models\Solicitud;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SolicitudesDePruebaSeeder extends Seeder
{
    /**
     * Este seeder crea datos de ejemplo para poder probar el dashboard admin:
     * varias solicitudes, con distintos estudiantes, tipos de trámite y estados.
     * Usamos firstOrCreate() en los catálogos para poder correrlo varias veces
     * sin que truene por datos duplicados (cédulas, correos, etc. son únicos).
     */
    public function run(): void
    {
        // 1. Tipo de documento (necesario para crear usuarios)
        $cedula = TipoDocumento::firstOrCreate(
            ['tdo_abreviatura' => 'V'],
            ['tdo_nombre_documento' => 'Cédula de Identidad']
        );

        // 2. Estados de solicitud
        $pendiente = EstadoSolicitud::firstOrCreate(['eso_nombre_estado' => 'pendiente']);
        $aprobada  = EstadoSolicitud::firstOrCreate(['eso_nombre_estado' => 'aprobada']);
        $rechazada = EstadoSolicitud::firstOrCreate(['eso_nombre_estado' => 'rechazada']);

        // 3. Tipos de solicitud (trámites que puede pedir un estudiante)
        $constancia = TipoSolicitud::firstOrCreate(
            ['tsi_nombre_tipo' => 'Constancia de Estudio'],
            ['tsi_estado_tipo' => 'activo', 'tsi_tiempo_estimado_dias' => 3]
        );
        $cambioCarrera = TipoSolicitud::firstOrCreate(
            ['tsi_nombre_tipo' => 'Cambio de Carrera'],
            ['tsi_estado_tipo' => 'activo', 'tsi_tiempo_estimado_dias' => 15]
        );
        $revisionNota = TipoSolicitud::firstOrCreate(
            ['tsi_nombre_tipo' => 'Revisión de Nota'],
            ['tsi_estado_tipo' => 'activo', 'tsi_tiempo_estimado_dias' => 7]
        );

        // 4. Lapso académico activo
        $lapso = LapsoAcademico::firstOrCreate(
            ['lac_id_lapso' => '2026-2'],
            [
                'lac_fecha_inicio' => '2026-07-01',
                'lac_fecha_cierre' => '2026-12-15',
                'lac_estado_lapso' => 'activo',
            ]
        );

        // 5. Estudiantes de prueba (cédulas distintas para poder probar el buscador)
        $estudiantes = [
            ['V-30123456', 'María',   'Pérez',   'maria.perez@ejemplo.com'],
            ['V-28456789', 'Carlos',  'Gómez',   'carlos.gomez@ejemplo.com'],
            ['V-27998877', 'Andreina','Rojas',   'andreina.rojas@ejemplo.com'],
            ['V-31234567', 'Luis',    'Fernández','luis.fernandez@ejemplo.com'],
        ];

        $usuarios = [];
        foreach ($estudiantes as [$documento, $nombre, $apellido, $correo]) {
            $usuarios[] = Usuario::firstOrCreate(
                ['usu_numero_documento' => $documento],
                [
                    'usu_rol' => 'estudiante',
                    'usu_tdo_id' => $cedula->tdo_id,
                    'usu_primer_nombre' => $nombre,
                    'usu_primer_apellido' => $apellido,
                    'usu_correo_electronico' => $correo,
                    'usu_contrasena_hash' => bcrypt('password'),
                    'usu_estado_cuenta' => 'activo',
                    'usu_fecha_registro' => now(),
                ]
            );
        }

        // 6. Solicitudes de prueba: combinamos estudiantes, tipos y estados distintos
        $solicitudesDePrueba = [
            ['usuarios' => 0, 'tipo' => $constancia,     'estado' => $pendiente, 'motivo' => 'Necesito constancia para trámite de beca.'],
            ['usuarios' => 1, 'tipo' => $cambioCarrera,  'estado' => $pendiente, 'motivo' => 'Deseo cambiarme de Informática a Administración.'],
            ['usuarios' => 2, 'tipo' => $revisionNota,   'estado' => $aprobada,  'motivo' => 'Solicito revisión de la nota de Matemática II.'],
            ['usuarios' => 3, 'tipo' => $constancia,     'estado' => $rechazada, 'motivo' => 'Constancia de notas certificadas.'],
            ['usuarios' => 0, 'tipo' => $revisionNota,   'estado' => $pendiente, 'motivo' => 'Revisión de nota de Programación I.'],
        ];

        foreach ($solicitudesDePrueba as $s) {
            Solicitud::firstOrCreate(
                ['sol_id_seguimiento' => 'SOL-' . strtoupper(Str::random(8))],
                [
                    'sol_usu_id' => $usuarios[$s['usuarios']]->usu_id,
                    'sol_tsi_id' => $s['tipo']->tsi_id,
                    'sol_lac_id' => $lapso->lac_id,
                    'sol_eso_id' => $s['estado']->eso_id,
                    'sol_motivo_detallado' => $s['motivo'],
                    'sol_prioridad' => 'normal',
                    'sol_fecha_creacion' => now(),
                    'sol_fecha_ultima_actualizacion' => now(),
                ]
            );
        }
    }
}
