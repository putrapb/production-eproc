<?php

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─── Requester-only routes ───

test('non-requester cannot access create ticket route', function () {
    foreach (['teamLeader', 'departmentHead'] as $role) {
        $user = User::factory()->$role()->create();
        $this->actingAs($user)->get('/tickets/create')->assertForbidden();
    }
});

test('non-requester cannot post to tickets store route', function () {
    foreach (['teamLeader', 'departmentHead'] as $role) {
        $user = User::factory()->$role()->create();
        $this->actingAs($user)->post('/tickets', [])->assertForbidden();
    }
});

// ─── Team Leader-only routes ───

test('non-team-leader cannot review a ticket document', function () {
    foreach (['requester', 'departmentHead'] as $role) {
        $ticket = Ticket::factory()->pendingReview()->create();
        $user   = User::factory()->$role()->create();

        $this->actingAs($user)
            ->post("/tickets/{$ticket->id}/review", ['action' => 'accept'])
            ->assertForbidden();
    }
});

test('non-team-leader cannot generate form', function () {
    foreach (['requester', 'departmentHead'] as $role) {
        $ticket = Ticket::factory()->approved()->create();
        $user   = User::factory()->$role()->create();

        $this->actingAs($user)
            ->post("/tickets/{$ticket->id}/generate-form")
            ->assertForbidden();
    }
});

// ─── Department Head-only routes ───

test('non-department-head cannot decide on a ticket', function () {
    foreach (['requester', 'teamLeader'] as $role) {
        $ticket = Ticket::factory()->pendingDeptHead()->create();
        $user   = User::factory()->$role()->create();

        $this->actingAs($user)
            ->post("/tickets/{$ticket->id}/decide", ['action' => 'approve'])
            ->assertForbidden();
    }
});

// ─── Unauthenticated access ───

test('unauthenticated user is redirected to login', function () {
    $this->get('/dashboard')->assertRedirect(route('login'));
    $this->get('/tickets')->assertRedirect(route('login'));
});
