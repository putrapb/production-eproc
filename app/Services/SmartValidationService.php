<?php

namespace App\Services;

use App\Models\ApprovalLog;
use App\Models\Budget;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * SmartValidationService
 *
 * Executes the 4-Gate validation engine sequentially.
 * Each gate runs only if the previous gate passed.
 *
 * Gate 1 — Duplicate Check
 * Gate 2 — Nominal Validation
 * Gate 3 — CAPEX/OPEX Auto-Classification
 * Gate 4 — Budget Availability + Temporary Lock
 */
class SmartValidationService
{
    /**
     * Run the 4-gate validation engine.
     *
     * @return array{
     *   success: bool,
     *   gate: int,
     *   message: string,
     *   over_budget: bool,
     *   classified_type: string|null,
     *   available_balance: float|null,
     * }
     */
    public function run(Ticket $ticket, User $requester): array
    {
        // Guard: ticket must be in 'need_to_validate' status
        if (! $ticket->isNeedToValidate()) {
            return $this->fail(0, 'Tiket tidak dalam status yang dapat divalidasi.');
        }

        // ─────────────── GATE 1: Duplicate Check ───────────────
        $gate1 = $this->gate1DuplicateCheck($ticket, $requester);
        if (! $gate1['passed']) {
            return $this->fail(1, $gate1['message']);
        }

        // ─────────────── GATE 2: Nominal Validation ───────────────
        $gate2 = $this->gate2NominalValidation($ticket);
        if (! $gate2['passed']) {
            return $this->fail(2, $gate2['message']);
        }

        // ─────────────── GATE 3: CAPEX/OPEX Classification ───────────────
        $classifiedType = $this->gate3Classification($ticket);
        DB::transaction(function () use ($ticket, $classifiedType) {
            $ticket->update(['expenditure_type' => $classifiedType]);
        });

        // ─────────────── GATE 4: Monthly Budget Limit + Atomic Lock ───────────────
        //
        // Budget check uses a 3-tier monthly rule:
        //  - Tier 1: amount <= monthly_limit (annual/12) → pass normally
        //  - Tier 2: monthly_limit < amount <= monthly_limit * 1.30 → pass with tolerance (10–30% over)
        //  - Tier 3: amount > monthly_limit * 1.30 → mandatory cross-fund (> 30% over monthly limit)
        //
        // NOTE: CAPEX/OPEX classification (Gate 3, threshold Rp 200 juta) is SEPARATE
        // from this cross-fund monthly logic. Gate 3 only classifies; Gate 4 checks budget.
        $gate4Result = DB::transaction(function () use ($ticket, $classifiedType, $requester) {
            $budget = Budget::findForTicket(
                $classifiedType,
                $ticket->category,
                now()->year
            );

            if (! $budget) {
                return ['status' => 'over_budget', 'available_balance' => 0.0, 'committed' => false];
            }

            $available    = $budget->available_balance;
            $amount       = (float) $ticket->amount;
            $totalLimit   = (float) $budget->total_limit;

            // Monthly limit = annual budget / 12
            $monthlyLimit = $totalLimit > 0 ? $totalLimit / 12 : 0.0;

            // Tier 3: amount exceeds monthly_limit by more than 30% → mandatory cross-fund
            if ($monthlyLimit > 0 && $amount > $monthlyLimit * 1.30) {
                return [
                    'status'           => 'over_budget',
                    'available_balance' => $available,
                    'monthly_limit'    => $monthlyLimit,
                    'committed'        => false,
                ];
            }

            // Tier 1 & 2: amount within monthly_limit or within 10–30% tolerance
            // Still check annual available balance to prevent overspending the yearly budget
            if ($amount > $available) {
                return [
                    'status'           => 'over_budget',
                    'available_balance' => $available,
                    'monthly_limit'    => $monthlyLimit,
                    'committed'        => false,
                ];
            }

            // Saldo mencukupi dan dalam batas bulanan (atau toleransi 10–30%) — kunci saldo
            $budget->lock($amount);

            $ticket->update(['status' => Ticket::STATUS_PENDING_DEPT_HEAD]);

            $toleranceNote = ($monthlyLimit > 0 && $amount > $monthlyLimit)
                ? ' (Kelebihan pagu bulanan dalam batas toleransi 10–30%.)'
                : '';

            ApprovalLog::create([
                'ticket_id' => $ticket->id,
                'user_id'   => $requester->id,
                'action'    => ApprovalLog::ACTION_VALIDATED,
                'notes'     => "Klasifikasi: {$classifiedType}. Saldo dikunci sementara.{$toleranceNote}",
            ]);

            return ['status' => 'ok', 'available_balance' => $available, 'committed' => true];
        });

        if ($gate4Result['status'] === 'over_budget') {
            // Return over-budget info — caller will display cross-fund popup
            return [
                'success'           => false,
                'gate'              => 4,
                'message'           => 'Pengajuan melebihi batas anggaran bulanan (> 30% dari pagu bulan ini). Silang dana diperlukan.',
                'over_budget'       => true,
                'classified_type'   => $classifiedType,
                'available_balance' => $gate4Result['available_balance'],
            ];
        }

        return [
            'success'           => true,
            'gate'              => 4,
            'message'           => 'Validasi berhasil. Tiket diteruskan ke Department Head.',
            'over_budget'       => false,
            'classified_type'   => $classifiedType,
            'available_balance' => null,
        ];

    }

    /**
     * Apply cross-fund: flag ticket, lock alternate budget, advance to pending_dept_head.
     *
     * When OPEX is over-budget → try CAPEX of same category, and vice versa.
     *
     * @return array{success: bool, message: string}
     */
    public function applyCrossFund(Ticket $ticket, User $requester): array
    {
        $originalType    = $ticket->expenditure_type;
        $alternativeType = $originalType === Ticket::TYPE_OPEX
            ? Ticket::TYPE_CAPEX
            : Ticket::TYPE_OPEX;

        // Wrap entire check + lock in a single atomic transaction to prevent
        // concurrent cross-fund requests from double-spending the alternate budget.
        $result = DB::transaction(function () use ($ticket, $alternativeType, $requester) {
            $budget = Budget::findForTicket($alternativeType, $ticket->category, now()->year);

            if (! $budget || $budget->available_balance < (float) $ticket->amount) {
                return ['success' => false];
            }

            $ticket->update([
                'expenditure_type' => $alternativeType,
                'is_cross_fund'    => true,
                'status'           => Ticket::STATUS_PENDING_DEPT_HEAD,
            ]);

            $budget->lock((float) $ticket->amount);

            ApprovalLog::create([
                'ticket_id' => $ticket->id,
                'user_id'   => $requester->id,
                'action'    => ApprovalLog::ACTION_CROSS_FUND_REQUESTED,
                'notes'     => "Silang dana dari {$ticket->expenditure_type} ke {$alternativeType}. Saldo dikunci sementara.",
            ]);

            return ['success' => true];
        });

        if (! $result['success']) {
            return [
                'success' => false,
                'message' => 'Saldo anggaran alternatif juga tidak mencukupi. Pengajuan tidak dapat dilanjutkan.',
            ];
        }

        return [
            'success' => true,
            'message' => 'Silang dana berhasil diajukan. Tiket diteruskan ke Department Head.',
        ];
    }

    // ──────────────────────────────────────────────────────────
    // Gate Implementations
    // ──────────────────────────────────────────────────────────

    /**
     * Gate 1 — Duplicate Check
     *
     * Checks if an identical active ticket (same item_name + same user) exists.
     * Tickets with status 'declined' are excluded from the check.
     */
    private function gate1DuplicateCheck(Ticket $ticket, User $requester): array
    {
        $duplicate = Ticket::where('user_id', $requester->id)
            ->where('item_name', $ticket->item_name)
            ->where('id', '!=', $ticket->id)
            ->whereNotIn('status', [Ticket::STATUS_DECLINED])
            ->exists();

        if ($duplicate) {
            return [
                'passed'  => false,
                'message' => 'Pengajuan serupa sudah tersedia dalam sistem.',
            ];
        }

        return ['passed' => true, 'message' => ''];
    }

    /**
     * Gate 2 — Nominal Validation
     *
     * Validates the submitted amount for anomalies.
     */
    private function gate2NominalValidation(Ticket $ticket): array
    {
        $amount = (float) $ticket->amount;

        if ($amount <= 0) {
            return [
                'passed'  => false,
                'message' => 'Nominal harga tidak wajar atau tidak valid. Nilai harus lebih dari 0.',
            ];
        }

        // Reasonableness upper bound: 99 billion (configurable via config if needed)
        $maxReasonable = 99_000_000_000;
        if ($amount > $maxReasonable) {
            return [
                'passed'  => false,
                'message' => 'Nominal harga tidak wajar atau tidak valid. Nilai terlalu besar.',
            ];
        }

        return ['passed' => true, 'message' => ''];
    }

    /**
     * Gate 3 — CAPEX/OPEX Auto-Classification
     *
     * Classification rules (no fail state — always resolves to CAPEX or OPEX):
     *
     *  - hardware or software AND amount >= capitalization_threshold → CAPEX
     *  - hardware or software AND amount < capitalization_threshold  → OPEX
     *  - services                                                    → OPEX
     *  - office_supplies                                             → OPEX
     *  - others                                                      → OPEX
     */
    private function gate3Classification(Ticket $ticket): string
    {
        $threshold = (float) config('eprocurement.capitalization_threshold', 200_000_000);
        $amount    = (float) $ticket->amount;
        $category  = $ticket->category;

        // CAPEX-eligible asset classes:
        //   infrastruktur_utama  (formerly hardware)  → CAPEX if >= threshold
        //   lisensi_sistem       (formerly software)   → CAPEX if >= threshold
        //
        // Always OPEX:
        //   layanan_pemeliharaan     (formerly services)
        //   perlengkapan_operasional (formerly office_supplies)
        $capexEligibleCategories = [
            Ticket::CATEGORY_INFRASTRUKTUR_UTAMA,
            Ticket::CATEGORY_LISENSI_SISTEM,
        ];

        if (in_array($category, $capexEligibleCategories) && $amount >= $threshold) {
            return Ticket::TYPE_CAPEX;
        }

        return Ticket::TYPE_OPEX;
    }

    /**
     * Gate 4 — Budget Availability Check (read-only, used only for over-budget response preview)
     *
     * NOTE: This method is NO LONGER called in the main run() flow.
     * The atomic check+lock is handled directly in the DB::transaction block above.
     * Kept here for potential standalone use in cross-fund pre-check scenarios.
     */
    private function gate4BudgetCheck(Ticket $ticket, string $expenditureType): array
    {
        $budget = Budget::where('expenditure_type', $expenditureType)
            ->where('category', $ticket->category)
            ->where('fiscal_year', now()->year)
            ->first(); // Read-only preview — no lockForUpdate needed here

        if (! $budget) {
            return [
                'over_budget'       => true,
                'available_balance' => 0.0,
            ];
        }

        $available = $budget->available_balance;
        $amount    = (float) $ticket->amount;

        if ($amount > $available) {
            return [
                'over_budget'       => true,
                'available_balance' => $available,
            ];
        }

        return [
            'over_budget'       => false,
            'available_balance' => $available,
        ];
    }

    // ──────────────────────────────────────────────────────────
    // Internal Helpers
    // ──────────────────────────────────────────────────────────

    private function fail(int $gate, string $message): array
    {
        return [
            'success'           => false,
            'gate'              => $gate,
            'message'           => $message,
            'over_budget'       => false,
            'classified_type'   => null,
            'available_balance' => null,
        ];
    }
}
