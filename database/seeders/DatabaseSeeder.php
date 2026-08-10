<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Prisoner;
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
        User::factory()->create([
            'name' => 'Ava Admin',
            'email' => 'admin@demo.com',
            'role' => Role::Admin,
        ]);

        User::factory()->create([
            'name' => 'Owen Officer',
            'email' => 'officer@demo.com',
            'role' => Role::Officer,
        ]);

        User::factory()->create([
            'name' => 'Sara Supervisor',
            'email' => 'supervisor@demo.com',
            'role' => Role::Supervisor,
        ]);

        Prisoner::factory(40)->create();

        $this->call(HousingSeeder::class);
    }
}
