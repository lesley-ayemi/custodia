<?php

use App\Enums\PrisonerStatus;
use App\Enums\Role;
use App\Models\Block;
use App\Models\Cell;
use App\Models\Facility;
use App\Models\Incident;
use App\Models\Prisoner;
use App\Models\User;
use App\Models\Wing;
use App\Services\HousingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Builds a block of identical cells and puts one prisoner in each of the first
 * $occupied of them.
 */
function estateBlock(string $name, int $cells, int $capacity, int $occupied = 0): Block
{
    $block = Block::create(['name' => $name, 'facility_id' => Facility::first()->id]);
    $wing = Wing::create(['block_id' => $block->id, 'name' => "{$name} Wing"]);
    $officer = User::factory()->create(['role' => Role::Officer]);

    foreach (range(1, $cells) as $index) {
        $cell = Cell::create(['wing_id' => $wing->id, 'code' => "{$name}-{$index}", 'capacity' => $capacity]);

        if ($index <= $occupied) {
            Prisoner::factory()->create(['status' => PrisonerStatus::InCustody])
                ->housingAssignments()
                ->create(['cell_id' => $cell->id, 'assigned_by' => $officer->id, 'started_at' => now()]);
        }
    }

    return $block;
}

/**
 * Counts the SQL statements a request runs.
 */
function queriesRunBy(Closure $request): int
{
    DB::flushQueryLog();
    DB::enableQueryLog();

    $request();

    $count = count(DB::getQueryLog());
    DB::disableQueryLog();

    return $count;
}

test('the dashboard reports population, occupancy and open incidents', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);

    // 8 beds in Block A with 3 taken, 4 empty beds in Block B: 12 beds, 3 used.
    estateBlock('Block A', cells: 4, capacity: 2, occupied: 3);
    estateBlock('Block B', cells: 2, capacity: 2);

    $released = Prisoner::factory()->create(['status' => PrisonerStatus::Released]);
    Incident::factory()->create(['prisoner_id' => $released->id]);

    $response = $this->actingAs($admin)->getJson('/api/dashboard')->assertOk();

    $response->assertJsonPath('total_prisoners', 4)
        ->assertJsonPath('in_custody_prisoners', 3)
        ->assertJsonPath('occupancy_percent', 25)
        ->assertJsonPath('available_beds', 9)
        ->assertJsonPath('open_incidents', 1)
        ->assertJsonPath('block_occupancy.0.name', 'Block A')
        ->assertJsonPath('block_occupancy.0.percent', 38)
        ->assertJsonPath('block_occupancy.1.name', 'Block B')
        ->assertJsonPath('block_occupancy.1.percent', 0);
});

test('an empty estate reports zero occupancy rather than dividing by zero', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);

    $this->actingAs($admin)->getJson('/api/dashboard')->assertOk()
        ->assertJsonPath('occupancy_percent', 0)
        ->assertJsonPath('available_beds', 0)
        ->assertJsonPath('block_occupancy', []);
});

test('a block with no cells yet does not break the occupancy breakdown', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    Block::create(['name' => 'Block A', 'facility_id' => Facility::first()->id]);

    $this->actingAs($admin)->getJson('/api/dashboard')->assertOk()
        ->assertJsonPath('block_occupancy.0.percent', 0);
});

test('an officer can see the dashboard', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);

    $this->actingAs($officer)->getJson('/api/dashboard')->assertOk();
});

test('the dashboard runs the same number of queries however big the estate is', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    Incident::factory()->create(['prisoner_id' => Prisoner::factory()]);

    estateBlock('Block A', cells: 2, capacity: 2, occupied: 1);
    $small = queriesRunBy(fn () => $this->actingAs($admin)->getJson('/api/dashboard')->assertOk());

    estateBlock('Block B', cells: 30, capacity: 2, occupied: 20);
    $large = queriesRunBy(fn () => $this->actingAs($admin)->getJson('/api/dashboard')->assertOk());

    // Counting occupancy cell by cell used to add a query per cell, so this
    // would have been $small + 30.
    expect($large)->toBe($small);
});

test('the housing list runs the same number of queries however big the estate is', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);

    estateBlock('Block A', cells: 2, capacity: 2, occupied: 1);
    $small = queriesRunBy(fn () => $this->actingAs($admin)->getJson('/api/blocks')->assertOk());

    estateBlock('Block B', cells: 30, capacity: 2, occupied: 20);
    $large = queriesRunBy(fn () => $this->actingAs($admin)->getJson('/api/blocks')->assertOk());

    // BlockResource asks each cell for both its occupancy and its free beds, so
    // this used to grow by two queries per cell.
    expect($large)->toBe($small);
});

test('a cell reports the same occupancy whether or not the count was eager loaded', function () {
    estateBlock('Block A', cells: 1, capacity: 4, occupied: 1);

    $eagerLoaded = Cell::withCount('activeAssignments')->firstOrFail();
    $plain = Cell::firstOrFail();

    expect($eagerLoaded->occupancy())->toBe(1)
        ->and($eagerLoaded->availableBeds())->toBe(3)
        ->and($plain->occupancy())->toBe(1)
        ->and($plain->availableBeds())->toBe(3);
});

test('the capacity guard re-reads occupancy instead of trusting an eager-loaded count', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    estateBlock('Block A', cells: 1, capacity: 1);

    // This cell carries active_assignments_count = 0 from here on, however full
    // it actually gets.
    $cell = Cell::withCount('activeAssignments')->firstOrFail();

    app(HousingService::class)->assign(Prisoner::factory()->create(), $cell, $officer);

    expect(fn () => app(HousingService::class)->assign(Prisoner::factory()->create(), $cell, $officer))
        ->toThrow(ValidationException::class);

    expect($cell->fresh()->occupancy())->toBe(1);
});
