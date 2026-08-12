<?php

namespace Database\Factories;

use App\Models\Prisoner;
use App\Models\PropertyItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyItem>
 */
class PropertyItemFactory extends Factory
{
    protected static int $sequence = 1;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'prisoner_id' => Prisoner::factory(),
            'property_number' => sprintf('PB-%d-%04d', now()->year, static::$sequence++),
            'description' => fake()->randomElement(['Phone', 'Wallet', 'Watch', 'Shoes', 'Clothes']),
            'quantity' => 1,
            'storage_location' => fake()->randomElement(['Store A', 'Store B', 'Safe 1']),
            'received_by' => User::factory(),
            'received_at' => now(),
        ];
    }
}
