<?php

use App\Enums\PrisonerStatus;
use App\Enums\Role;
use App\Models\Block;
use App\Models\Cell;
use App\Models\Prisoner;
use App\Models\ReleaseReview;
use App\Models\User;

function makeReleaseCell(): Cell
{
    $block = Block::create(['name' => 'Release Test Block']);

    return $block->cells()->create(['code' => 'RTB-101', 'capacity' => 2]);
}

test('an officer can schedule a release review for a prisoner in custody', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $prisoner = Prisoner::factory()->create(['status' => PrisonerStatus::InCustody]);

    $response = $this->actingAs($officer)->postJson("/api/prisoners/{$prisoner->id}/release-reviews");

    $response->assertCreated();
    $response->assertJsonPath('status', 'in_progress');
    $response->assertJsonPath('next_step', 'legal_verification');
});

test('a prisoner cannot have two release reviews in progress at once', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $prisoner = Prisoner::factory()->create(['status' => PrisonerStatus::InCustody]);
    ReleaseReview::factory()->for($prisoner)->create();

    $response = $this->actingAs($officer)->postJson("/api/prisoners/{$prisoner->id}/release-reviews");

    $response->assertUnprocessable();
});

test('a release review cannot be scheduled for a prisoner who is not in custody', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $prisoner = Prisoner::factory()->create(['status' => PrisonerStatus::Transferred]);

    $response = $this->actingAs($officer)->postJson("/api/prisoners/{$prisoner->id}/release-reviews");

    $response->assertUnprocessable();
});

test('a supervisor cannot schedule a release review', function () {
    $supervisor = User::factory()->create(['role' => Role::Supervisor]);
    $prisoner = Prisoner::factory()->create(['status' => PrisonerStatus::InCustody]);

    $response = $this->actingAs($supervisor)->postJson("/api/prisoners/{$prisoner->id}/release-reviews");

    $response->assertForbidden();
});

test('an officer can complete the legal verification step first', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $review = ReleaseReview::factory()->create();

    $response = $this->actingAs($officer)->postJson("/api/release-reviews/{$review->id}/legal-verification");

    $response->assertOk();
    $response->assertJsonPath('next_step', 'sentence_verification');
});

test('steps cannot be completed out of order', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $review = ReleaseReview::factory()->create();

    $response = $this->actingAs($officer)->postJson("/api/release-reviews/{$review->id}/sentence-verification");

    $response->assertUnprocessable();
});

test('an officer cannot perform the supervisor approval step', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $review = ReleaseReview::factory()->create();
    $this->actingAs($officer)->postJson("/api/release-reviews/{$review->id}/legal-verification");
    $this->actingAs($officer)->postJson("/api/release-reviews/{$review->id}/sentence-verification");
    $this->actingAs($officer)->postJson("/api/release-reviews/{$review->id}/property-verification");
    $this->actingAs($officer)->postJson("/api/release-reviews/{$review->id}/documentation");

    $response = $this->actingAs($officer)->postJson("/api/release-reviews/{$review->id}/supervisor-approval");

    $response->assertForbidden();
});

test('completing all five steps in order releases the prisoner and vacates their cell', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $supervisor = User::factory()->create(['role' => Role::Supervisor]);
    $prisoner = Prisoner::factory()->create(['status' => PrisonerStatus::InCustody]);
    $cell = makeReleaseCell();
    $this->actingAs($officer)->postJson('/api/housing-assignments', ['prisoner_id' => $prisoner->id, 'cell_id' => $cell->id]);
    $review = ReleaseReview::factory()->for($prisoner)->create();

    $this->actingAs($officer)->postJson("/api/release-reviews/{$review->id}/legal-verification")->assertOk();
    $this->actingAs($officer)->postJson("/api/release-reviews/{$review->id}/sentence-verification")->assertOk();
    $this->actingAs($officer)->postJson("/api/release-reviews/{$review->id}/property-verification")->assertOk();
    $this->actingAs($officer)->postJson("/api/release-reviews/{$review->id}/documentation")->assertOk();
    $response = $this->actingAs($supervisor)->postJson("/api/release-reviews/{$review->id}/supervisor-approval");

    $response->assertOk();
    $response->assertJsonPath('status', 'released');
    expect($prisoner->fresh()->status)->toBe(PrisonerStatus::Released);
    expect($prisoner->fresh()->housingAssignments()->whereNull('ended_at')->exists())->toBeFalse();
});

test('a finalised release review cannot have further steps recorded', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $supervisor = User::factory()->create(['role' => Role::Supervisor]);
    $review = ReleaseReview::factory()->create();
    $this->actingAs($officer)->postJson("/api/release-reviews/{$review->id}/legal-verification");
    $this->actingAs($officer)->postJson("/api/release-reviews/{$review->id}/sentence-verification");
    $this->actingAs($officer)->postJson("/api/release-reviews/{$review->id}/property-verification");
    $this->actingAs($officer)->postJson("/api/release-reviews/{$review->id}/documentation");
    $this->actingAs($supervisor)->postJson("/api/release-reviews/{$review->id}/supervisor-approval");

    $response = $this->actingAs($officer)->postJson("/api/release-reviews/{$review->id}/legal-verification");

    $response->assertUnprocessable();
});

test('an admin can cancel an in-progress release review', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $review = ReleaseReview::factory()->create();

    $response = $this->actingAs($admin)->postJson("/api/release-reviews/{$review->id}/cancel", ['reason' => 'Release order rescinded.']);

    $response->assertOk();
    $response->assertJsonPath('status', 'cancelled');
});

test('an officer cannot cancel a release review', function () {
    $officer = User::factory()->create(['role' => Role::Officer]);
    $review = ReleaseReview::factory()->create();

    $response = $this->actingAs($officer)->postJson("/api/release-reviews/{$review->id}/cancel");

    $response->assertForbidden();
});

test('all three roles can view release reviews', function () {
    ReleaseReview::factory()->create();

    foreach ([Role::Admin, Role::Officer, Role::Supervisor] as $role) {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)->getJson('/api/release-reviews')->assertOk();
    }
});

test('guests cannot access release review endpoints', function () {
    $this->getJson('/api/release-reviews')->assertUnauthorized();
});
