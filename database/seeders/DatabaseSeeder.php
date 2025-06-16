<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
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

        $this->call([
            UserSeeder::class,
            ClienteSeeder::class,
            ContactoSeeder::class,
            VisitaSeeder::class,
        ]);

        $this->command->info('¡Datos de prueba creados exitosamente!');
        $this->command->info('Usuarios de prueba:');
        $this->command->info('- admin@crm.local (password123)');
        $this->command->info('- juan.perez@crm.local (password123)');
        $this->command->info('- maria.rodriguez@crm.local (password123)');
        $this->command->info('- carlos.mendez@crm.local (password123)');
    }
}
