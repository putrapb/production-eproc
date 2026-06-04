<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    // ─────────────────────────────────────────────
    // Status Constants
    // ─────────────────────────────────────────────

    const STATUS_PENDING_REVIEW    = 'pending_review';
    const STATUS_REVISION          = 'revision';
    const STATUS_NEED_TO_VALIDATE  = 'need_to_validate';
    const STATUS_PENDING_DEPT_HEAD = 'pending_dept_head';
    const STATUS_PENDING_DIV_HEAD  = 'pending_div_head';
    const STATUS_DECLINED          = 'declined';
    const STATUS_APPROVED          = 'approved';
    const STATUS_PO_GENERATED      = 'po_generated';

    // ─────────────────────────────────────────────
    // Category Constants
    // ─────────────────────────────────────────────

    const CATEGORY_HARDWARE       = 'hardware';
    const CATEGORY_SOFTWARE       = 'software';
    const CATEGORY_SERVICES       = 'services';
    const CATEGORY_OFFICE_SUPPLIES = 'office_supplies';
    const CATEGORY_OTHERS         = 'others';

    // ─────────────────────────────────────────────
    // Expenditure Type Constants
    // ─────────────────────────────────────────────

    const TYPE_CAPEX = 'CAPEX';
    const TYPE_OPEX  = 'OPEX';

    protected $fillable = [
        'user_id',
        'title',
        'item_name',
        'category',
        'description',
        'quantity',
        'vendor_name',
        'amount',
        'expenditure_type',
        'document_path',
        'document_po_path',
        'status',
        'is_cross_fund',
    ];

    protected function casts(): array
    {
        return [
            'amount'        => 'decimal:2',
            'is_cross_fund' => 'boolean',
            'quantity'      => 'integer',
        ];
    }

    // ─────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approvalLogs(): HasMany
    {
        return $this->hasMany(ApprovalLog::class, 'ticket_id')->latest();
    }

    // ─────────────────────────────────────────────
    // Query Scopes
    // ─────────────────────────────────────────────

    /** Tiket milik requester tertentu */
    public function scopeForRequester($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /** Tiket yang antri di PFA (pending_review) */
    public function scopePendingReview($query)
    {
        return $query->where('status', self::STATUS_PENDING_REVIEW);
    }

    /** Tiket yang siap divalidasi oleh Requester */
    public function scopeNeedToValidate($query)
    {
        return $query->where('status', self::STATUS_NEED_TO_VALIDATE);
    }

    /** Tiket di antrian Department Head */
    public function scopePendingDeptHead($query)
    {
        return $query->where('status', self::STATUS_PENDING_DEPT_HEAD);
    }

    /** Tiket di antrian Division Head */
    public function scopePendingDivHead($query)
    {
        return $query->where('status', self::STATUS_PENDING_DIV_HEAD);
    }

    /** Tiket yang sudah disetujui (menunggu PO) */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /** Filter tiket yang relevan berdasarkan role pengguna */
    public function scopeForRole($query, User $user)
    {
        return match ($user->role) {
            'requester'       => $query->where('user_id', $user->id),
            'pfa'             => $query->whereIn('status', [
                self::STATUS_PENDING_REVIEW,
                self::STATUS_APPROVED,
                self::STATUS_PO_GENERATED,
            ]),
            'department_head' => $query->whereIn('status', [
                self::STATUS_PENDING_DEPT_HEAD,
                self::STATUS_PENDING_DIV_HEAD,
                self::STATUS_APPROVED,
                self::STATUS_DECLINED,
                self::STATUS_PO_GENERATED,
            ]),
            'division_head'   => $query->whereIn('status', [
                self::STATUS_PENDING_DIV_HEAD,
                self::STATUS_APPROVED,
                self::STATUS_DECLINED,
                self::STATUS_PO_GENERATED,
            ]),
            default           => $query,
        };
    }

    // ─────────────────────────────────────────────
    // Status Helpers
    // ─────────────────────────────────────────────

    public function isPendingReview(): bool
    {
        return $this->status === self::STATUS_PENDING_REVIEW;
    }

    public function isRevision(): bool
    {
        return $this->status === self::STATUS_REVISION;
    }

    public function isNeedToValidate(): bool
    {
        return $this->status === self::STATUS_NEED_TO_VALIDATE;
    }

    public function isPendingDeptHead(): bool
    {
        return $this->status === self::STATUS_PENDING_DEPT_HEAD;
    }

    public function isPendingDivHead(): bool
    {
        return $this->status === self::STATUS_PENDING_DIV_HEAD;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isDeclined(): bool
    {
        return $this->status === self::STATUS_DECLINED;
    }

    public function isPoGenerated(): bool
    {
        return $this->status === self::STATUS_PO_GENERATED;
    }

    /**
     * Get human-readable status label (Bahasa Indonesia).
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_REVIEW    => 'Menunggu Review',
            self::STATUS_REVISION          => 'Revisi',
            self::STATUS_NEED_TO_VALIDATE  => 'Perlu Validasi',
            self::STATUS_PENDING_DEPT_HEAD => 'Menunggu Dept Head',
            self::STATUS_PENDING_DIV_HEAD  => 'Menunggu Div Head',
            self::STATUS_DECLINED          => 'Ditolak',
            self::STATUS_APPROVED          => 'Disetujui',
            self::STATUS_PO_GENERATED      => 'PO Diterbitkan',
            default                        => $this->status,
        };
    }

    /**
     * Get semantic color class for status badge.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_REVIEW,
            self::STATUS_NEED_TO_VALIDATE,
            self::STATUS_PENDING_DEPT_HEAD,
            self::STATUS_PENDING_DIV_HEAD  => 'blue',
            self::STATUS_REVISION          => 'yellow',
            self::STATUS_DECLINED          => 'red',
            self::STATUS_APPROVED,
            self::STATUS_PO_GENERATED      => 'green',
            default                        => 'gray',
        };
    }

    /**
     * Get formatted amount in Rupiah.
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }
}
