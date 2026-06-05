<?php

use App\Models\Budget;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('department head can forward ticket to division head', function () {
    $deptHead = User::factory()->departmentHead()->create();
    $ticket   = Ticket::factory()->pendingDeptHead()->create();

    $this->actingAs($deptHead)
        ->post("/tickets/{$ticket->id}/forward", ['notes' => 'Disetujui untuk diteruskan']);

    expect($ticket->fresh()->status)->toBe(Ticket::STATUS_PENDING_DIV_HEAD);
});

test('division head can approve ticket — budget permanently deducted', function () {
    $divHead = User::factory()->divisionHead()->create();
    $ticket  = Ticket::factory()->pendingDivHead()->create([
        'category'         => 'infrastruktur_utama',
        'amount'           => 50_000_000,
        'expenditure_type' => 'OPEX',
    ]);

    $budget = Budget::factory()->opex()->forCategory('infrastruktur_utama')
        ->withLimit(1_000_000_000)
        ->create(['locked_amount' => 50_000_000]);

    $this->actingAs($divHead)
        ->post("/tickets/{$ticket->id}/decide", ['action' => 'approve']);

    expect($ticket->fresh()->status)->toBe(Ticket::STATUS_APPROVED);
    expect($budget->fresh()->locked_amount)->toBe('0.00');
    expect($budget->fresh()->used_amount)->toBe('50000000.00');
});

test('division head can decline ticket — temporary lock is released', function () {
    $divHead = User::factory()->divisionHead()->create();
    $ticket  = Ticket::factory()->pendingDivHead()->create([
        'category'         => 'lisensi_sistem',
        'amount'           => 100_000_000,
        'expenditure_type' => 'OPEX',
    ]);

    $budget = Budget::factory()->opex()->forCategory('lisensi_sistem')
        ->withLimit(1_000_000_000)
        ->create(['locked_amount' => 100_000_000]);

    $this->actingAs($divHead)
        ->post("/tickets/{$ticket->id}/decide", ['action' => 'decline', 'notes' => 'Tidak disetujui']);

    expect($ticket->fresh()->status)->toBe(Ticket::STATUS_DECLINED);
    expect($budget->fresh()->locked_amount)->toBe('0.00');  // Lock released
    expect($budget->fresh()->used_amount)->toBe('0.00');    // No deduction
});

test('department head cannot decline a ticket', function () {
    $deptHead = User::factory()->departmentHead()->create();
    $ticket   = Ticket::factory()->pendingDeptHead()->create();

    // DH has no access to the /decide route (division_head only)
    $this->actingAs($deptHead)
        ->post("/tickets/{$ticket->id}/decide", ['action' => 'decline'])
        ->assertForbidden();
});
