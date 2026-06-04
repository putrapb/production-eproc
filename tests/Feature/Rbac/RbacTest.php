<?php

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─── Requester-only routes ───

test('non-requester cannot access create ticket route', function () {
    foreach (['pfa', 'departmentHead', 'divisionHead'] as $role) {
        $user = User::factory()->$role()->create();
        $this->actingAs($user)->get('/tickets/create')->assertForbidden();
    }
});

test('non-requester cannot post to tickets store route', function () {
    foreach (['pfa', 'departmentHead', 'divisionHead'] as $role) {
        $user = User::factory()->$role()->create();
        $this->actingAs($user)->post('/tickets', [])->assertForbidden();
    }
});

// ─── PFA-only routes ───

test('non-PFA cannot review a ticket document', function () {
    foreach (['requester', 'departmentHead', 'divisionHead'] as $role) {
        $ticket = Ticket::factory()->pendingReview()->create();
        $user   = User::factory()->$role()->create();

        $this->actingAs($user)
            ->post("/tickets/{$ticket->id}/review", ['action' => 'accept'])
            ->assertForbidden();
    }
});

test('non-PFA cannot generate PO', function () {
    foreach (['requester', 'departmentHead', 'divisionHead'] as $role) {
        $ticket = Ticket::factory()->approved()->create();
        $user   = User::factory()->$role()->create();

        $this->actingAs($user)
            ->post("/tickets/{$ticket->id}/generate-po")
            ->assertForbidden();
    }
});

// ─── Department Head-only routes ───

test('non-department-head cannot forward a ticket', function () {
    foreach (['requester', 'pfa', 'divisionHead'] as $role) {
        $ticket = Ticket::factory()->pendingDeptHead()->create();
        $user   = User::factory()->$role()->create();

        $this->actingAs($user)
            ->post("/tickets/{$ticket->id}/forward")
            ->assertForbidden();
    }
});

// ─── Division Head-only routes ───

test('non-division-head cannot decide on a ticket', function () {
    foreach (['requester', 'pfa', 'departmentHead'] as $role) {
        $ticket = Ticket::factory()->pendingDivHead()->create();
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
