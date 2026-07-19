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

    // Relasi

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    // Helper

    /**
     * Format rupiah subtotal
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }

    /**
     * Format rupiah harga satuan
     */
    public function getFormattedUnitPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->unit_price, 0, ',', '.');
    }

    /**
     * Ambil tipe pengeluaran (cek item dulu, jika kosong pakai tipe tiket induk)
     */
    public function getEffectiveExpenditureTypeAttribute(): ?string
    {
        if ($this->expenditure_type) {
            return $this->expenditure_type;
        }

        // Ambil dari tiket induk (hindari N+1 query jika sudah eager loaded)
        return $this->ticket?->expenditure_type;
    }
}
