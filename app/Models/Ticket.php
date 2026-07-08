<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\TicketItem;

class Ticket extends Model
{
    use HasFactory;

    // ─────────────────────────────────────────────
    // Status Constants
    // ─────────────────────────────────────────────

    const STATUS_PENDING_REVIEW    = 'pending_review';
    const STATUS_REVISION          = 'revision';
    const STATUS_NEED_TO_VALIDATE  = 'need_to_validate';
    const STATUS_PENDING_DEPT_HEAD = 'pending_dept_head';  // Menunggu Department Head (decision maker)
    const STATUS_DECLINED          = 'declined';
    const STATUS_APPROVED          = 'approved';
    const STATUS_FORM_GENERATED    = 'form_generated';     // Form pengadaan diterbitkan oleh Team Leader

    // ─────────────────────────────────────────────
    // Category Constants
    // ─────────────────────────────────────────────

    // Asset class categories (4 corporate standard classes)
    const CATEGORY_INFRASTRUKTUR_UTAMA      = 'infrastruktur_utama';      // Server, network, devices
    const CATEGORY_LISENSI_SISTEM           = 'lisensi_sistem';           // Software licenses, SaaS
    const CATEGORY_LAYANAN_PEMELIHARAAN     = 'layanan_pemeliharaan';     // Maintenance, managed services
    const CATEGORY_PERLENGKAPAN_OPERASIONAL = 'perlengkapan_operasional'; // Stationery, operational supplies

    // ─────────────────────────────────────────────
    // Expenditure Type Constants
    // ─────────────────────────────────────────────

    const TYPE_CAPEX = 'CAPEX';
    const TYPE_OPEX  = 'OPEX';

    protected $fillable = [
        'user_id',
        'title',
        'category',
        'description',
        'pic_name',
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
            'pic_name'      => 'array',
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

    public function documents(): HasMany
    {
        return $this->hasMany(TicketDocument::class, 'ticket_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TicketItem::class, 'ticket_id')->orderBy('id');
    }

    // ─────────────────────────────────────────────
    // Query Scopes
    // ─────────────────────────────────────────────

    /** Tiket milik requester tertentu */
    public function scopeForRequester($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /** Tiket yang antri di Team Leader untuk cek dokumen (pending_review) */
    public function scopePendingReview($query)
    {
        return $query->where('status', self::STATUS_PENDING_REVIEW);
    }

    /** Tiket yang siap divalidasi oleh Requester */
    public function scopeNeedToValidate($query)
    {
        return $query->where('status', self::STATUS_NEED_TO_VALIDATE);
    }

    /** Tiket di antrian Department Head (decision maker) */
    public function scopePendingDeptHead($query)
    {
        return $query->where('status', self::STATUS_PENDING_DEPT_HEAD);
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
            // Team Leader: cek dokumen (pending_review) + generate form (approved) + arsip (form_generated)
            'team_leader'     => $query->whereIn('status', [
                self::STATUS_PENDING_REVIEW,
                self::STATUS_APPROVED,
                self::STATUS_DECLINED,
                self::STATUS_FORM_GENERATED,
            ]),
            // Department Head: keputusan final (pending_dept_head) + arsip
            'department_head' => $query->whereIn('status', [
                self::STATUS_PENDING_DEPT_HEAD,
                self::STATUS_APPROVED,
                self::STATUS_DECLINED,
                self::STATUS_FORM_GENERATED,
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

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isDeclined(): bool
    {
        return $this->status === self::STATUS_DECLINED;
    }

    public function isFormGenerated(): bool
    {
        return $this->status === self::STATUS_FORM_GENERATED;
    }

    /** @deprecated Use isFormGenerated() — kept for backward compatibility with old data */
    public function isPoGenerated(): bool
    {
        return $this->isFormGenerated();
    }

    /**
     * Get human-readable status label (Bahasa Indonesia).
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_REVIEW    => 'Menunggu Cek Dokumen',
            self::STATUS_REVISION          => 'Revisi Dokumen',
            self::STATUS_NEED_TO_VALIDATE  => 'Perlu Validasi',
            self::STATUS_PENDING_DEPT_HEAD => 'Menunggu Dept Head',
            self::STATUS_DECLINED          => 'Ditolak',
            self::STATUS_APPROVED          => 'Disetujui',
            self::STATUS_FORM_GENERATED    => 'Form Diterbitkan',
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
            self::STATUS_PENDING_DEPT_HEAD => 'blue',
            self::STATUS_REVISION          => 'yellow',
            self::STATUS_DECLINED          => 'red',
            self::STATUS_APPROVED,
            self::STATUS_FORM_GENERATED    => 'green',
            default                        => 'gray',
        };
    }

    /**
     * Get who currently "holds the ball" for this ticket.
     * Returns the role label of who needs to act next.
     */
    public function getBallHolderAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_REVIEW    => 'Team Leader',
            self::STATUS_REVISION          => 'Requester',
            self::STATUS_NEED_TO_VALIDATE  => 'Team Leader',  // Auto SmartVal runs on TL review
            self::STATUS_PENDING_DEPT_HEAD => 'Dept Head',
            self::STATUS_APPROVED          => 'Team Leader',  // TL must generate form
            default                        => '',              // Final states (declined, form_generated)
        };
    }

    /**
     * Get formatted amount in Rupiah.
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    /**
     * Get total amount — sum of all ticket_items subtotals.
     * Falls back to legacy (amount * quantity) if items relation not loaded.
     */
    public function getTotalAmountAttribute(): float
    {
        if ($this->relationLoaded('items') && $this->items->isNotEmpty()) {
            return (float) $this->items->sum('subtotal');
        }

        // Fallback: load items and sum (avoids N+1 if called without eager-load)
        if ($this->exists) {
            $sum = $this->items()->sum('subtotal');
            if ($sum > 0) {
                return (float) $sum;
            }
        }

        // Legacy fallback for old data without ticket_items
        return (float) ($this->attributes['amount'] ?? 0) * ($this->attributes['quantity'] ?? 1);
    }

    /**
     * Get formatted total amount in Rupiah.
     */
    public function getFormattedTotalAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

    // ─────────────────────────────────────────────
    // Column Alias Accessors (for template readability)
    // ─────────────────────────────────────────────

    public function getIzinPrinsipPathAttribute(): ?string
    {
        return $this->attributes['document_path'] ?? null;
    }

    public function getPoPathAttribute(): ?string
    {
        return $this->attributes['document_po_path'] ?? null;
    }

    public function getValidatedAtAttribute(): ?\Illuminate\Support\Carbon
    {
        // Use the approval log timestamp when ticket was validated
        $log = $this->approvalLogs()->where('action', 'validated')->first();
        return $log?->created_at;
    }

    public function getFormGeneratedAtAttribute(): ?\Illuminate\Support\Carbon
    {
        $log = $this->approvalLogs()->whereIn('action', ['form_issued', 'po_generated'])->first();
        return $log?->created_at;
    }

    /** @deprecated Use getFormGeneratedAtAttribute — kept for backward compat */
    public function getPoGeneratedAtAttribute(): ?\Illuminate\Support\Carbon
    {
        return $this->form_generated_at;
    }
}
