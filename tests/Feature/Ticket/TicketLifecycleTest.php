<?php

use App\Models\ApprovalLog;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
});

test('requester can create a ticket which starts at pending_review', function () {
    $requester = User::factory()->requester()->create();

    $response = $this->actingAs($requester)->post('/tickets', [
        'title'       => 'Pengadaan Server Rack',
        'item_name'   => 'Server Rack Dell PowerEdge',
        'category'    => 'infrastruktur_utama',
        'description' => 'Server rack untuk data center',
        'quantity'    => 2,
        'vendor_name' => 'PT Dell Indonesia',
        'amount'      => 350_000_000,
        'izin_prinsip' => UploadedFile::fake()->create('izin_prinsip.pdf', 500, 'application/pdf'),
    ]);

    $response->assertRedirect(route('tickets.index'));
    expect(Ticket::where('status', Ticket::STATUS_PENDING_REVIEW)->count())->toBe(1);
    expect(ApprovalLog::where('action', ApprovalLog::ACTION_SUBMITTED)->count())->toBe(1);
});

test('PFA can accept document — ticket moves to need_to_validate', function () {
    $pfa    = User::factory()->pfa()->create();
    $ticket = Ticket::factory()->pendingReview()->create();

    $this->actingAs($pfa)
        ->post("/tickets/{$ticket->id}/review", ['action' => 'accept']);

    expect($ticket->fresh()->status)->toBe(Ticket::STATUS_NEED_TO_VALIDATE);
    expect(ApprovalLog::where('ticket_id', $ticket->id)
        ->where('action', ApprovalLog::ACTION_FOLLOWED_UP)->count())->toBe(1);
});

test('PFA can reject document — ticket moves to revision', function () {
    $pfa    = User::factory()->pfa()->create();
    $ticket = Ticket::factory()->pendingReview()->create();

    $this->actingAs($pfa)
        ->post("/tickets/{$ticket->id}/review", ['action' => 'reject', 'notes' => 'Dokumen tidak lengkap']);

    expect($ticket->fresh()->status)->toBe(Ticket::STATUS_REVISION);
    expect(ApprovalLog::where('action', ApprovalLog::ACTION_REJECTED_DOCUMENT)->count())->toBe(1);
});

test('requester can re-upload document and ticket returns to pending_review', function () {
    $requester = User::factory()->requester()->create();
    $ticket    = Ticket::factory()->revision()->create(['user_id' => $requester->id]);

    $this->actingAs($requester)->put("/tickets/{$ticket->id}", [
        'izin_prinsip' => UploadedFile::fake()->create('izin_prinsip_v2.pdf', 200, 'application/pdf'),
    ]);

    expect($ticket->fresh()->status)->toBe(Ticket::STATUS_PENDING_REVIEW);
    expect(ApprovalLog::where('action', ApprovalLog::ACTION_REVISED)->count())->toBe(1);
});

test('non-requester cannot create a ticket', function () {
    foreach (['pfa', 'departmentHead', 'divisionHead'] as $role) {
        $user = User::factory()->$role()->create();
        $this->actingAs($user)->get('/tickets/create')->assertForbidden();
    }
});
