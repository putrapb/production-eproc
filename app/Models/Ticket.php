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

    // Konstanta status

    const STATUS_PENDING_REVIEW    = 'pending_review';
    const STATUS_REVISION          = 'revision';
    const STATUS_NEED_TO_VALIDATE  = 'need_to_validate';
    const STATUS_PENDING_DEPT_HEAD = 'pending_dept_head';  // menunggu dept head
    const STATUS_DECLINED          = 'declined';
    const STATUS_APPROVED          = 'approved';
    const STATUS_FORM_GENERATED    = 'form_generated';     // form diterbitkan TL

    // Kategori aset

    // Klasifikasi standar perusahaan
    const CATEGORY_INFRASTRUKTUR_UTAMA      = 'infrastruktur_utama';      // hardware/jaringan
    const CATEGORY_LISENSI_SISTEM           = 'lisensi_sistem';           // lisensi/SaaS
    const CATEGORY_LAYANAN_PEMELIHARAAN     = 'layanan_pemeliharaan';     // jasa/maintenance
    const CATEGORY_PERLENGKAPAN_OPERASIONAL = 'perlengkapan_operasional'; // ATK/operasional

    // Tipe pengeluaran

    const TYPE_CAPEX = 'CAPEX';
    const TYPE_OPEX  = 'OPEX';
    // Field sensitif yang tidak boleh di-mass-assign langsung
    protected $guarded = [
        'id',
        'is_cross_fund',   // diatur oleh internal silang dana
        'form_path',       // diatur otomatis oleh generator
    ];

    protected function casts(): array
    {
        return [
            'amount'        => 'decimal:2',
            'is_cross_fund' => 'boolean',
            'pic_name'      => 'array',
        ];
    }

    // Relasi

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pendingWithUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pending_with_user_id');
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

    // Query scope

    /** Tiket milik requester tertentu */
    public function scopeForRequester($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /** Filter tiket yang pending di role tertentu */
    public function scopePendingWith($query, string $role)
    {
        return $query->where('pending_with_role', $role);
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

    /** Filter tiket yang sesuai porsi role */
    public function scopeForRole($query, User $user)
    {
        return match ($user->role) {
            'requester'       => $query->where('user_id', $user->id),
            // TL melihat tiket aksi dan arsip
            'team_leader'     => $query->whereIn('status', [
                self::STATUS_PENDING_REVIEW,
                self::STATUS_APPROVED,
                self::STATUS_DECLINED,
                self::STATUS_FORM_GENERATED,
            ]),
            // DH melihat tiket keputusan dan arsip
            'department_head' => $query->whereIn('status', [
                self::STATUS_PENDING_DEPT_HEAD,
                self::STATUS_APPROVED,
                self::STATUS_DECLINED,
                self::STATUS_FORM_GENERATED,
            ]),
            default           => $query,
        };
    }

    // Helper status

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

    /** Kompatibilitas data lama */
    public function isPoGenerated(): bool
    {
        return $this->isFormGenerated();
    }

    /** Label status untuk UI */
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

    /** Warna badge status di UI */
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

    /** Siapa yang memegang kendali tiket (pemegang bola) */
    public function getBallHolderAttribute(): string
    {
        if ($this->pending_with_role) {
            return match ($this->pending_with_role) {
                'requester'       => 'Requester',
                'team_leader'     => 'Team Leader',
                'department_head' => 'Dept Head',
                'division_head'   => 'Div Head',
                'none'            => '',
                default           => $this->pending_with_role,
            };
        }

        return match ($this->status) {
            self::STATUS_PENDING_REVIEW    => 'Team Leader',
            self::STATUS_REVISION          => 'Requester',
            self::STATUS_NEED_TO_VALIDATE  => 'Team Leader',
            self::STATUS_PENDING_DEPT_HEAD => 'Dept Head',
            self::STATUS_APPROVED          => 'Team Leader',
            default                        => '',
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

    // Column Alias Accessors (for template readability)

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
