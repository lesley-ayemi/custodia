<?php

use App\Enums\Role;
use App\Models\Block;
use App\Models\Prisoner;
use App\Models\User;

test('an admin can create a block', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);

    $response = $this->actingAs($admin)->postJson('/api/blocks', ['name' => 'Block D']);

    $response->assertCreated();
    $this->assertDatabaseHas('blocks', ['name' => 'Block D']);
});

test('an officer cannot create a block', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);

    $this->actingAs($officer)->postJson('/api/blocks', ['name' => 'Block D'])->assertForbidden();
});

test('a supervisor cannot create a block', function () {
    $supervisor = User::factory()->create(['role' => Role::Supervisor]);

    $this->actingAs($supervisor)->postJson('/api/blocks', ['name' => 'Block D'])->assertForbidden();
});

test('an admin can rename a block', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $block = Block::create(['name' => 'Block D']);

    $response = $this->actingAs($admin)->putJson("/api/blocks/{$block->id}", ['name' => 'Block E']);

    $response->assertOk();
    $response->assertJsonPath('name', 'Block E');
});

test('an admin can delete an empty block', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $block = Block::create(['name' => 'Block D']);

    $this->actingAs($admin)->deleteJson("/api/blocks/{$block->id}")->assertNoContent();
    $this->assertDatabaseMissing('blocks', ['id' => $block->id]);
});

test('an admin cannot delete a block that still has cells', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $block = Block::create(['name' => 'Block D']);
    $block->cells()->create(['code' => 'D-101', 'capacity' => 2]);

    $this->actingAs($admin)->deleteJson("/api/blocks/{$block->id}")->assertUnprocessable();
    $this->assertDatabaseHas('blocks', ['id' => $block->id]);
});

test('an admin can create a cell in a block', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $block = Block::create(['name' => 'Block D']);

    $response = $this->actingAs($admin)->postJson('/api/cells', [
        'block_id' => $block->id,
        'code' => 'D-101',
        'capacity' => 2,
    ]);

    $response->assertCreated();
    $response->assertJsonPath('code', 'D-101');
    $response->assertJsonPath('occupancy', 0);
    $response->assertJsonPath('available', 2);
});

test('an officer cannot create a cell', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $block = Block::create(['name' => 'Block D']);

    $this->actingAs($officer)->postJson('/api/cells', [
        'block_id' => $block->id,
        'code' => 'D-101',
        'capacity' => 2,
    ])->assertForbidden();
});

test('an admin can update a cells capacity', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $block = Block::create(['name' => 'Block D']);
    $cell = $block->cells()->create(['code' => 'D-101', 'capacity' => 2]);

    $response = $this->actingAs($admin)->putJson("/api/cells/{$cell->id}", ['capacity' => 4]);

    $response->assertOk();
    $response->assertJsonPath('capacity', 4);
});

test('an admin cannot shrink a cells capacity below its current occupancy', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $officer = User::factory()->create(['role' => Role::Officer]);
    $block = Block::create(['name' => 'Block D']);
    $cell = $block->cells()->create(['code' => 'D-101', 'capacity' => 2]);
    $prisonerA = Prisoner::factory()->create();
    $prisonerB = Prisoner::factory()->create();

    $this->actingAs($officer)->postJson('/api/housing-assignments', ['prisoner_id' => $prisonerA->id, 'cell_id' => $cell->id])->assertCreated();
    $this->actingAs($officer)->postJson('/api/housing-assignments', ['prisoner_id' => $prisonerB->id, 'cell_id' => $cell->id])->assertCreated();

    $this->actingAs($admin)->putJson("/api/cells/{$cell->id}", ['capacity' => 1])->assertUnprocessable();
});

test('an admin can delete a cell with no housing history', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $block = Block::create(['name' => 'Block D']);
    $cell = $block->cells()->create(['code' => 'D-101', 'capacity' => 2]);

    $this->actingAs($admin)->deleteJson("/api/cells/{$cell->id}")->assertNoContent();
    $this->assertDatabaseMissing('cells', ['id' => $cell->id]);
});

test('an admin cannot delete a cell with housing history', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $officer = User::factory()->create(['role' => Role::Officer]);
    $block = Block::create(['name' => 'Block D']);
    $cell = $block->cells()->create(['code' => 'D-101', 'capacity' => 2]);
    $prisoner = Prisoner::factory()->create();

    $this->actingAs($officer)->postJson('/api/housing-assignments', ['prisoner_id' => $prisoner->id, 'cell_id' => $cell->id])->assertCreated();

    $this->actingAs($admin)->deleteJson("/api/cells/{$cell->id}")->assertUnprocessable();
    $this->assertDatabaseHas('cells', ['id' => $cell->id]);
});

test('a supervisor cannot manage cells', function () {
    $supervisor = User::factory()->create(['role' => Role::Supervisor]);
    $block = Block::create(['name' => 'Block D']);
    $cell = $block->cells()->create(['code' => 'D-101', 'capacity' => 2]);

    $this->actingAs($supervisor)->putJson("/api/cells/{$cell->id}", ['capacity' => 4])->assertForbidden();
    $this->actingAs($supervisor)->deleteJson("/api/cells/{$cell->id}")->assertForbidden();
});
