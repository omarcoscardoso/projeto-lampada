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
        $this->call(ShieldSeeder::class);

        $user = User::updateOrCreate(
            ['email' => 'cardoso.oliveira@gmail.com'],
            [
                'name' => 'Marcos Cardoso',
                'password' => bcrypt('password'), // Altere após o primeiro login ou use uma env
            ]
        );

        $user->assignRole('super_admin');
    }
}
