<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $user = App\Models\User::where('role', 'requester')->first();
    auth()->login($user);
    $totalAmount = 1 * 10000000;
    
    $ticket = App\Models\Ticket::create([
        'user_id'            => $user->id,
        'title'              => 'Test Ticket 500',
        'item_name'          => 'Test Item',
        'quantity'           => 1,
        'category'           => 'layanan_pemeliharaan',
        'description'        => 'Test Description',
        'pic_name'           => ['PIC 1'],
        'vendor_name'        => 'PT Test',
        'amount'             => $totalAmount,
        'status'             => App\Models\Ticket::STATUS_PENDING_REVIEW,
        'pending_with_role'  => 'team_leader',
    ]);
    
    echo "Ticket created successfully: {$ticket->id}\n";
    $ticket->forceDelete();
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
