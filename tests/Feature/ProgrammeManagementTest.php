<?php

use App\Enums\Role;
use App\Models\Prisoner;
use App\Models\Programme;
use App\Models\ProgrammeEnrolment;
use App\Models\User;

test('an admin can add a programme to the catalog', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);

    $response = $this->actingAs($admin)->postJson('/api/programmes', [
        'name' => 'Life Skills',
        'category' => 'life_skills',
        'description' => 'Budgeting, cooking, and independent living skills.',
        'capacity' => 12,
    ]);

    $response->assertCreated();
    $response->assertJsonPath('name', 'Life Skills');
    $response->assertJsonPath('status', 'active');
});

test('an officer cannot add a programme to the catalog', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);

    $response = $this->actingAs($officer)->postJson('/api/programmes', [
        'name' => 'Life Skills',
        'category' => 'life_skills',
    ]);

    $response->assertForbidden();
});

test('all three roles can view the programme catalog', function () {
    Programme::factory()->create();

    foreach ([Role::Admin, Role::Officer, Role::Supervisor] as $role) {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)->getJson('/api/programmes')->assertOk();
    }
});

test('an admin can update a programme', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $programme = Programme::factory()->create(['capacity' => 10]);

    $response = $this->actingAs($admin)->putJson("/api/programmes/{$programme->id}", ['capacity' => 15]);

    $response->assertOk();
    $response->assertJsonPath('capacity', 15);
});

test('an admin cannot delete a programme with enrolment history', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $programme = Programme::factory()->create();
    ProgrammeEnrolment::factory()->for($programme)->create();

    $response = $this->actingAs($admin)->deleteJson("/api/programmes/{$programme->id}");

    $response->assertUnprocessable();
    $this->assertDatabaseHas('programmes', ['id' => $programme->id]);
});

test('an admin can delete a programme with no enrolment history', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $programme = Programme::factory()->create();

    $response = $this->actingAs($admin)->deleteJson("/api/programmes/{$programme->id}");

    $response->assertNoContent();
    $this->assertDatabaseMissing('programmes', ['id' => $programme->id]);
});

test('an officer can enrol a prisoner in a programme', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $prisoner = Prisoner::factory()->create();
    $programme = Programme::factory()->create();

    $response = $this->actingAs($officer)->postJson("/api/prisoners/{$prisoner->id}/programme-enrolments", [
        'programme_id' => $programme->id,
        'enrolled_at' => now()->toDateString(),
    ]);

    $response->assertCreated();
    $response->assertJsonPath('status', 'enrolled');
    $response->assertJsonPath('programme_name', $programme->name);
});

test('a supervisor cannot enrol a prisoner in a programme', function () {
    $supervisor = User::factory()->create(['role' => Role::Supervisor]);
    $prisoner = Prisoner::factory()->create();
    $programme = Programme::factory()->create();

    $response = $this->actingAs($supervisor)->postJson("/api/prisoners/{$prisoner->id}/programme-enrolments", [
        'programme_id' => $programme->id,
        'enrolled_at' => now()->toDateString(),
    ]);

    $response->assertForbidden();
});

test('a prisoner cannot be enrolled twice in the same programme while already enrolled', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $prisoner = Prisoner::factory()->create();
    $programme = Programme::factory()->create();
    ProgrammeEnrolment::factory()->for($programme)->for($prisoner)->create();

    $response = $this->actingAs($officer)->postJson("/api/prisoners/{$prisoner->id}/programme-enrolments", [
        'programme_id' => $programme->id,
        'enrolled_at' => now()->toDateString(),
    ]);

    $response->assertUnprocessable();
});

test('all three roles can view a prisoners programme enrolments', function () {
    $prisoner = Prisoner::factory()->create();
    ProgrammeEnrolment::factory()->for($prisoner)->create();

    foreach ([Role::Admin, Role::Officer, Role::Supervisor] as $role) {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)->getJson("/api/prisoners/{$prisoner->id}/programme-enrolments")->assertOk();
    }
});

test('an officer can record attendance for a session', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $enrolment = ProgrammeEnrolment::factory()->create();

    $response = $this->actingAs($officer)->postJson("/api/programme-enrolments/{$enrolment->id}/attendance", [
        'session_date' => now()->toDateString(),
        'attended' => true,
        'notes' => 'Engaged well with the session.',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('attended', true);
    $this->assertDatabaseHas('programme_attendances', ['programme_enrolment_id' => $enrolment->id]);
});

test('an officer can mark an enrolment as completed', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $enrolment = ProgrammeEnrolment::factory()->create();

    $response = $this->actingAs($officer)->postJson("/api/programme-enrolments/{$enrolment->id}/complete");

    $response->assertOk();
    $response->assertJsonPath('status', 'completed');
    expect($enrolment->fresh()->completed_at)->not->toBeNull();
});

test('an officer can withdraw an enrolment with a reason', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $enrolment = ProgrammeEnrolment::factory()->create();

    $response = $this->actingAs($officer)->postJson("/api/programme-enrolments/{$enrolment->id}/withdraw", [
        'reason' => 'Transferred to another facility.',
    ]);

    $response->assertOk();
    $response->assertJsonPath('status', 'withdrawn');
    $response->assertJsonPath('withdrawal_reason', 'Transferred to another facility.');
});

test('an already finalised enrolment cannot be completed again', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $enrolment = ProgrammeEnrolment::factory()->create(['status' => 'withdrawn']);

    $response = $this->actingAs($officer)->postJson("/api/programme-enrolments/{$enrolment->id}/complete");

    $response->assertUnprocessable();
});

test('guests cannot access programme endpoints', function () {
    $this->getJson('/api/programmes')->assertUnauthorized();
});
