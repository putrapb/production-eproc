<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalLog extends Model
{
    use HasFactory;

    // ─────────────────────────────────────────────
    // Action Constants
    // ─────────────────────────────────────────────

    const ACTION_SUBMITTED            = 'submitted';
    const ACTION_FOLLOWED_UP          = 'followed_up';       // PFA accept doc
    const ACTION_REJECTED_DOCUMENT    = 'rejected_document'; // PFA reject doc
    const ACTION_REVISED              = 'revised';           // Requester re-upload
    const ACTION_VALIDATED            = 'validated';         // Smart Validation pass
    const ACTION_CROSS_FUND_REQUESTED = 'cross_fund_requested';
    const ACTION_FORWARDED            = 'forwarded';         // Team Leader → DeptHead
    const ACTION_APPROVED             = 'approved';          // DeptHead approve
    const ACTION_DECLINED             = 'declined';          // DeptHead decline
    const ACTION_PO_ISSUED            = 'po_issued';         // PFA generate PO

    protected $fillable = [
        'ticket_id',
        'user_id',
        'action',
        'notes',
    ];

    // ─────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ─────────────────────────────────────────────
    // Display Helpers
    // ─────────────────────────────────────────────

    /**
     * Get human-readable action label (Bahasa Indonesia).
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_SUBMITTED            => 'Tiket diajukan',
            self::ACTION_FOLLOWED_UP          => 'Dokumen diterima oleh PFA',
            self::ACTION_REJECTED_DOCUMENT    => 'Dokumen ditolak oleh PFA',
            self::ACTION_REVISED              => 'Dokumen direvisi oleh Requester',
            self::ACTION_VALIDATED            => 'Smart Validation berhasil',
            self::ACTION_CROSS_FUND_REQUESTED => 'Silang dana diajukan',
            self::ACTION_FORWARDED            => 'Diteruskan ke Department Head',
            self::ACTION_APPROVED             => 'Pengadaan disetujui',
            self::ACTION_DECLINED             => 'Pengadaan ditolak',
            self::ACTION_PO_ISSUED            => 'Purchase Order diterbitkan',
            default                           => $this->action,
        };
    }
}
