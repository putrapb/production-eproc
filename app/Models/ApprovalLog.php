<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalLog extends Model
{
    use HasFactory;

    // Konstanta aksi

    const ACTION_SUBMITTED            = 'submitted';
    const ACTION_FOLLOWED_UP          = 'followed_up';       // TL terima doc
    const ACTION_REJECTED_DOCUMENT    = 'rejected_document'; // TL tolak doc
    const ACTION_EDITED               = 'edited';            // Requester perbarui form
    const ACTION_REVISED              = 'revised';           // Requester perbarui doc
    const ACTION_VALIDATED            = 'validated';         // Lolos validasi
    const ACTION_CROSS_FUND_REQUESTED = 'cross_fund_requested';
    const ACTION_FORWARDED            = 'forwarded';         // TL -> DeptHead
    const ACTION_APPROVED             = 'approved';          // DeptHead setuju
    const ACTION_DECLINED             = 'declined';          // DeptHead tolak
    const ACTION_PO_ISSUED            = 'po_issued';         // (Data lama) PFA buat PO
    const ACTION_FORM_ISSUED          = 'form_issued';       // TL terbitkan form

    protected $fillable = [
        'ticket_id',
        'user_id',
        'action',
        'notes',
    ];

    /**
     * Log audit bersifat immutable (tidak boleh diubah/dihapus demi kepatuhan PSAK)
     */
    protected static function booted(): void
    {
        static::updating(function () {
            throw new \LogicException('ApprovalLog bersifat immutable. Entri audit trail tidak dapat dimodifikasi setelah dibuat.');
        });

        static::deleting(function () {
            throw new \LogicException('ApprovalLog bersifat immutable. Entri audit trail tidak dapat dihapus.');
        });
    }

    // Relasi

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    // Display Helpers

    /**
     * Get human-readable action label (Bahasa Indonesia).
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_SUBMITTED            => 'Tiket diajukan',
            self::ACTION_FOLLOWED_UP          => 'Dokumen diterima oleh Team Leader',
            self::ACTION_REJECTED_DOCUMENT    => 'Dokumen ditolak oleh Team Leader',
            self::ACTION_EDITED               => 'Tiket diedit oleh Requester',
            self::ACTION_REVISED              => 'Dokumen direvisi oleh Requester',
            self::ACTION_VALIDATED            => 'Smart Validation berhasil',
            self::ACTION_CROSS_FUND_REQUESTED => 'Silang dana diajukan',
            self::ACTION_FORWARDED            => 'Diteruskan ke Department Head',
            self::ACTION_APPROVED             => 'Pengadaan disetujui',
            self::ACTION_DECLINED             => 'Pengadaan ditolak',
            self::ACTION_PO_ISSUED            => 'Purchase Order diterbitkan',
            self::ACTION_FORM_ISSUED          => 'Form Pengadaan diterbitkan',
            default                           => $this->action,
        };
    }
}
