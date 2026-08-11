<?php

use App\Enums\Role;
use App\Models\CourtCase;
use App\Models\CourtHearing;
use App\Models\Prisoner;
use App\Models\User;

test('an officer can open a court case for a prisoner', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $prisoner = Prisoner::factory()->create();

    $response = $this->actingAs($officer)->postJson("/api/prisoners/{$prisoner->id}/court-cases", [
        'court_name' => 'District Court 4',
        'charge' => 'Assault',
        'opened_at' => now()->toDateString(),
    ]);

    $response->assertCreated();
    $response->assertJsonPath('status', 'open');
    $this->assertMatchesRegularExpression('/^CASE-\d{4}-\d{4}$/', $response->json('case_number'));
});

test('a supervisor cannot open a court case', function () {
    $supervisor = User::factory()->create(['role' => Role::Supervisor]);
    $prisoner = Prisoner::factory()->create();

    $response = $this->actingAs($supervisor)->postJson("/api/prisoners/{$prisoner->id}/court-cases", [
        'court_name' => 'District Court 4',
        'charge' => 'Assault',
        'opened_at' => now()->toDateString(),
    ]);

    $response->assertForbidden();
});

test('an admin can open a court case', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $prisoner = Prisoner::factory()->create();

    $response = $this->actingAs($admin)->postJson("/api/prisoners/{$prisoner->id}/court-cases", [
        'court_name' => 'District Court 4',
        'charge' => 'Assault',
        'opened_at' => now()->toDateString(),
    ]);

    $response->assertCreated();
});

test('all three roles can view a prisoners court cases', function () {
    $prisoner = Prisoner::factory()->create();
    CourtCase::factory()->for($prisoner)->create();

    foreach ([Role::Admin, Role::Officer, Role::Supervisor] as $role) {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)->getJson("/api/prisoners/{$prisoner->id}/court-cases")->assertOk();
    }
});

test('an officer can schedule a hearing for a court case', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $case = CourtCase::factory()->create();

    $response = $this->actingAs($officer)->postJson("/api/court-cases/{$case->id}/hearings", [
        'type' => 'arraignment',
        'scheduled_at' => now()->addWeek()->toIso8601String(),
        'location' => 'Courtroom 3',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('status', 'scheduled');
    $response->assertJsonPath('type', 'arraignment');
    $this->assertDatabaseHas('court_hearings', ['court_case_id' => $case->id, 'location' => 'Courtroom 3']);
});

test('a supervisor cannot schedule a hearing', function () {
    $supervisor = User::factory()->create(['role' => Role::Supervisor]);
    $case = CourtCase::factory()->create();

    $response = $this->actingAs($supervisor)->postJson("/api/court-cases/{$case->id}/hearings", [
        'type' => 'arraignment',
        'scheduled_at' => now()->addWeek()->toIso8601String(),
        'location' => 'Courtroom 3',
    ]);

    $response->assertForbidden();
});

test('upcoming hearings only returns scheduled hearings in the future', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);

    $future = CourtHearing::factory()->for(CourtCase::factory())->create([
        'scheduled_at' => now()->addWeek(),
        'status' => 'scheduled',
    ]);
    CourtHearing::factory()->for(CourtCase::factory())->create([
        'scheduled_at' => now()->subWeek(),
        'status' => 'scheduled',
    ]);
    CourtHearing::factory()->for(CourtCase::factory())->create([
        'scheduled_at' => now()->addWeek(),
        'status' => 'cancelled',
    ]);

    $response = $this->actingAs($officer)->getJson('/api/court-hearings/upcoming');

    $response->assertOk();
    $ids = collect($response->json())->pluck('id');
    expect($ids)->toHaveCount(1);
    expect($ids->first())->toBe($future->id);
});

test('guests cannot access court management endpoints', function () {
    $this->getJson('/api/court-hearings/upcoming')->assertUnauthorized();
    $this->getJson('/api/legal-representatives')->assertUnauthorized();
});
