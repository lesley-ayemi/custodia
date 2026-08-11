<?php

use App\Enums\Role;
use App\Models\User;

test('admin and supervisor can view audit logs but officer cannot', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $supervisor = User::factory()->create(['role' => Role::Supervisor]);
    $officer = User::factory()->create(['role' => Role::Officer]);

    $this->actingAs($admin)->getJson('/api/audit-logs')->assertOk();
    $this->actingAs($supervisor)->getJson('/api/audit-logs')->assertOk();
    $this->actingAs($officer)->getJson('/api/audit-logs')->assertForbidden();
});

test('all three roles can view the dashboard', function () {
    foreach ([Role::Admin, Role::Officer, Role::Supervisor] as $role) {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)->getJson('/api/dashboard')->assertOk();
    }
});

test('all three roles can view housing blocks', function () {
    foreach ([Role::Admin, Role::Officer, Role::Supervisor] as $role) {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)->getJson('/api/blocks')->assertOk();
    }
});

test('guests are rejected from every protected module', function () {
    $this->getJson('/api/prisoners')->assertUnauthorized();
    $this->getJson('/api/blocks')->assertUnauthorized();
    $this->getJson('/api/incidents')->assertUnauthorized();
    $this->getJson('/api/audit-logs')->assertUnauthorized();
    $this->getJson('/api/dashboard')->assertUnauthorized();
});
