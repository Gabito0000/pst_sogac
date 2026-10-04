<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call(SolicitudesDePruebaSeeder::class);
        $this->call(SolicitudesPanelEstadisticasSeeder::class);
        $this->call(PreguntasFrecuentesDemoSeeder::class);

        // Va al final porque reconstruye la bitacora a partir de lo que dejaron
        // los seeders anteriores: necesita solicitudes con historial y catálogo
        // ya cargados.
        $this->call(HistorialCambiosDemoSeeder::class);
    }
}
