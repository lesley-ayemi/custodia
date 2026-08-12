<?php

use App\Enums\Role;
use App\Models\Block;
use App\Models\Cell;
use App\Models\Facility;
use App\Models\Incident;
use App\Models\Prisoner;
use App\Models\PropertyItem;
use App\Models\User;
use App\Models\Wing;
use Illuminate\Support\Facades\RateLimiter;

function hardeningCell(int $capacity): Cell
{
    $block = Block::create(['name' => 'Sec Block', 'facility_id' => Facility::first()->id]);
    $wing = Wing::create(['block_id' => $block->id, 'name' => 'Sec Wing']);

    return Cell::create(['wing_id' => $wing->id, 'code' => 'SEC-1', 'capacity' => $capacity]);
}

test('login is rate limited after repeated failures', function () {
    RateLimiter::clear('sec@demo.com|127.0.0.1');
    User::factory()->create(['email' => 'sec@demo.com', 'role' => Role::Officer]);

    foreach (range(1, 5) as $ignored) {
        $this->postJson('/api/login', ['email' => 'sec@demo.com', 'password' => 'wrong-password'])
            ->assertStatus(422);
    }

    // The sixth attempt should be blocked by the throttle, not merely rejected.
    $this->postJson('/api/login', ['email' => 'sec@demo.com', 'password' => 'wrong-password'])
        ->assertStatus(429);
});

test('a prisoner cannot be assigned to a cell that is already full', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $cell = hardeningCell(capacity: 1);

    $first = Prisoner::factory()->create();
    $second = Prisoner::factory()->create();

    $this->actingAs($officer)->postJson('/api/housing-assignments', [
        'prisoner_id' => $first->id,
        'cell_id' => $cell->id,
    ])->assertCreated();

    $this->actingAs($officer)->postJson('/api/housing-assignments', [
        'prisoner_id' => $second->id,
        'cell_id' => $cell->id,
    ])->assertUnprocessable();

    expect($cell->fresh()->occupancy())->toBe(1);
});

test('reassigning a prisoner to the cell they already occupy is still allowed', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $cell = hardeningCell(capacity: 1);
    $prisoner = Prisoner::factory()->create();

    $payload = ['prisoner_id' => $prisoner->id, 'cell_id' => $cell->id];

    $this->actingAs($officer)->postJson('/api/housing-assignments', $payload)->assertCreated();
    $this->actingAs($officer)->postJson('/api/housing-assignments', $payload)->assertCreated();

    expect($cell->fresh()->occupancy())->toBe(1);
});

test('an incident cannot be resolved without first being reviewed', function () {
    $supervisor = User::factory()->create(['role' => Role::Supervisor]);
    $incident = Incident::factory()->for(Prisoner::factory())->create([
        'officer_id' => User::factory()->create(['role' => Role::Officer])->id,
        'status' => 'reported',
    ]);

    $this->actingAs($supervisor)->postJson("/api/incidents/{$incident->id}/resolve")
        ->assertUnprocessable();

    expect($incident->fresh()->status->value)->toBe('reported');
});

test('an incident cannot be moved under review twice', function () {
    $supervisor = User::factory()->create(['role' => Role::Supervisor]);
    $incident = Incident::factory()->for(Prisoner::factory())->create([
        'officer_id' => User::factory()->create(['role' => Role::Officer])->id,
        'status' => 'reported',
    ]);

    $this->actingAs($supervisor)->postJson("/api/incidents/{$incident->id}/review")->assertOk();
    $this->actingAs($supervisor)->postJson("/api/incidents/{$incident->id}/review")->assertUnprocessable();
});

test('moving an incident under review is recorded in the audit log', function () {
    $supervisor = User::factory()->create(['role' => Role::Supervisor]);
    $incident = Incident::factory()->for(Prisoner::factory())->create([
        'officer_id' => User::factory()->create(['role' => Role::Officer])->id,
        'status' => 'reported',
    ]);

    $this->actingAs($supervisor)->postJson("/api/incidents/{$incident->id}/review")->assertOk();

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $supervisor->id,
        'action' => 'moved under review',
        'entity_type' => 'Incident',
        'entity_id' => $incident->id,
    ]);
});

test('the audit log records the real previous status when an incident is resolved', function () {
    $supervisor = User::factory()->create(['role' => Role::Supervisor]);
    $incident = Incident::factory()->for(Prisoner::factory())->create([
        'officer_id' => User::factory()->create(['role' => Role::Officer])->id,
        'status' => 'under_review',
    ]);

    $this->actingAs($supervisor)->postJson("/api/incidents/{$incident->id}/resolve")->assertOk();

    $log = DB::table('audit_logs')
        ->where('action', 'resolved')
        ->where('entity_id', $incident->id)
        ->latest('id')
        ->first();

    expect(json_decode($log->old_values, true))->toBe(['status' => 'under_review']);
});

test('a property item cannot be released twice', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $item = PropertyItem::factory()->for(Prisoner::factory())->create([
        'received_by' => $officer->id,
        'released_at' => null,
        'released_by' => null,
        'released_to' => null,
    ]);

    $this->actingAs($officer)->postJson("/api/property-items/{$item->id}/release", [
        'released_to' => 'First Claimant',
    ])->assertOk();

    $this->actingAs($officer)->postJson("/api/property-items/{$item->id}/release", [
        'released_to' => 'Second Claimant',
    ])->assertUnprocessable();

    expect($item->fresh()->released_to)->toBe('First Claimant');
});

test('an admin cannot demote themselves and lock everyone out', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);

    $this->actingAs($admin)->putJson("/api/users/{$admin->id}", [
        'role' => 'officer',
    ])->assertUnprocessable();

    expect($admin->fresh()->role)->toBe(Role::Admin);
});

test('a visitor cannot be registered with a date of birth in the future', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);

    $this->actingAs($officer)->postJson('/api/visitors', [
        'name' => 'Future Person',
        'date_of_birth' => now()->addYear()->toDateString(),
        'id_type' => 'passport',
        'id_number' => 'X1234567',
        'phone' => '08012345678',
    ])->assertUnprocessable();
});
