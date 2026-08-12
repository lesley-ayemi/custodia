<?php

use App\Enums\Role;
use App\Models\Block;
use App\Models\Cell;
use App\Models\Facility;
use App\Models\Prisoner;
use App\Models\User;

function makeCell(string $blockName = 'Block A', string $code = 'A-101', int $capacity = 2): Cell
{
    $block = Block::create(['name' => $blockName, 'facility_id' => Facility::first()->id]);
    $wing = $block->wings()->create(['name' => 'Wing 1']);

    return $wing->cells()->create(['code' => $code, 'capacity' => $capacity]);
}

test('an officer can assign a prisoner to a cell', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $prisoner = Prisoner::factory()->create();
    $cell = makeCell();

    $response = $this->actingAs($officer)->postJson('/api/housing-assignments', [
        'prisoner_id' => $prisoner->id,
        'cell_id' => $cell->id,
    ]);

    $response->assertCreated();
    $response->assertJsonPath('cell_code', 'A-101');
    $this->assertDatabaseHas('housing_assignments', [
        'prisoner_id' => $prisoner->id,
        'cell_id' => $cell->id,
        'ended_at' => null,
    ]);
});

test('reassigning a prisoner closes the previous housing assignment', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $prisoner = Prisoner::factory()->create();
    $cellA = makeCell('Block A', 'A-101');
    $cellB = makeCell('Block B', 'B-104');

    $this->actingAs($officer)->postJson('/api/housing-assignments', [
        'prisoner_id' => $prisoner->id,
        'cell_id' => $cellA->id,
    ])->assertCreated();

    $this->actingAs($officer)->postJson('/api/housing-assignments', [
        'prisoner_id' => $prisoner->id,
        'cell_id' => $cellB->id,
    ])->assertCreated();

    $history = $prisoner->housingAssignments()->orderBy('started_at')->get();

    expect($history)->toHaveCount(2);
    expect($history->first()->ended_at)->not->toBeNull();
    expect($history->first()->cell_id)->toBe($cellA->id);
    expect($history->last()->ended_at)->toBeNull();
    expect($history->last()->cell_id)->toBe($cellB->id);
});

test('housing history is returned in full for a prisoner', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $prisoner = Prisoner::factory()->create();
    $cell = makeCell();

    $this->actingAs($officer)->postJson('/api/housing-assignments', [
        'prisoner_id' => $prisoner->id,
        'cell_id' => $cell->id,
    ]);

    $response = $this->actingAs($officer)->getJson("/api/prisoners/{$prisoner->id}/housing-history");

    $response->assertOk();
    expect($response->json())->toHaveCount(1);
});

test('an admin can assign a prisoner to a cell', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $prisoner = Prisoner::factory()->create();
    $cell = makeCell();

    $response = $this->actingAs($admin)->postJson('/api/housing-assignments', [
        'prisoner_id' => $prisoner->id,
        'cell_id' => $cell->id,
    ]);

    $response->assertCreated();
});

test('a supervisor cannot assign a prisoner to a cell', function () {
    $supervisor = User::factory()->create(['role' => Role::Supervisor]);
    $prisoner = Prisoner::factory()->create();
    $cell = makeCell();

    $response = $this->actingAs($supervisor)->postJson('/api/housing-assignments', [
        'prisoner_id' => $prisoner->id,
        'cell_id' => $cell->id,
    ]);

    $response->assertForbidden();
});
