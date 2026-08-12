<?php

use App\Enums\Role;
use App\Models\Prisoner;
use App\Models\PropertyItem;
use App\Models\User;

test('an officer can receive a property bag with multiple items', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $prisoner = Prisoner::factory()->create();

    $response = $this->actingAs($officer)->postJson("/api/prisoners/{$prisoner->id}/property", [
        'items' => [
            ['description' => 'Phone', 'quantity' => 1, 'storage_location' => 'Store A'],
            ['description' => 'Wallet', 'quantity' => 1, 'storage_location' => 'Store A'],
            ['description' => 'Cash', 'quantity' => 1, 'storage_location' => 'Safe 1'],
        ],
    ]);

    $response->assertCreated();
    expect($response->json())->toHaveCount(3);

    $propertyNumber = $response->json('0.property_number');
    $this->assertMatchesRegularExpression('/^PB-\d{4}-\d{4}$/', $propertyNumber);

    foreach ($response->json() as $item) {
        expect($item['property_number'])->toBe($propertyNumber);
    }
});

test('a supervisor cannot receive property', function () {
    $supervisor = User::factory()->create(['role' => Role::Supervisor]);
    $prisoner = Prisoner::factory()->create();

    $response = $this->actingAs($supervisor)->postJson("/api/prisoners/{$prisoner->id}/property", [
        'items' => [['description' => 'Phone', 'quantity' => 1, 'storage_location' => 'Store A']],
    ]);

    $response->assertForbidden();
});

test('an admin can receive property', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $prisoner = Prisoner::factory()->create();

    $response = $this->actingAs($admin)->postJson("/api/prisoners/{$prisoner->id}/property", [
        'items' => [['description' => 'Phone', 'quantity' => 1, 'storage_location' => 'Store A']],
    ]);

    $response->assertCreated();
});

test('all three roles can view a prisoners property', function () {
    $prisoner = Prisoner::factory()->create();
    PropertyItem::factory()->for($prisoner)->create();

    foreach ([Role::Admin, Role::Officer, Role::Supervisor] as $role) {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)->getJson("/api/prisoners/{$prisoner->id}/property")->assertOk();
    }
});

test('an officer can release a property item', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $item = PropertyItem::factory()->create();

    $response = $this->actingAs($officer)->postJson("/api/property-items/{$item->id}/release");

    $response->assertOk();
    $response->assertJsonPath('released_by', $officer->name);
    expect($item->fresh()->released_at)->not->toBeNull();
});

test('a supervisor cannot release a property item', function () {
    $supervisor = User::factory()->create(['role' => Role::Supervisor]);
    $item = PropertyItem::factory()->create();

    $this->actingAs($supervisor)->postJson("/api/property-items/{$item->id}/release")->assertForbidden();
});

test('each item in a bag can be released independently', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $prisoner = Prisoner::factory()->create();

    $response = $this->actingAs($officer)->postJson("/api/prisoners/{$prisoner->id}/property", [
        'items' => [
            ['description' => 'Phone', 'quantity' => 1, 'storage_location' => 'Store A'],
            ['description' => 'Wallet', 'quantity' => 1, 'storage_location' => 'Store A'],
        ],
    ]);

    $firstItemId = $response->json('0.id');

    $this->actingAs($officer)->postJson("/api/property-items/{$firstItemId}/release")->assertOk();

    $items = $this->actingAs($officer)->getJson("/api/prisoners/{$prisoner->id}/property")->json();
    $released = collect($items)->firstWhere('id', $firstItemId);
    $stillHeld = collect($items)->firstWhere('id', '!=', $firstItemId);

    expect($released['released_at'])->not->toBeNull();
    expect($stillHeld['released_at'])->toBeNull();
});

test('guests cannot access property endpoints', function () {
    $prisoner = Prisoner::factory()->create();

    $this->getJson("/api/prisoners/{$prisoner->id}/property")->assertUnauthorized();
});
