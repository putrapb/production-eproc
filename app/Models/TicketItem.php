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
        'expenditure_type',
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

    /**
     * Get the effective expenditure_type for this item.
     *
     * If the item has its own per-item classification, use it.
     * Otherwise fall back to the parent ticket's type (for legacy items / null).
     */
    public function getEffectiveExpenditureTypeAttribute(): ?string
    {
        if ($this->expenditure_type) {
            return $this->expenditure_type;
        }

        // Avoid extra query if ticket is already loaded via eager-load
        return $this->ticket?->expenditure_type;
    }
}
