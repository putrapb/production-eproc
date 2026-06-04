<?php

use App\Models\Budget;
use App\Models\Ticket;
use App\Models\User;
use App\Services\SmartValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─── Gate 1: Duplicate Check ───

test('gate 1 halts when identical active ticket exists', function () {
    $requester = User::factory()->requester()->create();
    $existing  = Ticket::factory()->pendingReview()->create([
        'user_id'   => $requester->id,
        'item_name' => 'Server Rack Dell PowerEdge',
    ]);

    // Ticket to validate — same item name, same requester
    $ticket = Ticket::factory()->needToValidate()->create([
        'user_id'   => $requester->id,
        'item_name' => 'Server Rack Dell PowerEdge',
    ]);

    $result = app(SmartValidationService::class)->run($ticket, $requester);

    expect($result['success'])->toBeFalse();
    expect($result['gate'])->toBe(1);
    expect($ticket->fresh()->status)->toBe(Ticket::STATUS_NEED_TO_VALIDATE);
});

test('gate 1 passes when only declined duplicates exist', function () {
    $requester = User::factory()->requester()->create();

    // Declined ticket with same item — should be ignored by Gate 1
    Ticket::factory()->declined()->create([
        'user_id'   => $requester->id,
        'item_name' => 'Server Rack Dell PowerEdge',
        'expenditure_type' => 'OPEX',
    ]);

    $ticket = Ticket::factory()->needToValidate()->create([
        'user_id'   => $requester->id,
        'item_name' => 'Server Rack Dell PowerEdge',
        'category'  => 'services',
        'amount'    => 5_000_000,
    ]);

    Budget::factory()->opex()->forCategory('services')->withLimit(1_000_000_000)->create();

    $result = app(SmartValidationService::class)->run($ticket, $requester);

    // Should pass gate 1 (no active duplicate)
    expect($result['gate'])->not->toBe(1);
});

// ─── Gate 2: Nominal Validation ───

test('gate 2 halts when amount is zero', function () {
    $requester = User::factory()->requester()->create();
    $ticket    = Ticket::factory()->needToValidate()->create([
        'user_id' => $requester->id,
        'amount'  => 0,
    ]);

    $result = app(SmartValidationService::class)->run($ticket, $requester);

    expect($result['success'])->toBeFalse();
    expect($result['gate'])->toBe(2);
});

test('gate 2 halts when amount is unreasonably large', function () {
    $requester = User::factory()->requester()->create();
    $ticket    = Ticket::factory()->needToValidate()->create([
        'user_id' => $requester->id,
        'amount'  => 100_000_000_000, // 100 billion
    ]);

    $result = app(SmartValidationService::class)->run($ticket, $requester);

    expect($result['success'])->toBeFalse();
    expect($result['gate'])->toBe(2);
});

// ─── Gate 3: CAPEX/OPEX Classification ───

test('gate 3 classifies hardware above threshold as CAPEX', function () {
    $requester = User::factory()->requester()->create();
    $ticket    = Ticket::factory()->needToValidate()->create([
        'user_id'  => $requester->id,
        'category' => 'hardware',
        'amount'   => 350_000_000, // Above 200M threshold
    ]);

    Budget::factory()->capex()->forCategory('hardware')->withLimit(2_000_000_000)->create();

    $result = app(SmartValidationService::class)->run($ticket, $requester);

    expect($ticket->fresh()->expenditure_type)->toBe('CAPEX');
});

test('gate 3 classifies hardware below threshold as OPEX', function () {
    $requester = User::factory()->requester()->create();
    $ticket    = Ticket::factory()->needToValidate()->create([
        'user_id'  => $requester->id,
        'category' => 'hardware',
        'amount'   => 50_000_000, // Below 200M threshold
    ]);

    Budget::factory()->opex()->forCategory('hardware')->withLimit(2_000_000_000)->create();

    $result = app(SmartValidationService::class)->run($ticket, $requester);

    expect($ticket->fresh()->expenditure_type)->toBe('OPEX');
});

test('gate 3 always classifies services as OPEX', function () {
    $requester = User::factory()->requester()->create();
    $ticket    = Ticket::factory()->needToValidate()->create([
        'user_id'  => $requester->id,
        'category' => 'services',
        'amount'   => 500_000_000, // High amount, but services → always OPEX
    ]);

    Budget::factory()->opex()->forCategory('services')->withLimit(2_000_000_000)->create();

    $result = app(SmartValidationService::class)->run($ticket, $requester);

    expect($ticket->fresh()->expenditure_type)->toBe('OPEX');
});

// ─── Gate 4: Budget Availability ───

test('gate 4 locks budget and advances ticket when balance is sufficient', function () {
    $requester = User::factory()->requester()->create();
    $ticket    = Ticket::factory()->needToValidate()->create([
        'user_id'  => $requester->id,
        'category' => 'software',
        'amount'   => 50_000_000, // Below CAPEX threshold → OPEX
    ]);

    $budget = Budget::factory()->opex()->forCategory('software')
        ->withLimit(1_000_000_000)->create();

    $result = app(SmartValidationService::class)->run($ticket, $requester);

    expect($result['success'])->toBeTrue();
    expect($ticket->fresh()->status)->toBe(Ticket::STATUS_PENDING_DEPT_HEAD);
    expect($budget->fresh()->locked_amount)->toBe('50000000.00');
});

test('gate 4 returns over_budget when balance is insufficient', function () {
    $requester = User::factory()->requester()->create();
    $ticket    = Ticket::factory()->needToValidate()->create([
        'user_id'  => $requester->id,
        'category' => 'software',
        'amount'   => 950_000_000,
    ]);

    Budget::factory()->opex()->forCategory('software')->almostExhausted()->create();
    // Only 20M remaining, ticket needs 950M

    $result = app(SmartValidationService::class)->run($ticket, $requester);

    expect($result['success'])->toBeFalse();
    expect($result['over_budget'])->toBeTrue();
    expect($ticket->fresh()->status)->toBe(Ticket::STATUS_NEED_TO_VALIDATE); // No status change
});
