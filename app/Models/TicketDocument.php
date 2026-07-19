<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TicketDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'file_path',
        'description',
        'status',
        'feedback',
    ];

    // Relationships

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    // Accessors & Helpers

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'  => 'Menunggu Review',
            'accepted' => 'Disetujui',
            'rejected' => 'Perlu Revisi',
            default    => 'Tidak Diketahui',
        };
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }
}
