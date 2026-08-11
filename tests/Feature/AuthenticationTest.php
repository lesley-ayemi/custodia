<?php

use App\Enums\Role;
use App\Models\User;

test('a user can log in with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'admin@demo.com',
        'password' => bcrypt('password'),
        'role' => Role::Admin,
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'admin@demo.com',
        'password' => 'password',
    ]);

    $response->assertOk()->assertJson(['id' => $user->id, 'role' => 'admin']);
    $this->assertAuthenticatedAs($user);
});

test('login fails with invalid credentials', function () {
    User::factory()->create(['email' => 'admin@demo.com', 'password' => bcrypt('password')]);

    $response = $this->postJson('/api/login', [
        'email' => 'admin@demo.com',
        'password' => 'wrong-password',
    ]);

    $response->assertUnprocessable();
    $this->assertGuest();
});

test('an authenticated user can log out', function () {
    User::factory()->create(['email' => 'admin@demo.com', 'password' => bcrypt('password')]);

    $this->postJson('/api/login', ['email' => 'admin@demo.com', 'password' => 'password'])->assertOk();

    $this->postJson('/api/logout')->assertNoContent();
});

test('logout requires authentication', function () {
    $this->postJson('/api/logout')->assertUnauthorized();
});

test('me endpoint returns the authenticated user', function () {
    $user = User::factory()->create(['role' => Role::Supervisor]);

    $response = $this->actingAs($user)->getJson('/api/me');

    $response->assertOk()->assertJson(['id' => $user->id, 'role' => 'supervisor']);
});

test('guests cannot access protected endpoints', function () {
    $this->getJson('/api/me')->assertUnauthorized();
    $this->getJson('/api/dashboard')->assertUnauthorized();
});
