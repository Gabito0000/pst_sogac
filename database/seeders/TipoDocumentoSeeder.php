<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoDocumentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documentos = [
            [
                'tdo_nombre_documento' => 'Extranjero', 
                'tdo_abreviatura' => 'E'
            ],
            [
                'tdo_nombre_documento' => 'Jurídico', 
                'tdo_abreviatura' => 'J'
            ],
            [
                'tdo_nombre_documento' => 'Gubernamental / Gobierno', 
                'tdo_abreviatura' => 'G'
            ],
            [
                'tdo_nombre_documento' => 'Comunas / Consejos Comunales', 
                'tdo_abreviatura' => 'C'
            ],
        ];

        // Insertar los registros en la base de datos
        DB::table('tipo_documentos')->insert($documentos);
    }
}