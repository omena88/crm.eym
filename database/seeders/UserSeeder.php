<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuario Vendedor
        User::factory()->create([
            'name' => 'Vendedor Demo',
            'email' => 'vendedor@demo.com',
            'password' => Hash::make('password'),
            'rol' => 'vendedor',
        ]);

        // Usuario Gerente
        User::factory()->create([
            'name' => 'Gerente Demo',
            'email' => 'gerente@demo.com',
            'password' => Hash::make('password'),
            'rol' => 'gerente',
        ]);
    }
}
