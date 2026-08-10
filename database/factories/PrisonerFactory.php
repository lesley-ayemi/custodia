<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Enums\PrisonerStatus;
use App\Models\Prisoner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Prisoner>
 */
class PrisonerFactory extends Factory
{
    protected static int $sequence = 1;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = fake()->randomElement(Gender::cases());
        $admissionDate = fake()->dateTimeBetween('-2 years', 'now');

        return [
            'prisoner_number' => sprintf('INM-%d-%04d', now()->year, static::$sequence++),
            'first_name' => $gender === Gender::Male ? fake()->firstNameMale() : fake()->firstNameFemale(),
            'last_name' => fake()->lastName(),
            'date_of_birth' => fake()->dateTimeBetween('-65 years', '-18 years'),
            'gender' => $gender,
            'admission_date' => $admissionDate,
            'expected_release_date' => fake()->dateTimeBetween($admissionDate, '+5 years'),
            'status' => fake()->randomElement([
                PrisonerStatus::InCustody,
                PrisonerStatus::InCustody,
                PrisonerStatus::InCustody,
                PrisonerStatus::InCustody,
                PrisonerStatus::Released,
                PrisonerStatus::Transferred,
            ]),
        ];
    }
}
