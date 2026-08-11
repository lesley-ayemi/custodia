<?php

use App\Enums\Role;
use App\Models\Incident;
use App\Models\Prisoner;
use App\Models\User;

test('an officer can report an incident', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $prisoner = Prisoner::factory()->create();

    $response = $this->actingAs($officer)->postJson('/api/incidents', [
        'prisoner_id' => $prisoner->id,
        'type' => 'property_damage',
        'severity' => 'medium',
        'location' => 'Block A Yard',
        'description' => 'Prisoner damaged furniture during a dispute.',
        'occurred_at' => now()->toIso8601String(),
    ]);

    $response->assertCreated();
    $response->assertJsonPath('status', 'reported');
    $this->assertMatchesRegularExpression('/^INC-\d{4}-\d{4}$/', $response->json('incident_number'));
});

test('a supervisor cannot report an incident', function () {
    $supervisor = User::factory()->create(['role' => Role::Supervisor]);
    $prisoner = Prisoner::factory()->create();

    $response = $this->actingAs($supervisor)->postJson('/api/incidents', [
        'prisoner_id' => $prisoner->id,
        'type' => 'property_damage',
        'severity' => 'medium',
        'location' => 'Block A Yard',
        'description' => 'Prisoner damaged furniture during a dispute.',
        'occurred_at' => now()->toIso8601String(),
    ]);

    $response->assertForbidden();
});

test('an incident moves through the reported, under review, and resolved workflow', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $supervisor = User::factory()->create(['role' => Role::Supervisor]);
    $incident = Incident::factory()->for(Prisoner::factory())->create([
        'officer_id' => $officer->id,
        'status' => 'reported',
    ]);

    $this->actingAs($supervisor)->postJson("/api/incidents/{$incident->id}/review")
        ->assertOk()
        ->assertJsonPath('status', 'under_review');

    $this->actingAs($supervisor)->postJson("/api/incidents/{$incident->id}/resolve")
        ->assertOk()
        ->assertJsonPath('status', 'resolved')
        ->assertJsonPath('resolved_by', $supervisor->name);

    expect($incident->fresh()->resolved_at)->not->toBeNull();
});

test('an officer cannot review or resolve an incident', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $incident = Incident::factory()->for(Prisoner::factory())->create(['officer_id' => $officer->id]);

    $this->actingAs($officer)->postJson("/api/incidents/{$incident->id}/review")->assertForbidden();
    $this->actingAs($officer)->postJson("/api/incidents/{$incident->id}/resolve")->assertForbidden();
});

test('incidents can be filtered by status', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    Incident::factory()->for(Prisoner::factory())->create(['officer_id' => $officer->id, 'status' => 'reported']);
    Incident::factory()->for(Prisoner::factory())->create(['officer_id' => $officer->id, 'status' => 'resolved']);

    $response = $this->actingAs($officer)->getJson('/api/incidents?status=resolved');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
    $response->assertJsonPath('data.0.status', 'resolved');
});
