<?php

use App\Enums\Role;
use App\Models\MedicalAlert;
use App\Models\MedicalAppointment;
use App\Models\Prescription;
use App\Models\Prisoner;
use App\Models\User;

test('a medical officer can add a medical record', function () {
    $medical = User::factory()->create(['role' => Role::Medical]);
    $prisoner = Prisoner::factory()->create();

    $response = $this->actingAs($medical)->postJson("/api/prisoners/{$prisoner->id}/medical-records", [
        'condition' => 'Type 2 Diabetes',
        'notes' => 'Diagnosed 2024, managed with metformin.',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('condition', 'Type 2 Diabetes');
});

test('a correctional officer cannot view medical records', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $prisoner = Prisoner::factory()->create();

    $this->actingAs($officer)->getJson("/api/prisoners/{$prisoner->id}/medical-records")->assertForbidden();
});

test('a supervisor cannot view medical records', function () {
    $supervisor = User::factory()->create(['role' => Role::Supervisor]);
    $prisoner = Prisoner::factory()->create();

    $this->actingAs($supervisor)->getJson("/api/prisoners/{$prisoner->id}/medical-records")->assertForbidden();
});

test('an admin can view medical records', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $prisoner = Prisoner::factory()->create();

    $this->actingAs($admin)->getJson("/api/prisoners/{$prisoner->id}/medical-records")->assertOk();
});

test('a correctional officer cannot add a medical record', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $prisoner = Prisoner::factory()->create();

    $this->actingAs($officer)->postJson("/api/prisoners/{$prisoner->id}/medical-records", [
        'condition' => 'Type 2 Diabetes',
    ])->assertForbidden();
});

test('a medical officer can schedule and complete an appointment', function () {
    $medical = User::factory()->create(['role' => Role::Medical]);
    $prisoner = Prisoner::factory()->create();

    $response = $this->actingAs($medical)->postJson("/api/prisoners/{$prisoner->id}/medical-appointments", [
        'appointment_type' => 'GP review',
        'location' => 'Health Wing',
        'scheduled_at' => now()->addDay()->toIso8601String(),
    ]);

    $response->assertCreated();
    $appointmentId = $response->json('id');

    $completeResponse = $this->actingAs($medical)->postJson("/api/medical-appointments/{$appointmentId}/complete");
    $completeResponse->assertOk();
    $completeResponse->assertJsonPath('status', 'completed');
});

test('a finalised appointment cannot be completed again', function () {
    $medical = User::factory()->create(['role' => Role::Medical]);
    $appointment = MedicalAppointment::factory()->create();
    $this->actingAs($medical)->postJson("/api/medical-appointments/{$appointment->id}/cancel")->assertOk();

    $this->actingAs($medical)->postJson("/api/medical-appointments/{$appointment->id}/complete")->assertUnprocessable();
});

test('upcoming appointments only returns scheduled appointments in the future', function () {
    $medical = User::factory()->create(['role' => Role::Medical]);

    $future = MedicalAppointment::factory()->create(['scheduled_at' => now()->addWeek(), 'status' => 'scheduled']);
    MedicalAppointment::factory()->create(['scheduled_at' => now()->subWeek(), 'status' => 'scheduled']);
    MedicalAppointment::factory()->create(['scheduled_at' => now()->addWeek(), 'status' => 'cancelled']);

    $response = $this->actingAs($medical)->getJson('/api/medical-appointments/upcoming');

    $response->assertOk();
    $ids = collect($response->json())->pluck('id');
    expect($ids)->toHaveCount(1);
    expect($ids->first())->toBe($future->id);
});

test('a correctional officer cannot view medical appointments', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $prisoner = Prisoner::factory()->create();

    $this->actingAs($officer)->getJson("/api/prisoners/{$prisoner->id}/medical-appointments")->assertForbidden();
});

test('a medical officer can prescribe medication and discontinue it', function () {
    $medical = User::factory()->create(['role' => Role::Medical]);
    $prisoner = Prisoner::factory()->create();

    $response = $this->actingAs($medical)->postJson("/api/prisoners/{$prisoner->id}/prescriptions", [
        'medication_name' => 'Metformin',
        'dosage' => '500mg',
        'frequency' => 'Twice daily',
        'administration_time' => '14:00',
        'start_date' => now()->toDateString(),
    ]);

    $response->assertCreated();
    $response->assertJsonPath('status', 'active');
    $prescriptionId = $response->json('id');

    $discontinueResponse = $this->actingAs($medical)->postJson("/api/prescriptions/{$prescriptionId}/discontinue");
    $discontinueResponse->assertOk();
    $discontinueResponse->assertJsonPath('status', 'discontinued');
});

test('an already discontinued prescription cannot be discontinued again', function () {
    $medical = User::factory()->create(['role' => Role::Medical]);
    $prescription = Prescription::factory()->create(['status' => 'discontinued']);

    $this->actingAs($medical)->postJson("/api/prescriptions/{$prescription->id}/discontinue")->assertUnprocessable();
});

test('a correctional officer cannot view prescriptions', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $prisoner = Prisoner::factory()->create();

    $this->actingAs($officer)->getJson("/api/prisoners/{$prisoner->id}/prescriptions")->assertForbidden();
});

test('all four roles can view a prisoners active medical alerts', function () {
    $prisoner = Prisoner::factory()->create();
    MedicalAlert::factory()->for($prisoner)->create();

    foreach ([Role::Admin, Role::Officer, Role::Supervisor, Role::Medical] as $role) {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)->getJson("/api/prisoners/{$prisoner->id}/medical-alerts")->assertOk();
    }
});

test('a correctional officer cannot create a medical alert', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $prisoner = Prisoner::factory()->create();

    $this->actingAs($officer)->postJson("/api/prisoners/{$prisoner->id}/medical-alerts", [
        'message' => 'Requires medication at 14:00',
        'severity' => 'medium',
    ])->assertForbidden();
});

test('a medical officer can create and resolve a medical alert', function () {
    $medical = User::factory()->create(['role' => Role::Medical]);
    $prisoner = Prisoner::factory()->create();

    $response = $this->actingAs($medical)->postJson("/api/prisoners/{$prisoner->id}/medical-alerts", [
        'message' => 'Requires medication at 14:00',
        'severity' => 'medium',
    ]);

    $response->assertCreated();
    $alertId = $response->json('id');

    $resolveResponse = $this->actingAs($medical)->postJson("/api/medical-alerts/{$alertId}/resolve");
    $resolveResponse->assertOk();
    $resolveResponse->assertJsonPath('active', false);
});

test('a resolved alert no longer appears in the active alerts list', function () {
    $medical = User::factory()->create(['role' => Role::Medical]);
    $prisoner = Prisoner::factory()->create();
    $alert = MedicalAlert::factory()->for($prisoner)->create();

    $this->actingAs($medical)->postJson("/api/medical-alerts/{$alert->id}/resolve")->assertOk();

    $response = $this->actingAs($medical)->getJson("/api/prisoners/{$prisoner->id}/medical-alerts");
    expect(collect($response->json())->pluck('id'))->not->toContain($alert->id);
});

test('a medical officer can view a prisoners basic profile', function () {
    $medical = User::factory()->create(['role' => Role::Medical]);
    $prisoner = Prisoner::factory()->create();

    $this->actingAs($medical)->getJson("/api/prisoners/{$prisoner->id}")->assertOk();
});

test('a medical officer cannot view court cases, property, programmes, or release reviews', function () {
    $medical = User::factory()->create(['role' => Role::Medical]);
    $prisoner = Prisoner::factory()->create();

    $this->actingAs($medical)->getJson("/api/prisoners/{$prisoner->id}/court-cases")->assertForbidden();
    $this->actingAs($medical)->getJson("/api/prisoners/{$prisoner->id}/property")->assertForbidden();
    $this->actingAs($medical)->getJson("/api/prisoners/{$prisoner->id}/programme-enrolments")->assertForbidden();
    $this->actingAs($medical)->getJson("/api/prisoners/{$prisoner->id}/release-reviews")->assertForbidden();
});

test('guests cannot access medical endpoints', function () {
    $prisoner = Prisoner::factory()->create();

    $this->getJson("/api/prisoners/{$prisoner->id}/medical-records")->assertUnauthorized();
    $this->getJson("/api/prisoners/{$prisoner->id}/medical-alerts")->assertUnauthorized();
});
