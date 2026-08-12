<?php

use App\Enums\Role;
use App\Models\Admission;
use App\Models\Block;
use App\Models\Cell;
use App\Models\Facility;
use App\Models\User;

function admissionCell(): Cell
{
    $block = Block::create(['name' => 'Admission Test Block', 'facility_id' => Facility::first()->id]);
    $wing = $block->wings()->create(['name' => 'Wing 1']);

    return $wing->cells()->create(['code' => 'ATB-101', 'capacity' => 2]);
}

test('an officer can start an admission', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);

    $response = $this->actingAs($officer)->postJson('/api/admissions', [
        'first_name' => 'Nadia',
        'last_name' => 'Osei',
        'date_of_birth' => '1990-01-01',
        'gender' => 'female',
        'admission_date' => now()->toDateString(),
        'admission_reason' => 'Remanded in custody',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('status', 'draft');
    $response->assertJsonPath('prisoner_name', 'Nadia Osei');
    $this->assertDatabaseHas('prisoners', ['first_name' => 'Nadia', 'last_name' => 'Osei']);
});

test('a supervisor cannot start an admission', function () {
    $supervisor = User::factory()->create(['role' => Role::Supervisor]);

    $response = $this->actingAs($supervisor)->postJson('/api/admissions', [
        'first_name' => 'Nadia',
        'last_name' => 'Osei',
        'date_of_birth' => '1990-01-01',
        'gender' => 'female',
        'admission_date' => now()->toDateString(),
        'admission_reason' => 'Remanded in custody',
    ]);

    $response->assertForbidden();
});

test('an officer can record legal authority, moving the admission to processing', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $admission = Admission::factory()->create();

    $response = $this->actingAs($officer)->postJson("/api/admissions/{$admission->id}/legal-authority", [
        'reference' => 'Remand Order #RO-2026-0045',
    ]);

    $response->assertOk();
    $response->assertJsonPath('status', 'processing');
    $response->assertJsonPath('legal_authority_reference', 'Remand Order #RO-2026-0045');
});

test('legal authority cannot be recorded twice', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $admission = Admission::factory()->create(['status' => 'processing']);

    $this->actingAs($officer)->postJson("/api/admissions/{$admission->id}/legal-authority", [
        'reference' => 'Remand Order #RO-2026-0045',
    ])->assertUnprocessable();
});

test('an officer can record initial assessment and security classification while processing', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $admission = Admission::factory()->create(['status' => 'processing']);

    $assessment = $this->actingAs($officer)->postJson("/api/admissions/{$admission->id}/assessment", [
        'notes' => 'No immediate concerns noted.',
    ]);
    $assessment->assertOk();
    $assessment->assertJsonPath('initial_assessment_notes', 'No immediate concerns noted.');

    $classification = $this->actingAs($officer)->postJson("/api/admissions/{$admission->id}/classification", [
        'classification' => 'medium',
    ]);
    $classification->assertOk();
    $classification->assertJsonPath('security_classification', 'medium');
});

test('an admission cannot advance to medical screening without a security classification', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $admission = Admission::factory()->create(['status' => 'processing']);

    $this->actingAs($officer)->postJson("/api/admissions/{$admission->id}/advance-to-medical")->assertUnprocessable();
});

test('an officer can advance to medical screening once classification is set', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $admission = Admission::factory()->create(['status' => 'processing', 'security_classification' => 'low']);

    $response = $this->actingAs($officer)->postJson("/api/admissions/{$admission->id}/advance-to-medical");

    $response->assertOk();
    $response->assertJsonPath('status', 'awaiting_medical');
});

test('an officer cannot complete medical screening', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $admission = Admission::factory()->create(['status' => 'awaiting_medical']);

    $this->actingAs($officer)->postJson("/api/admissions/{$admission->id}/complete-medical")->assertForbidden();
});

test('a medical officer can complete medical screening', function () {
    $medical = User::factory()->create(['role' => Role::Medical]);
    $admission = Admission::factory()->create(['status' => 'awaiting_medical']);

    $response = $this->actingAs($medical)->postJson("/api/admissions/{$admission->id}/complete-medical");

    $response->assertOk();
    $response->assertJsonPath('status', 'awaiting_housing');
});

test('housing assignment cannot be completed without an actual cell assignment', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $admission = Admission::factory()->create(['status' => 'awaiting_housing']);

    $this->actingAs($officer)->postJson("/api/admissions/{$admission->id}/complete-housing")->assertUnprocessable();
});

test('completing the housing assignment finishes the admission', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $admission = Admission::factory()->create(['status' => 'awaiting_housing']);
    $cell = admissionCell();

    $this->actingAs($officer)->postJson('/api/housing-assignments', [
        'prisoner_id' => $admission->prisoner_id,
        'cell_id' => $cell->id,
    ])->assertCreated();

    $response = $this->actingAs($officer)->postJson("/api/admissions/{$admission->id}/complete-housing");

    $response->assertOk();
    $response->assertJsonPath('status', 'completed');
    expect($admission->fresh()->completed_at)->not->toBeNull();
});

test('all three roles can view admissions', function () {
    Admission::factory()->create();

    foreach ([Role::Admin, Role::Officer, Role::Supervisor] as $role) {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)->getJson('/api/admissions')->assertOk();
    }
});

test('guests cannot access admission endpoints', function () {
    $this->getJson('/api/admissions')->assertUnauthorized();
});
