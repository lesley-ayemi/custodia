<?php

use App\Enums\Role;
use App\Models\Prisoner;
use App\Models\User;
use App\Models\Visit;
use App\Models\Visitor;
use App\Models\VisitRequest;

test('an officer can register a visitor', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);

    $response = $this->actingAs($officer)->postJson('/api/visitors', [
        'name' => 'Jamie Rivers',
        'date_of_birth' => '1990-04-12',
        'id_type' => 'passport',
        'id_number' => 'X1234567',
        'phone' => '07700 900123',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('name', 'Jamie Rivers');
});

test('a supervisor cannot register a visitor', function () {
    $supervisor = User::factory()->create(['role' => Role::Supervisor]);

    $response = $this->actingAs($supervisor)->postJson('/api/visitors', [
        'name' => 'Jamie Rivers',
        'date_of_birth' => '1990-04-12',
        'id_type' => 'passport',
        'id_number' => 'X1234567',
        'phone' => '07700 900123',
    ]);

    $response->assertForbidden();
});

test('all three roles can view visitors', function () {
    Visitor::factory()->create();

    foreach ([Role::Admin, Role::Officer, Role::Supervisor] as $role) {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)->getJson('/api/visitors')->assertOk();
    }
});

test('an officer can request a visit', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $visitor = Visitor::factory()->create();
    $prisoner = Prisoner::factory()->create();

    $response = $this->actingAs($officer)->postJson('/api/visit-requests', [
        'visitor_id' => $visitor->id,
        'prisoner_id' => $prisoner->id,
        'relationship' => 'Spouse',
        'requested_visit_date' => now()->addWeek()->toDateString(),
    ]);

    $response->assertCreated();
    $response->assertJsonPath('status', 'pending');
});

test('a supervisor cannot request a visit', function () {
    $supervisor = User::factory()->create(['role' => Role::Supervisor]);
    $visitor = Visitor::factory()->create();
    $prisoner = Prisoner::factory()->create();

    $response = $this->actingAs($supervisor)->postJson('/api/visit-requests', [
        'visitor_id' => $visitor->id,
        'prisoner_id' => $prisoner->id,
        'relationship' => 'Spouse',
        'requested_visit_date' => now()->addWeek()->toDateString(),
    ]);

    $response->assertForbidden();
});

test('an officer cannot approve a visit request', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $visitRequest = VisitRequest::factory()->create();

    $response = $this->actingAs($officer)->postJson("/api/visit-requests/{$visitRequest->id}/approve", [
        'scheduled_at' => now()->addWeek()->toIso8601String(),
    ]);

    $response->assertForbidden();
});

test('a supervisor can approve a visit request, which schedules a visit', function () {
    $supervisor = User::factory()->create(['role' => Role::Supervisor]);
    $visitRequest = VisitRequest::factory()->create();

    $response = $this->actingAs($supervisor)->postJson("/api/visit-requests/{$visitRequest->id}/approve", [
        'scheduled_at' => now()->addWeek()->toIso8601String(),
    ]);

    $response->assertOk();
    $response->assertJsonPath('status', 'approved');
    expect($response->json('visit'))->not->toBeNull();
    expect($response->json('visit.status'))->toBe('scheduled');
    $this->assertDatabaseHas('visits', ['visit_request_id' => $visitRequest->id, 'status' => 'scheduled']);
});

test('a banned visitors request cannot be approved', function () {
    $supervisor = User::factory()->create(['role' => Role::Supervisor]);
    $visitor = Visitor::factory()->create(['banned_at' => now(), 'ban_reason' => 'Attempted to smuggle contraband']);
    $visitRequest = VisitRequest::factory()->for($visitor)->create();

    $response = $this->actingAs($supervisor)->postJson("/api/visit-requests/{$visitRequest->id}/approve", [
        'scheduled_at' => now()->addWeek()->toIso8601String(),
    ]);

    $response->assertUnprocessable();
    $this->assertDatabaseMissing('visits', ['visit_request_id' => $visitRequest->id]);
});

test('a supervisor can reject a visit request with a reason', function () {
    $supervisor = User::factory()->create(['role' => Role::Supervisor]);
    $visitRequest = VisitRequest::factory()->create();

    $response = $this->actingAs($supervisor)->postJson("/api/visit-requests/{$visitRequest->id}/reject", [
        'reason' => 'Visitor identity could not be verified.',
    ]);

    $response->assertOk();
    $response->assertJsonPath('status', 'rejected');
    $response->assertJsonPath('rejection_reason', 'Visitor identity could not be verified.');
});

test('an already decided visit request cannot be approved again', function () {
    $supervisor = User::factory()->create(['role' => Role::Supervisor]);
    $visitRequest = VisitRequest::factory()->create(['status' => 'rejected']);

    $response = $this->actingAs($supervisor)->postJson("/api/visit-requests/{$visitRequest->id}/approve", [
        'scheduled_at' => now()->addWeek()->toIso8601String(),
    ]);

    $response->assertUnprocessable();
});

test('an officer can check a visitor in and out', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $visit = Visit::factory()->create();

    $checkIn = $this->actingAs($officer)->postJson("/api/visits/{$visit->id}/check-in");
    $checkIn->assertOk();
    $checkIn->assertJsonPath('status', 'checked_in');

    $checkOut = $this->actingAs($officer)->postJson("/api/visits/{$visit->id}/check-out", [
        'notes' => 'No issues.',
    ]);
    $checkOut->assertOk();
    $checkOut->assertJsonPath('status', 'completed');
    $checkOut->assertJsonPath('notes', 'No issues.');
});

test('a visit cannot be checked out before it is checked in', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $visit = Visit::factory()->create();

    $this->actingAs($officer)->postJson("/api/visits/{$visit->id}/check-out")->assertUnprocessable();
});

test('an officer can cancel a scheduled visit but not a checked-in one', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $visit = Visit::factory()->create();

    $this->actingAs($officer)->postJson("/api/visits/{$visit->id}/check-in")->assertOk();
    $this->actingAs($officer)->postJson("/api/visits/{$visit->id}/cancel")->assertUnprocessable();

    $scheduledVisit = Visit::factory()->create();
    $this->actingAs($officer)->postJson("/api/visits/{$scheduledVisit->id}/cancel")->assertOk();
});

test('upcoming visits only returns scheduled visits in the future', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);

    $future = Visit::factory()->create(['scheduled_at' => now()->addWeek(), 'status' => 'scheduled']);
    Visit::factory()->create(['scheduled_at' => now()->subWeek(), 'status' => 'scheduled']);
    Visit::factory()->create(['scheduled_at' => now()->addWeek(), 'status' => 'cancelled']);

    $response = $this->actingAs($officer)->getJson('/api/visits/upcoming');

    $response->assertOk();
    $ids = collect($response->json())->pluck('id');
    expect($ids)->toHaveCount(1);
    expect($ids->first())->toBe($future->id);
});

test('guests cannot access visitor endpoints', function () {
    $this->getJson('/api/visitors')->assertUnauthorized();
    $this->getJson('/api/visits/upcoming')->assertUnauthorized();
});
