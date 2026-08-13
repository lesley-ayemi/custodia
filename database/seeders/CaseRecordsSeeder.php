<?php

namespace Database\Seeders;

use App\Enums\PrisonerStatus;
use App\Enums\Role;
use App\Models\CourtCase;
use App\Models\CourtHearing;
use App\Models\LegalRepresentative;
use App\Models\Prisoner;
use App\Models\Programme;
use App\Models\ProgrammeEnrolment;
use App\Models\PropertyItem;
use App\Models\Sentence;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Gives the demo data enough depth to actually show the app working. Without
 * this a freshly seeded database renders every panel on a prisoner profile as
 * "nothing on file", which isn't representative of anything.
 */
class CaseRecordsSeeder extends Seeder
{
    public function run(): void
    {
        $officer = User::query()->where('role', Role::Officer)->firstOrFail();

        $representatives = LegalRepresentative::factory(4)->create();

        // firstOrCreate so re-running the seeder doesn't trip the unique name index.
        $programmes = collect(['Education', 'Counselling', 'Vocational Training', 'Life Skills'])
            ->map(fn (string $name) => Programme::query()->firstOrCreate(
                ['name' => $name],
                Programme::factory()->make(['name' => $name])->getAttributes(),
            ));

        $prisoners = Prisoner::query()
            ->where('status', PrisonerStatus::InCustody)
            ->take(18)
            ->get();

        foreach ($prisoners as $index => $prisoner) {
            // Court case, with a hearing on roughly half of them.
            $case = CourtCase::factory()->for($prisoner)->create([
                'legal_representative_id' => $representatives->random()->id,
            ]);

            if ($index % 2 === 0) {
                CourtHearing::factory()->for($case)->create([
                    'scheduled_at' => now()->addDays(random_int(3, 60)),
                ]);
            }

            // Most people in custody are serving something.
            if ($index % 4 !== 0) {
                Sentence::factory()->for($prisoner)->create([
                    'case_number' => $case->case_number,
                    'court' => $case->court_name,
                    'offence' => $case->charge,
                ]);
            }

            // A property bag, sharing one property number across its items.
            $bagNumber = sprintf('PB-%d-%04d', now()->year, $index + 1);
            foreach (range(1, random_int(1, 3)) as $ignored) {
                PropertyItem::factory()->for($prisoner)->create([
                    'property_number' => $bagNumber,
                    'received_by' => $officer->id,
                ]);
            }

            if ($index % 3 === 0) {
                ProgrammeEnrolment::factory()->for($prisoner)->create([
                    'programme_id' => $programmes->random()->id,
                    'enrolled_by' => $officer->id,
                ]);
            }
        }
    }
}
