<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Prisoner;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // updateOrCreate keeps these demo accounts idempotent — db:seed can be
        // re-run safely instead of failing on a duplicate email.
        User::query()->updateOrCreate(
            ['email' => 'admin@demo.com'],
            ['name' => 'Ava Admin', 'password' => Hash::make('password'), 'role' => Role::Admin],
        );

        User::query()->updateOrCreate(
            ['email' => 'officer@demo.com'],
            ['name' => 'Owen Officer', 'password' => Hash::make('password'), 'role' => Role::Officer],
        );

        User::query()->updateOrCreate(
            ['email' => 'supervisor@demo.com'],
            ['name' => 'Sara Supervisor', 'password' => Hash::make('password'), 'role' => Role::Supervisor],
        );

        User::query()->updateOrCreate(
            ['email' => 'medical@demo.com'],
            ['name' => 'Mira Medical', 'password' => Hash::make('password'), 'role' => Role::Medical],
        );

        if (Prisoner::query()->count() === 0) {
            Prisoner::factory(40)->create();

            $this->call(HousingSeeder::class);
            $this->call(IncidentSeeder::class);
        }
    }
}
