<?php

use App\Enums\Role;
use App\Models\Movement;
use App\Models\Prisoner;
use App\Models\User;

test('an officer can request a movement', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $prisoner = Prisoner::factory()->create();

    $response = $this->actingAs($officer)->postJson("/api/prisoners/{$prisoner->id}/movements", [
        'from_location' => 'HMP Custodia',
        'to_location' => 'Crown Court',
        'reason' => 'Court appearance',
        'scheduled_at' => now()->addDay()->toIso8601String(),
    ]);

    $response->assertCreated();
    $response->assertJsonPath('status', 'requested');
});

test('a supervisor cannot request a movement', function () {
    $supervisor = User::factory()->create(['role' => Role::Supervisor]);
    $prisoner = Prisoner::factory()->create();

    $response = $this->actingAs($supervisor)->postJson("/api/prisoners/{$prisoner->id}/movements", [
        'from_location' => 'HMP Custodia',
        'to_location' => 'Crown Court',
        'reason' => 'Court appearance',
        'scheduled_at' => now()->addDay()->toIso8601String(),
    ]);

    $response->assertForbidden();
});

test('all three roles can view a prisoners movements', function () {
    $prisoner = Prisoner::factory()->create();
    Movement::factory()->for($prisoner)->create();

    foreach ([Role::Admin, Role::Officer, Role::Supervisor] as $role) {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)->getJson("/api/prisoners/{$prisoner->id}/movements")->assertOk();
    }
});

test('an officer cannot approve a movement', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $movement = Movement::factory()->create();

    $this->actingAs($officer)->postJson("/api/movements/{$movement->id}/approve")->assertForbidden();
});

test('a supervisor can approve a movement', function () {
    $supervisor = User::factory()->create(['role' => Role::Supervisor]);
    $movement = Movement::factory()->create();

    $response = $this->actingAs($supervisor)->postJson("/api/movements/{$movement->id}/approve");

    $response->assertOk();
    $response->assertJsonPath('status', 'approved');
    $response->assertJsonPath('approved_by', $supervisor->name);
});

test('a movement cannot depart before it is approved', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $movement = Movement::factory()->create();

    $this->actingAs($officer)->postJson("/api/movements/{$movement->id}/depart")->assertUnprocessable();
});

test('a movement can be taken through the full lifecycle in order', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $supervisor = User::factory()->create(['role' => Role::Supervisor]);
    $movement = Movement::factory()->create();

    $this->actingAs($supervisor)->postJson("/api/movements/{$movement->id}/approve")->assertOk();

    $departed = $this->actingAs($officer)->postJson("/api/movements/{$movement->id}/depart");
    $departed->assertOk();
    $departed->assertJsonPath('status', 'departed');

    $arrived = $this->actingAs($officer)->postJson("/api/movements/{$movement->id}/arrive");
    $arrived->assertOk();
    $arrived->assertJsonPath('status', 'arrived');

    $returned = $this->actingAs($officer)->postJson("/api/movements/{$movement->id}/return");
    $returned->assertOk();
    $returned->assertJsonPath('status', 'returned');
});

test('a movement can be cancelled before it departs but not after', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $supervisor = User::factory()->create(['role' => Role::Supervisor]);

    $requested = Movement::factory()->create();
    $this->actingAs($officer)->postJson("/api/movements/{$requested->id}/cancel")->assertOk();

    $departing = Movement::factory()->create();
    $this->actingAs($supervisor)->postJson("/api/movements/{$departing->id}/approve")->assertOk();
    $this->actingAs($officer)->postJson("/api/movements/{$departing->id}/depart")->assertOk();
    $this->actingAs($officer)->postJson("/api/movements/{$departing->id}/cancel")->assertUnprocessable();
});

test('upcoming movements excludes returned and cancelled ones', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);

    $active = Movement::factory()->create(['status' => 'approved']);
    Movement::factory()->create(['status' => 'returned']);
    Movement::factory()->create(['status' => 'cancelled']);

    $response = $this->actingAs($officer)->getJson('/api/movements/upcoming');

    $response->assertOk();
    $ids = collect($response->json())->pluck('id');
    expect($ids)->toHaveCount(1);
    expect($ids->first())->toBe($active->id);
});

test('guests cannot access movement endpoints', function () {
    $this->getJson('/api/movements/upcoming')->assertUnauthorized();
});
