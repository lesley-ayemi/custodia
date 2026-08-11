<?php

use App\Enums\Role;
use App\Models\Incident;
use App\Models\Prisoner;
use App\Models\User;

test('an admin can create a staff member', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);

    $response = $this->actingAs($admin)->postJson('/api/users', [
        'name' => 'New Officer',
        'email' => 'new.officer@demo.com',
        'password' => 'password123',
        'role' => 'officer',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('email', 'new.officer@demo.com');
    $this->assertDatabaseHas('users', ['email' => 'new.officer@demo.com', 'role' => 'officer']);
});

test('an officer cannot create a staff member', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);

    $response = $this->actingAs($officer)->postJson('/api/users', [
        'name' => 'New Officer',
        'email' => 'new.officer@demo.com',
        'password' => 'password123',
        'role' => 'officer',
    ]);

    $response->assertForbidden();
});

test('a supervisor cannot create a staff member', function () {
    $supervisor = User::factory()->create(['role' => Role::Supervisor]);

    $response = $this->actingAs($supervisor)->postJson('/api/users', [
        'name' => 'New Officer',
        'email' => 'new.officer@demo.com',
        'password' => 'password123',
        'role' => 'officer',
    ]);

    $response->assertForbidden();
});

test('an admin can update a staff member', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $target = User::factory()->create(['role' => Role::Officer, 'name' => 'Old Name']);

    $response = $this->actingAs($admin)->putJson("/api/users/{$target->id}", [
        'name' => 'New Name',
        'role' => 'supervisor',
    ]);

    $response->assertOk();
    $response->assertJsonPath('name', 'New Name');
    $response->assertJsonPath('role', 'supervisor');
});

test('search filters staff by name or email', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    User::factory()->create(['name' => 'Zach Zebra', 'email' => 'zach@demo.com']);
    User::factory()->create(['name' => 'Amy Apple', 'email' => 'amy@demo.com']);

    $response = $this->actingAs($admin)->getJson('/api/users?search=Zach');

    $response->assertOk();
    expect(collect($response->json('data'))->pluck('name'))->toContain('Zach Zebra');
    expect(collect($response->json('data'))->pluck('name'))->not->toContain('Amy Apple');
});

test('staff list can be sorted by name descending', function () {
    $admin = User::factory()->create(['role' => Role::Admin, 'name' => 'AAA Admin']);
    User::factory()->create(['name' => 'Zach Zebra']);

    $response = $this->actingAs($admin)->getJson('/api/users?sort=name&direction=desc');

    $response->assertOk();
    $names = collect($response->json('data'))->pluck('name');
    expect($names->first())->toBe('Zach Zebra');
});

test('an admin can deactivate another staff member', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $target = User::factory()->create(['role' => Role::Officer]);

    $this->actingAs($admin)->deleteJson("/api/users/{$target->id}")->assertNoContent();

    $this->assertSoftDeleted('users', ['id' => $target->id]);
});

test('a deactivated user can no longer log in', function () {
    $user = User::factory()->create(['email' => 'gone@demo.com', 'password' => bcrypt('password')]);
    $user->delete();

    $this->postJson('/api/login', ['email' => 'gone@demo.com', 'password' => 'password'])
        ->assertUnprocessable();
});

test('an admin cannot deactivate themselves', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);

    $this->actingAs($admin)->deleteJson("/api/users/{$admin->id}")->assertForbidden();
    $this->assertDatabaseHas('users', ['id' => $admin->id, 'deleted_at' => null]);
});

test('an officer cannot deactivate a staff member', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $target = User::factory()->create(['role' => Role::Supervisor]);

    $this->actingAs($officer)->deleteJson("/api/users/{$target->id}")->assertForbidden();
});

test('deactivated housing and incident history survives even though the user is gone', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $officer = User::factory()->create(['role' => Role::Officer]);
    $prisoner = Prisoner::factory()->create();
    $incident = Incident::factory()->for($prisoner)->create(['officer_id' => $officer->id]);

    $this->actingAs($admin)->deleteJson("/api/users/{$officer->id}")->assertNoContent();

    $this->assertDatabaseHas('incidents', ['id' => $incident->id, 'officer_id' => $officer->id]);
});
