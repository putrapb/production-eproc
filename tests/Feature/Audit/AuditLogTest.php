<?php

use App\Models\ApprovalLog;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('unauthenticated user is redirected to login from audit logs page', function () {
    $response = $this->get('/audit-logs');

    $response->assertRedirect(route('login'));
});

test('requester role is denied access to audit logs page', function () {
    $user = User::factory()->create(['role' => 'requester']);

    $response = $this->actingAs($user)->get('/audit-logs');

    $response->assertStatus(403);
});

test('team leader role can access audit logs page', function () {
    $user = User::factory()->create(['role' => 'team_leader']);

    $response = $this->actingAs($user)->get('/audit-logs');

    $response->assertStatus(200);
});

test('department head role can access audit logs page', function () {
    $user = User::factory()->create(['role' => 'department_head']);

    $response = $this->actingAs($user)->get('/audit-logs');

    $response->assertStatus(200);
});

test('audit logs list is searchable and filterable', function () {
    $tl = User::factory()->create(['role' => 'team_leader']);
    
    // Create tickets
    $ticketA = Ticket::factory()->create(['title' => 'Server Procurement A']);
    $ticketB = Ticket::factory()->create(['title' => 'Software License B']);

    // Create logs
    ApprovalLog::create([
        'ticket_id' => $ticketA->id,
        'user_id' => $tl->id,
        'action' => ApprovalLog::ACTION_SUBMITTED,
        'notes' => 'Submission notes',
    ]);

    ApprovalLog::create([
        'ticket_id' => $ticketB->id,
        'user_id' => $tl->id,
        'action' => ApprovalLog::ACTION_APPROVED,
        'notes' => 'Approval notes',
    ]);

    // Test Search by title
    $response = $this->actingAs($tl)->get('/audit-logs?search=Server');
    $response->assertStatus(200);
    $response->assertSee('Server Procurement A');
    $response->assertDontSee('Software License B');

    // Test Search by code (padded ID)
    $response = $this->actingAs($tl)->get('/audit-logs?search=' . str_pad($ticketB->id, 4, '0', STR_PAD_LEFT));
    $response->assertStatus(200);
    $response->assertSee('Software License B');
    $response->assertDontSee('Server Procurement A');

    // Test Filter by Action
    $response = $this->actingAs($tl)->get('/audit-logs?action=' . ApprovalLog::ACTION_APPROVED);
    $response->assertStatus(200);
    $response->assertSee('Software License B');
    $response->assertDontSee('Server Procurement A');
});
