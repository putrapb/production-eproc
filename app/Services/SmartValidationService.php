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
 * Gate 1 — Duplicate Check              (soft warning: user can override)
 * Gate 2 — Nominal Validation           (soft warning: user can override if > 99B)
 * Gate 3 — CAPEX/OPEX Classification   (industry-based per PSAK 16 & 19, soft warning if mismatch)
 * Gate 4 — Budget Availability Check   (hard: insufficient budget = block)
 *
 * Classification rules (Gate 3) — based on PSAK 16 (Aset Tetap) & PSAK 19 (Aset Takberwujud):
 *
 *  infrastruktur_utama      → CAPEX  (server, storage, network hardware = fixed asset per PSAK 16)
 *  lisensi_sistem           → depends on item keywords:
 *                               OPEX keywords (subscription/SaaS/cloud/langganan/sewa) → OPEX
 *                               else → CAPEX (perpetual license = intangible asset per PSAK 19)
 *  layanan_pemeliharaan     → OPEX   (recurring maintenance/managed service = operational cost)
 *  perlengkapan_operasional → OPEX   (consumables, not capitalized)
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
    public function run(
        Ticket $ticket,
        User   $requester,
        bool   $duplicateConfirmed      = false,
        bool   $nominalConfirmed        = false,
        bool   $classificationConfirmed = false
    ): array {
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
                    'success'                           => false,
                    'gate'                              => 1,
                    'message'                           => $gate1['message'],
                    'needs_duplicate_confirmation'      => true,
                    'needs_nominal_confirmation'        => false,
                    'needs_classification_confirmation' => false,
                    'over_budget'                       => false,
                    'classified_type'                   => null,
                    'available_balance'                 => null,
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
                    'success'                           => false,
                    'gate'                              => 2,
                    'message'                           => $gate2['message'],
                    'needs_duplicate_confirmation'      => false,
                    'needs_nominal_confirmation'        => true,
                    'needs_classification_confirmation' => false,
                    'over_budget'                       => false,
                    'classified_type'                   => null,
                    'available_balance'                 => null,
                ];
            }
        }

        // ─────────────── GATE 3: CAPEX/OPEX Industry Classification ───────────────
        // System suggests CAPEX/OPEX based on PSAK 16 & 19 rules.
        // If requester's upfront choice differs → soft warning (can be overridden).
        $suggestedType  = $this->gate3Classification($ticket);
        $requesterType  = $ticket->expenditure_type; // already set by requester at create-time
        $typeMismatch   = $requesterType && ($suggestedType !== $requesterType);

        if ($typeMismatch && ! $classificationConfirmed) {
            return [
                'success'                        => false,
                'gate'                           => 3,
                'message'                        => $this->gate3MismatchMessage($requesterType, $suggestedType, $ticket->category),
                'needs_duplicate_confirmation'   => false,
                'needs_nominal_confirmation'     => false,
                'needs_classification_confirmation' => true,
                'over_budget'                    => false,
                'classified_type'                => $suggestedType,
                'available_balance'              => null,
            ];
        }

        // Use requester's choice if they confirmed mismatch, otherwise use system suggestion
        $classifiedType = ($typeMismatch && $classificationConfirmed)
            ? $requesterType
            : $suggestedType;

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

            // Budget tersedia — lock budget dan advance tiket langsung ke Department Head
            // (Team Leader tidak lagi meneruskan; TL hanya cek dokumen)
            $budget->lock($amount);
            $ticket->update(['status' => Ticket::STATUS_PENDING_DEPT_HEAD]);

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
            'message'                      => 'Validasi berhasil. Tiket diteruskan ke Department Head untuk persetujuan.',
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
                'status'           => Ticket::STATUS_PENDING_DEPT_HEAD,
            ]);

            // Budget lock happens here — after cross-fund decision confirmed
            $budget->lock($ticket->total_amount);

            ApprovalLog::create([
                'ticket_id' => $ticket->id,
                'user_id'   => $requester->id,
                'action'    => ApprovalLog::ACTION_CROSS_FUND_REQUESTED,
                'notes'     => "Silang dana dari {$originalType} ke {$alternativeType}. Saldo dikunci sementara.",
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
     * Gate 1 — Duplicate Check (SOFT WARNING — user can override)
     *
     * Checks if an identical active ticket (same item_name + same user) exists.
     * Tickets with status 'declined' are excluded from the check.
     * Returns has_duplicate: true if found — caller shows warning popup.
     */
    private function gate1DuplicateCheck(Ticket $ticket, User $requester): array
    {
        // Duplicate check by title (item_name column has been removed in favor of ticket_items table)
        $duplicate = Ticket::where('user_id', $requester->id)
            ->where('title', $ticket->title)
            ->where('id', '!=', $ticket->id)
            ->whereNotIn('status', [Ticket::STATUS_DECLINED])
            ->first();

        if ($duplicate) {
            return [
                'has_duplicate' => true,
                'message'       => "Terdapat pengajuan dengan judul yang sama sudah aktif di sistem (Tiket #{$duplicate->id}: \"{$duplicate->title}\"). Apakah Anda yakin ingin melanjutkan pengajuan baru ini?",
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
     * Gate 3 — CAPEX/OPEX Industry-Based Classification
     *
     * Based on PSAK 16 (Aset Tetap) & PSAK 19 (Aset Takberwujud) — standard for
     * Indonesian banking / BUMN entities. No threshold involved.
     *
     * Rules:
     *   infrastruktur_utama      → always CAPEX
     *   lisensi_sistem           → OPEX if item names contain SaaS/subscription signals
     *                              else CAPEX (perpetual license = intangible asset)
     *   layanan_pemeliharaan     → always OPEX
     *   perlengkapan_operasional → always OPEX
     */
    private function gate3Classification(Ticket $ticket): string
    {
        $category = $ticket->category;

        // ── Always CAPEX ─────────────────────────────────────────────
        // Infrastruktur Utama: server, storage, network hardware
        // These are long-lived physical assets → PSAK 16 Aset Tetap
        if ($category === Ticket::CATEGORY_INFRASTRUKTUR_UTAMA) {
            return Ticket::TYPE_CAPEX;
        }

        // ── Always OPEX ──────────────────────────────────────────────
        // Layanan Pemeliharaan: maintenance contracts, managed services, ITSM
        // Perlengkapan Operasional: consumables, ATK, spare parts
        // These are recurring operational costs — never capitalized
        if (in_array($category, [
            Ticket::CATEGORY_LAYANAN_PEMELIHARAAN,
            Ticket::CATEGORY_PERLENGKAPAN_OPERASIONAL,
        ])) {
            return Ticket::TYPE_OPEX;
        }

        // ── Lisensi Sistem — keyword disambiguation ───────────────────
        // PSAK 19: Software licenses are intangible assets (CAPEX) IF perpetual.
        // SaaS/cloud/subscription = no asset ownership → OPEX.
        //
        // Check item names from ticket_items for OPEX signals.
        $opexSignals = [
            'subscription', 'langganan', 'saas', 'cloud', 'tahunan', 'bulanan',
            'monthly', 'annual', 'sewa', 'rental', 'as a service', 'managed service',
            'support contract', 'maintenance fee', 'hosting', 'recurring',
        ];

        $itemNames = $ticket->items->pluck('item_name')->map(fn ($n) => strtolower($n))->implode(' ');

        foreach ($opexSignals as $signal) {
            if (str_contains($itemNames, $signal)) {
                return Ticket::TYPE_OPEX;
            }
        }

        // Default for lisensi_sistem with no OPEX signals: perpetual license → CAPEX
        return Ticket::TYPE_CAPEX;
    }

    /**
     * Build a human-readable mismatch warning message for Gate 3.
     */
    private function gate3MismatchMessage(string $requesterType, string $suggestedType, string $category): string
    {
        $categoryLabels = [
            'infrastruktur_utama'       => 'Infrastruktur Utama',
            'lisensi_sistem'            => 'Lisensi Sistem',
            'layanan_pemeliharaan'      => 'Layanan Pemeliharaan',
            'perlengkapan_operasional'  => 'Perlengkapan Operasional',
        ];
        $catLabel = $categoryLabels[$category] ?? $category;

        $reasons = [
            'infrastruktur_utama'       => 'Infrastruktur Utama umumnya merupakan aset tetap jangka panjang (PSAK 16).',
            'lisensi_sistem'            => 'Berdasarkan nama item, lisensi ini teridentifikasi sebagai aset takberwujud permanen (PSAK 19).',
            'layanan_pemeliharaan'      => 'Layanan Pemeliharaan merupakan biaya operasional berulang, bukan aset.',
            'perlengkapan_operasional'  => 'Perlengkapan Operasional bersifat habis pakai dan tidak dikapitalisasi.',
        ];
        $reason = $reasons[$category] ?? '';

        return "Sistem menyarankan klasifikasi \"{$suggestedType}\" untuk kategori {$catLabel}. "
             . "Anda memilih \"{$requesterType}\". {$reason} "
             . "Apakah Anda yakin ingin mempertahankan pilihan {$requesterType}?";
    }

    // ──────────────────────────────────────────────────────────
    // Internal Helpers
    // ──────────────────────────────────────────────────────────

    private function fail(int $gate, string $message): array
    {
        return [
            'success'                           => false,
            'gate'                              => $gate,
            'message'                           => $message,
            'needs_duplicate_confirmation'      => false,
            'needs_nominal_confirmation'        => false,
            'needs_classification_confirmation' => false,
            'over_budget'                       => false,
            'classified_type'                   => null,
            'available_balance'                 => null,
        ];
    }
}
