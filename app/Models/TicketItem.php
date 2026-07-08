<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'item_name',
        'quantity',
        'unit_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity'   => 'integer',
            'unit_price' => 'decimal:2',
            'subtotal'   => 'decimal:2',
        ];
    }

    // ─────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    // ─────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────

    /**
     * Get subtotal formatted as Rupiah.
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }

    /**
     * Get unit_price formatted as Rupiah.
     */
    public function getFormattedUnitPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->unit_price, 0, ',', '.');
    }
}
