<?php

use App\Models\ApprovalLog;
use App\Models\Ticket;
use App\Models\TicketDocument;
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
        'title'            => 'Pengadaan Server Rack',
        'expenditure_type' => 'CAPEX',
        'category'         => 'infrastruktur_utama',
        'description'      => 'Server rack untuk data center',
        'pic_name'         => ['John Doe'],
        'vendor_name'      => 'PT Dell Indonesia',
        'items'            => [
            [
                'item_name'  => 'Server Rack Dell PowerEdge',
                'quantity'   => 2,
                'unit_price' => 175_000_000,
            ]
        ],
        'document_files' => [
            UploadedFile::fake()->create('izin_prinsip.pdf', 500, 'application/pdf')
        ],
        'document_descriptions' => [
            'Surat Izin Prinsip'
        ]
    ]);

    $response->assertRedirect();
    expect(Ticket::where('status', Ticket::STATUS_PENDING_REVIEW)->count())->toBe(1);
    expect(ApprovalLog::where('action', ApprovalLog::ACTION_SUBMITTED)->count())->toBe(1);
});

test('Team Leader can accept document — ticket moves to need_to_validate', function () {
    $tl     = User::factory()->teamLeader()->create();
    $ticket = Ticket::factory()->pendingReview()->create();
    
    $doc = TicketDocument::create([
        'ticket_id'   => $ticket->id,
        'file_path'   => 'izin_prinsip/test.pdf',
        'description' => 'Surat Izin Prinsip',
        'status'      => 'pending',
    ]);

    $this->actingAs($tl)
        ->post("/tickets/{$ticket->id}/review", [
            'document_status' => [
                $doc->id => 'accepted'
            ],
            'document_feedback' => [
                $doc->id => ''
            ],
            'notes' => 'Semua dokumen valid'
        ]);

    expect($ticket->fresh()->status)->toBe(Ticket::STATUS_NEED_TO_VALIDATE);
    expect(ApprovalLog::where('ticket_id', $ticket->id)
        ->where('action', ApprovalLog::ACTION_FOLLOWED_UP)->count())->toBe(1);
});

test('Team Leader can reject document — ticket moves to revision', function () {
    $tl     = User::factory()->teamLeader()->create();
    $ticket = Ticket::factory()->pendingReview()->create();

    $doc = TicketDocument::create([
        'ticket_id'   => $ticket->id,
        'file_path'   => 'izin_prinsip/test.pdf',
        'description' => 'Surat Izin Prinsip',
        'status'      => 'pending',
    ]);

    $this->actingAs($tl)
        ->post("/tickets/{$ticket->id}/review", [
            'document_status' => [
                $doc->id => 'rejected'
            ],
            'document_feedback' => [
                $doc->id => 'Dokumen tidak lengkap'
            ],
            'notes' => 'Perlu revisi dokumen'
        ]);

    expect($ticket->fresh()->status)->toBe(Ticket::STATUS_REVISION);
    expect(ApprovalLog::where('action', ApprovalLog::ACTION_REJECTED_DOCUMENT)->count())->toBe(1);
});

test('requester can re-upload document and ticket returns to pending_review', function () {
    $requester = User::factory()->requester()->create();
    $ticket    = Ticket::factory()->revision()->create(['user_id' => $requester->id]);

    $doc = TicketDocument::create([
        'ticket_id'   => $ticket->id,
        'file_path'   => 'izin_prinsip/test.pdf',
        'description' => 'Surat Izin Prinsip',
        'status'      => 'rejected',
    ]);

    $this->actingAs($requester)->put("/tickets/{$ticket->id}", [
        'title'            => 'Revised Pengadaan',
        'expenditure_type' => 'OPEX',
        'category'         => 'layanan_pemeliharaan',
        'description'      => 'Updated desc',
        'pic_name'         => ['John Doe'],
        'vendor_name'      => 'PT Vendor Baru',
        'items'            => [
            [
                'item_name'  => 'Layanan Cloud',
                'quantity'   => 1,
                'unit_price' => 50_000_000,
            ]
        ],
        'document_files' => [
            $doc->id => UploadedFile::fake()->create('izin_prinsip_v2.pdf', 200, 'application/pdf'),
        ]
    ]);

    expect($ticket->fresh()->status)->toBe(Ticket::STATUS_PENDING_REVIEW);
    expect(ApprovalLog::where('action', ApprovalLog::ACTION_REVISED)->count())->toBe(1);
});

test('non-requester cannot create a ticket', function () {
    foreach (['teamLeader', 'departmentHead'] as $role) {
        $user = User::factory()->$role()->create();
        $this->actingAs($user)->get('/tickets/create')->assertForbidden();
    }
});
