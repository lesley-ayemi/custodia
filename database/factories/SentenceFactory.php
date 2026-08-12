<?php

namespace Database\Factories;

use App\Enums\LegalStatus;
use App\Enums\SentenceType;
use App\Models\Prisoner;
use App\Models\Sentence;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sentence>
 */
class SentenceFactory extends Factory
{
    protected static int $sequence = 1;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-2 years', 'now');

        return [
            'prisoner_id' => Prisoner::factory(),
            'case_number' => sprintf('CASE-%d-%04d', now()->year, static::$sequence++),
            'court' => fake()->randomElement(['District Court 4', 'Crown Court', 'Magistrates Court']),
            'offence' => fake()->randomElement(['Assault', 'Theft', 'Fraud', 'Burglary']),
            'sentence_start' => $start,
            'sentence_end' => fake()->dateTimeBetween($start, '+5 years'),
            'sentence_type' => SentenceType::Custodial,
            'parole_eligibility_date' => fake()->dateTimeBetween($start, '+3 years'),
            'legal_status' => LegalStatus::Convicted,
        ];
    }
}
