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
 * Gate 1 — Duplicate Check       (soft warning: user can override)
 * Gate 2 — Nominal Validation    (soft warning: user can override if > threshold)
 * Gate 3 — CAPEX/OPEX Auto-Classification
 * Gate 4 — Budget Availability Check (no lock here — lock happens at cross-fund/decision)
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
     *   needs_duplicate_confirmation: bool,
     *   needs_nominal_confirmation: bool,
     *   over_budget: bool,
     *   classified_type: string|null,
     *   available_balance: float|null,
     * }
     */
    public function run(Ticket $ticket, User $requester, bool $duplicateConfirmed = false, bool $nominalConfirmed = false): array
    {
        // Guard: ticket must be in 'need_to_validate' status
        if (! $ticket->isNeedToValidate()) {
            return $this->fail(0, 'Tiket tidak dalam status yang dapat divalidasi.');
        }

        // ─────────────── GATE 1: Duplicate Check ───────────────
        // Soft warning — user can override by confirming
        if (! $duplicateConfirmed) {
            $gate1 = $this->gate1DuplicateCheck($ticket, $requester);
            if ($gate1['has_duplicate']) {
                return [
                    'success'                      => false,
                    'gate'                         => 1,
                    'message'                      => $gate1['message'],
                    'needs_duplicate_confirmation' => true,
                    'needs_nominal_confirmation'   => false,
                    'over_budget'                  => false,
                    'classified_type'              => null,
                    'available_balance'            => null,
                ];
            }
        }

        // ─────────────── GATE 2: Nominal Validation ───────────────
        // Hard fail: amount <= 0
        // Soft warning: amount > max_reasonable (user can confirm to proceed)
        if (! $nominalConfirmed) {
            $gate2 = $this->gate2NominalValidation($ticket);
            if ($gate2['hard_fail']) {
                return $this->fail(2, $gate2['message']);
            }
            if ($gate2['needs_confirmation']) {
                return [
                    'success'                      => false,
                    'gate'                         => 2,
                    'message'                      => $gate2['message'],
                    'needs_duplicate_confirmation' => false,
                    'needs_nominal_confirmation'   => true,
                    'over_budget'                  => false,
                    'classified_type'              => null,
                    'available_balance'            => null,
                ];
            }
        }

        // ─────────────── GATE 3: CAPEX/OPEX Classification ───────────────
        $classifiedType = $this->gate3Classification($ticket);
        DB::transaction(function () use ($ticket, $classifiedType) {
            $ticket->update(['expenditure_type' => $classifiedType]);
        });

        // ─────────────── GATE 4: Budget Availability Check (READ-ONLY) ───────────────
        //
        // NOTE: Budget is NO LONGER locked here. Locking happens only after:
        //  - Cross-fund confirmation (applyCrossFund)
        // This allows users to see the budget situation before committing.
        //
        // Budget check uses a 3-tier monthly rule:
        //  - Tier 1: amount <= monthly_limit (annual/12) → pass normally
        //  - Tier 2: monthly_limit < amount <= monthly_limit * 1.30 → pass with tolerance (10–30% over)
        //  - Tier 3: amount > monthly_limit * 1.30 → mandatory cross-fund (> 30% over monthly limit)
        $gate4Result = DB::transaction(function () use ($ticket, $classifiedType, $requester) {
            $budget = Budget::findForTicket(
                $classifiedType,
                $ticket->category,
                now()->year
            );

            if (! $budget) {
                return ['status' => 'over_budget', 'available_balance' => 0.0];
            }

            $available    = $budget->available_balance;
            $amount       = $ticket->total_amount;
            $totalLimit   = (float) $budget->total_limit;

            // Monthly limit = annual budget / 12
            $monthlyLimit = $totalLimit > 0 ? $totalLimit / 12 : 0.0;

            // Tier 3: amount exceeds monthly_limit by more than 30% → mandatory cross-fund
            if ($monthlyLimit > 0 && $amount > $monthlyLimit * 1.30) {
                return [
                    'status'           => 'over_budget',
                    'available_balance' => $available,
                    'monthly_limit'    => $monthlyLimit,
                ];
            }

            // Tier 1 & 2: amount within monthly_limit or within 10–30% tolerance
            if ($amount > $available) {
                return [
                    'status'           => 'over_budget',
                    'available_balance' => $available,
                    'monthly_limit'    => $monthlyLimit,
                ];
            }

            // Budget tersedia — advance tiket ke Team Leader (NO BUDGET LOCK HERE)
            $ticket->update(['status' => Ticket::STATUS_PENDING_TEAM_LEADER]);

            $toleranceNote = ($monthlyLimit > 0 && $amount > $monthlyLimit)
                ? ' (Kelebihan pagu bulanan dalam batas toleransi 10–30%.)'
                : '';

            ApprovalLog::create([
                'ticket_id' => $ticket->id,
                'user_id'   => $requester->id,
                'action'    => ApprovalLog::ACTION_VALIDATED,
                'notes'     => "Klasifikasi: {$classifiedType}. Validasi lolos.{$toleranceNote}",
            ]);

            return ['status' => 'ok', 'available_balance' => $available];
        });

        if ($gate4Result['status'] === 'over_budget') {
            // Return over-budget info — caller will display cross-fund popup
            return [
                'success'                      => false,
                'gate'                         => 4,
                'message'                      => 'Pengajuan melebihi batas anggaran bulanan (> 30% dari pagu bulan ini). Silang dana diperlukan.',
                'needs_duplicate_confirmation' => false,
                'needs_nominal_confirmation'   => false,
                'over_budget'                  => true,
                'classified_type'              => $classifiedType,
                'available_balance'            => $gate4Result['available_balance'],
            ];
        }

        return [
            'success'                      => true,
            'gate'                         => 4,
            'message'                      => 'Validasi berhasil. Tiket diteruskan ke Team Leader.',
            'needs_duplicate_confirmation' => false,
            'needs_nominal_confirmation'   => false,
            'over_budget'                  => false,
            'classified_type'              => $classifiedType,
            'available_balance'            => null,
        ];
    }

    /**
     * Apply cross-fund: flag ticket, lock alternate budget, advance to pending_team_leader.
     *
     * When OPEX is over-budget → try CAPEX of same category, and vice versa.
     * Budget lock happens HERE (not in Gate 4) per Revisi 3.
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

            if (! $budget || $budget->available_balance < $ticket->total_amount) {
                return ['success' => false];
            }

            $ticket->update([
                'expenditure_type' => $alternativeType,
                'is_cross_fund'    => true,
                'status'           => Ticket::STATUS_PENDING_TEAM_LEADER,
            ]);

            // Budget lock happens here — after cross-fund decision confirmed
            $budget->lock($ticket->total_amount);

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
            'message' => 'Silang dana berhasil diajukan. Tiket diteruskan ke Team Leader.',
        ];
    }

    // ──────────────────────────────────────────────────────────
    // Gate Implementations
    // ──────────────────────────────────────────────────────────

    /**
     * Gate 1 — Duplicate Check (SOFT WARNING — user can override)
     *
     * Checks if an identical active ticket (same item_name + same user) exists.
     * Tickets with status 'declined' are excluded from the check.
     * Returns has_duplicate: true if found — caller shows warning popup.
     */
    private function gate1DuplicateCheck(Ticket $ticket, User $requester): array
    {
        $duplicate = Ticket::where('user_id', $requester->id)
            ->where('item_name', $ticket->item_name)
            ->where('id', '!=', $ticket->id)
            ->whereNotIn('status', [Ticket::STATUS_DECLINED])
            ->first();

        if ($duplicate) {
            return [
                'has_duplicate' => true,
                'message'       => "Terdapat pengajuan serupa yang sudah aktif di sistem (Tiket #{$duplicate->id}: \"{$duplicate->title}\"). Apakah Anda yakin ingin melanjutkan pengajuan ini?",
            ];
        }

        return ['has_duplicate' => false, 'message' => ''];
    }

    /**
     * Gate 2 — Nominal Validation (SOFT WARNING for unreasonable amount)
     *
     * Hard fail: amount <= 0
     * Soft warning: amount > max_reasonable (user can confirm to proceed)
     */
    private function gate2NominalValidation(Ticket $ticket): array
    {
        $amount = $ticket->total_amount;

        // Hard fail: amount is zero or negative — cannot proceed
        if ($amount <= 0) {
            return [
                'hard_fail'           => true,
                'needs_confirmation'  => false,
                'message'             => 'Nominal harga tidak wajar atau tidak valid. Nilai harus lebih dari 0.',
            ];
        }

        // Soft warning: amount exceeds reasonableness threshold (99 billion)
        $maxReasonable = 99_000_000_000;
        if ($amount > $maxReasonable) {
            return [
                'hard_fail'           => false,
                'needs_confirmation'  => true,
                'message'             => 'Nominal pengadaan tergolong sangat besar (> Rp 99 Miliar). Apakah nominal ini sudah benar dan Anda yakin ingin melanjutkan?',
            ];
        }

        return ['hard_fail' => false, 'needs_confirmation' => false, 'message' => ''];
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
        $amount    = $ticket->total_amount;
        $category  = $ticket->category;

        // CAPEX-eligible asset classes:
        //   infrastruktur_utama  → CAPEX if >= threshold
        //   lisensi_sistem       → CAPEX if >= threshold
        //
        // Always OPEX:
        //   layanan_pemeliharaan, perlengkapan_operasional
        $capexEligibleCategories = [
            Ticket::CATEGORY_INFRASTRUKTUR_UTAMA,
            Ticket::CATEGORY_LISENSI_SISTEM,
        ];

        if (in_array($category, $capexEligibleCategories) && $amount >= $threshold) {
            return Ticket::TYPE_CAPEX;
        }

        return Ticket::TYPE_OPEX;
    }

    // ──────────────────────────────────────────────────────────
    // Internal Helpers
    // ──────────────────────────────────────────────────────────

    private function fail(int $gate, string $message): array
    {
        return [
            'success'                      => false,
            'gate'                         => $gate,
            'message'                      => $message,
            'needs_duplicate_confirmation' => false,
            'needs_nominal_confirmation'   => false,
            'over_budget'                  => false,
            'classified_type'              => null,
            'available_balance'            => null,
        ];
    }
}
