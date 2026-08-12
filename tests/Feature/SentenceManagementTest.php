<?php

use App\Enums\Role;
use App\Models\Prisoner;
use App\Models\Sentence;
use App\Models\User;

test('an officer can record a sentence for a prisoner', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $prisoner = Prisoner::factory()->create();

    $response = $this->actingAs($officer)->postJson("/api/prisoners/{$prisoner->id}/sentences", [
        'case_number' => 'CASE-2026-0001',
        'court' => 'Crown Court',
        'offence' => 'Burglary',
        'sentence_start' => now()->toDateString(),
        'sentence_end' => now()->addYears(3)->toDateString(),
        'sentence_type' => 'custodial',
        'parole_eligibility_date' => now()->addYears(1)->toDateString(),
        'legal_status' => 'convicted',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('case_number', 'CASE-2026-0001');
    $response->assertJsonPath('sentence_type', 'custodial');
    $this->assertDatabaseHas('sentences', ['prisoner_id' => $prisoner->id, 'case_number' => 'CASE-2026-0001']);
});

test('a life sentence can be recorded without an end date', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $prisoner = Prisoner::factory()->create();

    $response = $this->actingAs($officer)->postJson("/api/prisoners/{$prisoner->id}/sentences", [
        'case_number' => 'CASE-2026-0002',
        'court' => 'Crown Court',
        'offence' => 'Murder',
        'sentence_start' => now()->toDateString(),
        'sentence_type' => 'life',
        'legal_status' => 'convicted',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('sentence_end', null);
});

test('a supervisor cannot record a sentence', function () {
    $supervisor = User::factory()->create(['role' => Role::Supervisor]);
    $prisoner = Prisoner::factory()->create();

    $response = $this->actingAs($supervisor)->postJson("/api/prisoners/{$prisoner->id}/sentences", [
        'case_number' => 'CASE-2026-0003',
        'court' => 'Crown Court',
        'offence' => 'Theft',
        'sentence_start' => now()->toDateString(),
        'sentence_type' => 'custodial',
        'legal_status' => 'convicted',
    ]);

    $response->assertForbidden();
});

test('an admin can record a sentence', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $prisoner = Prisoner::factory()->create();

    $response = $this->actingAs($admin)->postJson("/api/prisoners/{$prisoner->id}/sentences", [
        'case_number' => 'CASE-2026-0004',
        'court' => 'Crown Court',
        'offence' => 'Fraud',
        'sentence_start' => now()->toDateString(),
        'sentence_type' => 'custodial',
        'legal_status' => 'convicted',
    ]);

    $response->assertCreated();
});

test('sentence end date cannot be before the start date', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $prisoner = Prisoner::factory()->create();

    $response = $this->actingAs($officer)->postJson("/api/prisoners/{$prisoner->id}/sentences", [
        'case_number' => 'CASE-2026-0005',
        'court' => 'Crown Court',
        'offence' => 'Theft',
        'sentence_start' => now()->toDateString(),
        'sentence_end' => now()->subDay()->toDateString(),
        'sentence_type' => 'custodial',
        'legal_status' => 'convicted',
    ]);

    $response->assertUnprocessable();
});

test('all three roles can view a prisoners sentences', function () {
    $prisoner = Prisoner::factory()->create();
    Sentence::factory()->for($prisoner)->create();

    foreach ([Role::Admin, Role::Officer, Role::Supervisor] as $role) {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)->getJson("/api/prisoners/{$prisoner->id}/sentences")->assertOk();
    }
});

test('a single sentence can be viewed', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $sentence = Sentence::factory()->create();

    $response = $this->actingAs($officer)->getJson("/api/sentences/{$sentence->id}");

    $response->assertOk();
    $response->assertJsonPath('id', $sentence->id);
});

test('recording a sentence writes an audit log entry', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $prisoner = Prisoner::factory()->create();

    $this->actingAs($officer)->postJson("/api/prisoners/{$prisoner->id}/sentences", [
        'case_number' => 'CASE-2026-0006',
        'court' => 'Crown Court',
        'offence' => 'Robbery',
        'sentence_start' => now()->toDateString(),
        'sentence_type' => 'custodial',
        'legal_status' => 'convicted',
    ])->assertCreated();

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'recorded sentence',
        'entity_type' => 'Sentence',
    ]);
});

test('guests cannot access sentence endpoints', function () {
    $prisoner = Prisoner::factory()->create();

    $this->getJson("/api/prisoners/{$prisoner->id}/sentences")->assertUnauthorized();
});
