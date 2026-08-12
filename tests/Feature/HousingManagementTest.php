<?php

use App\Enums\Role;
use App\Models\Block;
use App\Models\Facility;
use App\Models\Prisoner;
use App\Models\User;

test('an admin can create a block', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);

    $response = $this->actingAs($admin)->postJson('/api/blocks', ['name' => 'Block D']);

    $response->assertCreated();
    $this->assertDatabaseHas('blocks', ['name' => 'Block D', 'facility_id' => Facility::first()->id]);
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
    $block = Block::create(['name' => 'Block D', 'facility_id' => Facility::first()->id]);

    $response = $this->actingAs($admin)->putJson("/api/blocks/{$block->id}", ['name' => 'Block E']);

    $response->assertOk();
    $response->assertJsonPath('name', 'Block E');
});

test('an admin can delete an empty block', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $block = Block::create(['name' => 'Block D', 'facility_id' => Facility::first()->id]);

    $this->actingAs($admin)->deleteJson("/api/blocks/{$block->id}")->assertNoContent();
    $this->assertDatabaseMissing('blocks', ['id' => $block->id]);
});

test('an admin cannot delete a block that still has wings', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $block = Block::create(['name' => 'Block D', 'facility_id' => Facility::first()->id]);
    $block->wings()->create(['name' => 'Wing 1']);

    $this->actingAs($admin)->deleteJson("/api/blocks/{$block->id}")->assertUnprocessable();
    $this->assertDatabaseHas('blocks', ['id' => $block->id]);
});

test('an admin can add a wing to a block', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $block = Block::create(['name' => 'Block D', 'facility_id' => Facility::first()->id]);

    $response = $this->actingAs($admin)->postJson('/api/wings', ['block_id' => $block->id, 'name' => 'Wing 1']);

    $response->assertCreated();
    $response->assertJsonPath('name', 'Wing 1');
});

test('an officer cannot add a wing', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $block = Block::create(['name' => 'Block D', 'facility_id' => Facility::first()->id]);

    $this->actingAs($officer)->postJson('/api/wings', ['block_id' => $block->id, 'name' => 'Wing 1'])->assertForbidden();
});

test('an admin can rename a wing', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $block = Block::create(['name' => 'Block D', 'facility_id' => Facility::first()->id]);
    $wing = $block->wings()->create(['name' => 'Wing 1']);

    $response = $this->actingAs($admin)->putJson("/api/wings/{$wing->id}", ['name' => 'Wing 2']);

    $response->assertOk();
    $response->assertJsonPath('name', 'Wing 2');
});

test('an admin cannot delete a wing that still has cells', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $block = Block::create(['name' => 'Block D', 'facility_id' => Facility::first()->id]);
    $wing = $block->wings()->create(['name' => 'Wing 1']);
    $wing->cells()->create(['code' => 'D-101', 'capacity' => 2]);

    $this->actingAs($admin)->deleteJson("/api/wings/{$wing->id}")->assertUnprocessable();
    $this->assertDatabaseHas('wings', ['id' => $wing->id]);
});

test('an admin can delete an empty wing', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $block = Block::create(['name' => 'Block D', 'facility_id' => Facility::first()->id]);
    $wing = $block->wings()->create(['name' => 'Wing 1']);

    $this->actingAs($admin)->deleteJson("/api/wings/{$wing->id}")->assertNoContent();
    $this->assertDatabaseMissing('wings', ['id' => $wing->id]);
});

test('an admin can create a cell in a wing', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $block = Block::create(['name' => 'Block D', 'facility_id' => Facility::first()->id]);
    $wing = $block->wings()->create(['name' => 'Wing 1']);

    $response = $this->actingAs($admin)->postJson('/api/cells', [
        'wing_id' => $wing->id,
        'code' => 'D-101',
        'capacity' => 2,
    ]);

    $response->assertCreated();
    $response->assertJsonPath('code', 'D-101');
    $response->assertJsonPath('occupancy', 0);
    $response->assertJsonPath('available', 2);
    $response->assertJsonPath('wing_name', 'Wing 1');
    $response->assertJsonPath('block_name', 'Block D');
});

test('an officer cannot create a cell', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $block = Block::create(['name' => 'Block D', 'facility_id' => Facility::first()->id]);
    $wing = $block->wings()->create(['name' => 'Wing 1']);

    $this->actingAs($officer)->postJson('/api/cells', [
        'wing_id' => $wing->id,
        'code' => 'D-101',
        'capacity' => 2,
    ])->assertForbidden();
});

test('an admin can update a cells capacity', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $block = Block::create(['name' => 'Block D', 'facility_id' => Facility::first()->id]);
    $wing = $block->wings()->create(['name' => 'Wing 1']);
    $cell = $wing->cells()->create(['code' => 'D-101', 'capacity' => 2]);

    $response = $this->actingAs($admin)->putJson("/api/cells/{$cell->id}", ['capacity' => 4]);

    $response->assertOk();
    $response->assertJsonPath('capacity', 4);
});

test('an admin cannot shrink a cells capacity below its current occupancy', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $officer = User::factory()->create(['role' => Role::Officer]);
    $block = Block::create(['name' => 'Block D', 'facility_id' => Facility::first()->id]);
    $wing = $block->wings()->create(['name' => 'Wing 1']);
    $cell = $wing->cells()->create(['code' => 'D-101', 'capacity' => 2]);
    $prisonerA = Prisoner::factory()->create();
    $prisonerB = Prisoner::factory()->create();

    $this->actingAs($officer)->postJson('/api/housing-assignments', ['prisoner_id' => $prisonerA->id, 'cell_id' => $cell->id])->assertCreated();
    $this->actingAs($officer)->postJson('/api/housing-assignments', ['prisoner_id' => $prisonerB->id, 'cell_id' => $cell->id])->assertCreated();

    $this->actingAs($admin)->putJson("/api/cells/{$cell->id}", ['capacity' => 1])->assertUnprocessable();
});

test('an admin can delete a cell with no housing history', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $block = Block::create(['name' => 'Block D', 'facility_id' => Facility::first()->id]);
    $wing = $block->wings()->create(['name' => 'Wing 1']);
    $cell = $wing->cells()->create(['code' => 'D-101', 'capacity' => 2]);

    $this->actingAs($admin)->deleteJson("/api/cells/{$cell->id}")->assertNoContent();
    $this->assertDatabaseMissing('cells', ['id' => $cell->id]);
});

test('an admin cannot delete a cell with housing history', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $officer = User::factory()->create(['role' => Role::Officer]);
    $block = Block::create(['name' => 'Block D', 'facility_id' => Facility::first()->id]);
    $wing = $block->wings()->create(['name' => 'Wing 1']);
    $cell = $wing->cells()->create(['code' => 'D-101', 'capacity' => 2]);
    $prisoner = Prisoner::factory()->create();

    $this->actingAs($officer)->postJson('/api/housing-assignments', ['prisoner_id' => $prisoner->id, 'cell_id' => $cell->id])->assertCreated();

    $this->actingAs($admin)->deleteJson("/api/cells/{$cell->id}")->assertUnprocessable();
    $this->assertDatabaseHas('cells', ['id' => $cell->id]);
});

test('a supervisor cannot manage cells', function () {
    $supervisor = User::factory()->create(['role' => Role::Supervisor]);
    $block = Block::create(['name' => 'Block D', 'facility_id' => Facility::first()->id]);
    $wing = $block->wings()->create(['name' => 'Wing 1']);
    $cell = $wing->cells()->create(['code' => 'D-101', 'capacity' => 2]);

    $this->actingAs($supervisor)->putJson("/api/cells/{$cell->id}", ['capacity' => 4])->assertForbidden();
    $this->actingAs($supervisor)->deleteJson("/api/cells/{$cell->id}")->assertForbidden();
});
