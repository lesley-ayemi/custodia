<?php

use App\Enums\Role;
use App\Models\Prisoner;
use App\Models\User;

test('an officer can register a prisoner', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);

    $response = $this->actingAs($officer)->postJson('/api/prisoners', [
        'first_name' => 'Daniel',
        'last_name' => 'Johnson',
        'date_of_birth' => '1996-05-12',
        'gender' => 'male',
        'admission_date' => '2026-08-10',
        'expected_release_date' => '2028-03-20',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('first_name', 'Daniel');
    $response->assertJsonPath('status', 'in_custody');
    $this->assertMatchesRegularExpression('/^INM-\d{4}-\d{4}$/', $response->json('prisoner_number'));
    $this->assertDatabaseHas('prisoners', ['first_name' => 'Daniel', 'last_name' => 'Johnson']);
});

test('an admin can register a prisoner', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);

    $response = $this->actingAs($admin)->postJson('/api/prisoners', [
        'first_name' => 'Daniel',
        'last_name' => 'Johnson',
        'date_of_birth' => '1996-05-12',
        'gender' => 'male',
        'admission_date' => '2026-08-10',
    ]);

    $response->assertCreated();
});

test('a supervisor cannot register a prisoner', function () {
    $supervisor = User::factory()->create(['role' => Role::Supervisor]);

    $response = $this->actingAs($supervisor)->postJson('/api/prisoners', [
        'first_name' => 'Daniel',
        'last_name' => 'Johnson',
        'date_of_birth' => '1996-05-12',
        'gender' => 'male',
        'admission_date' => '2026-08-10',
    ]);

    $response->assertForbidden();
});

test('all three roles can view the prisoner list', function () {
    foreach ([Role::Admin, Role::Officer, Role::Supervisor] as $role) {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)->getJson('/api/prisoners')->assertOk();
    }
});

test('searching prisoners filters by name or prisoner number', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    Prisoner::factory()->create(['first_name' => 'Daniel', 'last_name' => 'Johnson']);
    Prisoner::factory()->create(['first_name' => 'Maria', 'last_name' => 'Garcia']);

    $response = $this->actingAs($officer)->getJson('/api/prisoners?search=Daniel');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
    $response->assertJsonPath('data.0.first_name', 'Daniel');
});

test('an officer can archive a prisoner and it disappears from the index', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $prisoner = Prisoner::factory()->create();

    $this->actingAs($officer)->postJson("/api/prisoners/{$prisoner->id}/archive")->assertOk();

    $this->assertDatabaseHas('prisoners', ['id' => $prisoner->id]);
    expect($prisoner->fresh()->archived_at)->not->toBeNull();

    $response = $this->actingAs($officer)->getJson('/api/prisoners');
    expect(collect($response->json('data'))->pluck('id'))->not->toContain($prisoner->id);
});
