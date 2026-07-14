<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'expenditure_type',
        'category',
        'fiscal_year',
        'total_limit',
        'locked_amount',
        'used_amount',
    ];

    protected function casts(): array
    {
        return [
            'total_limit'    => 'decimal:2',
            'locked_amount'  => 'decimal:2',
            'used_amount'    => 'decimal:2',
            'fiscal_year'    => 'integer',
        ];
    }

    // ─────────────────────────────────────────────
    // Computed Properties
    // ─────────────────────────────────────────────

    /**
     * Available balance = total_limit - locked_amount - used_amount
     */
    public function getAvailableBalanceAttribute(): float
    {
        return (float) $this->total_limit
             - (float) $this->locked_amount
             - (float) $this->used_amount;
    }

    /**
     * Utilization percentage (used + locked) / total_limit * 100
     */
    public function getUtilizationPercentageAttribute(): float
    {
        if ((float) $this->total_limit === 0.0) {
            return 0.0;
        }

        return round(
            (((float) $this->used_amount + (float) $this->locked_amount) / (float) $this->total_limit) * 100,
            2
        );
    }

    // ─────────────────────────────────────────────
    // Query Scopes
    // ─────────────────────────────────────────────

    public function scopeForCurrentYear($query)
    {
        return $query->where('fiscal_year', now()->year);
    }

    public function scopeCapex($query)
    {
        return $query->where('expenditure_type', Ticket::TYPE_CAPEX);
    }

    public function scopeOpex($query)
    {
        return $query->where('expenditure_type', Ticket::TYPE_OPEX);
    }

    // ─────────────────────────────────────────────
    // Budget Operations
    // ─────────────────────────────────────────────

    /**
     * Find the budget record for a given expenditure type and category in current fiscal year.
     */
    public static function findForTicket(string $expenditureType, string $category, int $fiscalYear, bool $lock = true): ?self
    {
        $query = static::where('expenditure_type', $expenditureType)
            ->where('category', $category)
            ->where('fiscal_year', $fiscalYear);

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    /**
     * Temporarily lock budget amount (Gate 4 — budget reservation).
     */
    public function lock(float $amount): void
    {
        $this->increment('locked_amount', $amount);
    }

    /**
     * Release temporary lock (on decline or cancel).
     */
    public function unlock(float $amount): void
    {
        $this->decrement('locked_amount', $amount);
    }

    /**
     * Convert temporary lock to permanent deduction (on Division Head approval).
     */
    public function permanentDeduct(float $amount): void
    {
        $this->decrement('locked_amount', $amount);
        $this->increment('used_amount', $amount);
    }
}
