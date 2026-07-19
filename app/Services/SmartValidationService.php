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
 * Menjalankan validasi 4-Gate secara berurutan sebelum tiket diteruskan ke Dept Head.
 * Setiap gate hanya berjalan jika gate sebelumnya lolos.
 *
 * Gate 1 - Cek Duplikasi          (peringatan lunak, bisa di-override user)
 * Gate 2 - Validasi Nominal       (peringatan lunak jika > 99M, hard fail jika <= 0)
 * Gate 3 - Klasifikasi CAPEX/OPEX (berbasis PSAK 16 & 19, peringatan jika tidak cocok)
 * Gate 4 - Ketersediaan Anggaran  (keras: blokir jika saldo tidak cukup)
 *
 * Aturan klasifikasi Gate 3 (PSAK 16 Aset Tetap & PSAK 19 Aset Takberwujud):
 *  infrastruktur_utama      -> CAPEX (server, storage, hardware jaringan)
 *  lisensi_sistem           -> tergantung kata kunci item:
 *                               OPEX jika ada kata kunci SaaS/cloud/langganan/sewa
 *                               CAPEX jika lisensi perpetual (aset takberwujud)
 *  layanan_pemeliharaan     -> OPEX (biaya pemeliharaan rutin)
 *  perlengkapan_operasional -> OPEX (perlengkapan habis pakai)
 */
class SmartValidationService
{
    /**
     * Jalankan 4-gate validation untuk sebuah tiket.
     */
    public function run(
        Ticket $ticket,
        User   $requester,
        bool   $duplicateConfirmed      = false,
        bool   $nominalConfirmed        = false,
        bool   $classificationConfirmed = false
    ): array {
        // Tiket harus dalam status need_to_validate
        if (! $ticket->isNeedToValidate()) {
            return $this->fail(0, 'Tiket tidak dalam status yang dapat divalidasi.');
        }

        // Gate 1: cek duplikasi pengajuan yang mirip
        // Kalau sudah dikonfirmasi user, skip gate ini
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

        // Gate 2: cek kewajaran nominal pengajuan
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

        // Gate 3: sistem menyarankan CAPEX/OPEX berdasarkan PSAK 16 & 19
        // Kalau beda sama pilihan requester, tampilkan peringatan lunak
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

        // Pakai pilihan requester jika mereka mengkonfirmasi ketidaksesuaian, selain itu pakai saran sistem
        $classifiedType = ($typeMismatch && $classificationConfirmed)
            ? $requesterType
            : $suggestedType;

        DB::transaction(function () use ($ticket, $classifiedType) {
            $ticket->update(['expenditure_type' => $classifiedType]);
        });

        // Gate 4: cek ketersediaan anggaran
        // Budget dikunci di sini kalau lolos, bukan sebelumnya
        // Hitungan pakai aturan monthly limit: >30% dari batas bulanan = wajib silang dana
        $gate4Result = DB::transaction(function () use ($ticket, $requester) {
            $capexTotal = 0.0;
            $opexTotal  = 0.0;

            $type = $classifiedType ?? $ticket->expenditure_type;
            if ($type === Ticket::TYPE_CAPEX) {
                $capexTotal = (float) $ticket->amount;
            } else {
                $opexTotal = (float) $ticket->amount;
            }

            $capexBudget = null;
            $opexBudget  = null;

            if ($capexTotal > 0) {
                $capexBudget = Budget::findForTicket(Ticket::TYPE_CAPEX, $ticket->category, now()->year);
                if (! $capexBudget) {
                    return ['status' => 'over_budget', 'type' => 'CAPEX', 'available_balance' => 0.0];
                }
                $available = (float) $capexBudget->available_balance;
                $totalLimit = (float) $capexBudget->total_limit;
                $monthlyLimit = $totalLimit > 0 ? $totalLimit / 12 : 0.0;

                if ($monthlyLimit > 0 && $capexTotal > $monthlyLimit * 1.30) {
                    return ['status' => 'over_budget', 'type' => 'CAPEX', 'available_balance' => $available];
                }
                if ($capexTotal > $available) {
                    return ['status' => 'over_budget', 'type' => 'CAPEX', 'available_balance' => $available];
                }
            }

            if ($opexTotal > 0) {
                $opexBudget = Budget::findForTicket(Ticket::TYPE_OPEX, $ticket->category, now()->year);
                if (! $opexBudget) {
                    return ['status' => 'over_budget', 'type' => 'OPEX', 'available_balance' => 0.0];
                }
                $available = (float) $opexBudget->available_balance;
                $totalLimit = (float) $opexBudget->total_limit;
                $monthlyLimit = $totalLimit > 0 ? $totalLimit / 12 : 0.0;

                if ($monthlyLimit > 0 && $opexTotal > $monthlyLimit * 1.30) {
                    return ['status' => 'over_budget', 'type' => 'OPEX', 'available_balance' => $available];
                }
                if ($opexTotal > $available) {
                    return ['status' => 'over_budget', 'type' => 'OPEX', 'available_balance' => $available];
                }
            }

            // Budget tersedia — lock budget dan advance tiket langsung ke Department Head
            if ($capexTotal > 0 && $capexBudget) {
                $capexBudget->lock($capexTotal);
            }
            if ($opexTotal > 0 && $opexBudget) {
                $opexBudget->lock($opexTotal);
            }

            $ticket->update(['status' => Ticket::STATUS_PENDING_DEPT_HEAD]);

            $desc = [];
            if ($capexTotal > 0) $desc[] = "CAPEX: Rp " . number_format($capexTotal, 0, ',', '.');
            if ($opexTotal > 0)  $desc[] = "OPEX: Rp " . number_format($opexTotal, 0, ',', '.');
            $notes = "Validasi lolos. Alokasi anggaran: " . implode(', ', $desc);

            ApprovalLog::create([
                'ticket_id' => $ticket->id,
                'user_id'   => $requester->id,
                'action'    => ApprovalLog::ACTION_VALIDATED,
                'notes'     => $notes,
            ]);

            return ['status' => 'ok', 'available_balance' => null];
        });

        if ($gate4Result['status'] === 'over_budget') {
            $overType = $gate4Result['type'] ?? $classifiedType;
            return [
                'success'                      => false,
                'gate'                         => 4,
                'message'                      => "Pengajuan melebihi batas anggaran bulanan atau saldo tidak mencukupi untuk jenis pengeluaran {$overType}.",
                'needs_duplicate_confirmation' => false,
                'needs_nominal_confirmation'   => false,
                'over_budget'                  => true,
                'classified_type'              => $overType,
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
     * Jalankan silang dana: kunci anggaran alternatif dan teruskan tiket ke Dept Head.
     * Dipanggil saat Gate 4 gagal karena over-budget dan requester memilih cross-fund.
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

    // Gate Implementations

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
                'message'             => 'Nominal pengajuan tidak valid. Total harga harus lebih dari Rp 0.',
            ];
        }

        // Soft warning: amount exceeds reasonableness threshold (99 billion)
        $maxReasonable = 99_000_000_000;
        if ($amount > $maxReasonable) {
            return [
                'hard_fail'           => false,
                'needs_confirmation'  => true,
                'message'             => 'Total nominal pengajuan sangat besar (> Rp 99 Miliar). Pastikan nominal sudah benar sebelum melanjutkan ke validasi ketersediaan dana.',
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

        // Infrastruktur Utama: server, storage, network hardware
        // These are long-lived physical assets → PSAK 16 Aset Tetap
        if ($category === Ticket::CATEGORY_INFRASTRUKTUR_UTAMA) {
            return Ticket::TYPE_CAPEX;
        }

        // Layanan Pemeliharaan: maintenance contracts, managed services, ITSM
        // Perlengkapan Operasional: consumables, ATK, spare parts
        // These are recurring operational costs — never capitalized
        if (in_array($category, [
            Ticket::CATEGORY_LAYANAN_PEMELIHARAAN,
            Ticket::CATEGORY_PERLENGKAPAN_OPERASIONAL,
        ])) {
            return Ticket::TYPE_OPEX;
        }

        // PSAK 19: Software licenses are intangible assets (CAPEX) IF perpetual.
        // SaaS/cloud/subscription = no asset ownership → OPEX.
        //
        // Check item names from ticket_items for OPEX signals.
        $opexSignals = [
            'subscription', 'langganan', 'saas', 'cloud', 'tahunan', 'bulanan',
            'monthly', 'annual', 'sewa', 'rental', 'as a service', 'managed service',
            'support contract', 'maintenance fee', 'hosting', 'recurring',
        ];

        $itemNames = strtolower($ticket->item_name);

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

    // Internal Helpers

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

    /**
     * Transkrip 2 — Upfront Validation (Preview Mode).
     *
     * Menjalankan Gate 1 (duplicate) + Gate 2 (nominal) + Gate 3 (klasifikasi CAPEX/OPEX)
     * secara READ-ONLY tanpa menyimpan apapun ke database.
     *
     * Digunakan oleh form create/edit via AJAX sebelum requester menekan "Submit".
     * Budget check (Gate 4) TIDAK dijalankan di sini — bola belum di-lock.
     *
     * Gate 3 sekarang mengklasifikasi PER-ITEM berdasarkan category tiket + item_name.
     * Hasilnya: per_item_classification = [{ item_name, suggested_type, subtotal }, ...]
     *
     * @param array $data Form data dari requester (belum tersimpan ke DB)
     * @param User  $requester
     * @return array{
     *   classified_type: string|null,
     *   total_amount: float,
     *   capex_total: float,
     *   opex_total: float,
     *   per_item_classification: array,
     *   has_duplicate: bool,
     *   nominal_warning: string|null,
     *   budget_status: string|null,
     *   capex_budget_status: string|null,
     *   opex_budget_status: string|null,
     *   available_balance: float|null,
     *   suggestions: string[],
     *   gates: array,
     * }
     */
    public function preview(array $data, User $requester): array
    {
        $category    = $data['category'] ?? '';
        $title       = $data['title'] ?? '';;
        $items       = $data['items'] ?? [];
        $suggestions = [];

        // Aturan: category di level tiket menentukan basis CAPEX/OPEX,
        // tapi item_name dari lisensi_sistem bisa override ke OPEX via keyword.
        $opexSignals = [
            'subscription', 'langganan', 'saas', 'cloud', 'tahunan', 'bulanan',
            'monthly', 'annual', 'sewa', 'rental', 'as a service', 'managed service',
            'hosting', 'recurring', 'support contract', 'maintenance fee',
        ];

        $perItemClassification = [];
        $capexTotal = 0.0;
        $opexTotal  = 0.0;

        foreach ($items as $item) {
            $itemName  = $item['item_name'] ?? '';
            $qty       = (int) ($item['quantity']   ?? 1);
            $price     = (float) ($item['unit_price'] ?? 0);
            $subtotal  = $qty * $price;
            $itemLower = strtolower($itemName);

            // Determine per-item type using same PSAK 16/19 rules as gate3Classification
            if (!empty($item['expenditure_type'])) {
                $suggestedType = $item['expenditure_type'];
            } else {
                if ($category === Ticket::CATEGORY_INFRASTRUKTUR_UTAMA) {
                    $suggestedType = Ticket::TYPE_CAPEX;
                } elseif (in_array($category, [
                    Ticket::CATEGORY_LAYANAN_PEMELIHARAAN,
                    Ticket::CATEGORY_PERLENGKAPAN_OPERASIONAL,
                ])) {
                    $suggestedType = Ticket::TYPE_OPEX;
                } elseif ($category === Ticket::CATEGORY_LISENSI_SISTEM) {
                    // Keyword-based: if item name has OPEX signals → OPEX, else CAPEX
                    $suggestedType = Ticket::TYPE_CAPEX;
                    foreach ($opexSignals as $signal) {
                        if (str_contains($itemLower, $signal)) {
                            $suggestedType = Ticket::TYPE_OPEX;
                            break;
                        }
                    }
                } else {
                    $suggestedType = null; // unknown category
                }
            }

            $perItemClassification[] = [
                'item_name'      => $itemName,
                'suggested_type' => $suggestedType,
                'subtotal'       => $subtotal,
                'qty'            => $qty,
                'unit_price'     => $price,
            ];

            if ($suggestedType === Ticket::TYPE_CAPEX) {
                $capexTotal += $subtotal;
            } elseif ($suggestedType === Ticket::TYPE_OPEX) {
                $opexTotal += $subtotal;
            }
        }

        $totalAmount = $capexTotal + $opexTotal;

        // Dominant type for ticket-level classification (higher value wins)
        $classifiedType = null;
        if ($capexTotal > 0 || $opexTotal > 0) {
            $classifiedType = $capexTotal >= $opexTotal ? Ticket::TYPE_CAPEX : Ticket::TYPE_OPEX;
        }

        $gate1 = ['status' => 'pass', 'message' => 'Tidak ditemukan tiket dengan judul serupa.'];
        $hasDuplicate = false;
        if ($title) {
            $existing = Ticket::where('user_id', $requester->id)
                ->where('title', 'like', '%' . substr($title, 0, 20) . '%')
                ->whereNotIn('status', [Ticket::STATUS_DECLINED])
                ->exists();
            if ($existing) {
                $hasDuplicate = true;
                $gate1 = [
                    'status'  => 'warning',
                    'message' => 'Ditemukan tiket dengan judul serupa yang masih aktif di sistem. Pastikan ini bukan pengajuan duplikat.',
                ];
            }
        }

        $nominalWarning = null;
        $gate2 = ['status' => 'pass', 'message' => 'Nominal pengajuan valid.'];
        if ($totalAmount <= 0) {
            $nominalWarning = 'Total nominal harus lebih dari Rp 0.';
            $gate2 = ['status' => 'fail', 'message' => $nominalWarning];
        } elseif ($totalAmount > 99_000_000_000) {
            $nominalWarning = 'Total nominal melebihi Rp 99 miliar. Pastikan sudah benar.';
            $gate2 = ['status' => 'warning', 'message' => $nominalWarning];
        }

        $gate3 = ['status' => 'skipped', 'message' => 'Pilih kategori terlebih dahulu.'];
        if ($category && $classifiedType) {
            $userSelectedType = $data['expenditure_type'] ?? null;
            if ($userSelectedType && $userSelectedType !== $classifiedType) {
                $gate3 = [
                    'status'  => 'warning',
                    'message' => "Sistem menyarankan <strong>" . e($classifiedType) . "</strong> berdasarkan PSAK 16/19. "
                               . "Anda memilih <strong>" . e($userSelectedType) . "</strong>. "
                               . "Anda tetap bisa melanjutkan dengan pilihan Anda.",
                ];
            } else {
                $mixedTypes = collect($perItemClassification)
                    ->pluck('suggested_type')
                    ->unique()
                    ->filter()
                    ->count() > 1;

                if ($mixedTypes) {
                    $gate3 = [
                        'status'  => 'info',
                        'message' => "Tiket ini mengandung item campuran CAPEX + OPEX. "
                                   . "CAPEX: <strong>Rp " . number_format($capexTotal, 0, ',', '.') . "</strong>, "
                                   . "OPEX: <strong>Rp " . number_format($opexTotal, 0, ',', '.') . "</strong>. "
                                   . "Masing-masing akan dibebankan ke pos anggaran yang sesuai.",
                    ];
                } else {
                    $gate3 = [
                        'status'  => 'pass',
                        'message' => "Semua item diklasifikasikan sebagai <strong>{$classifiedType}</strong> sesuai PSAK 16/19.",
                    ];
                }
            }
        }

        $gate4 = ['status' => 'skipped', 'message' => 'Klasifikasi belum selesai — pilih kategori dulu.'];
        $budgetStatus     = null;
        $capexBudgetStatus = null;
        $opexBudgetStatus  = null;
        $availableBalance = null;

        if ($category && $classifiedType) {
            $gate4Messages = [];
            $gate4Status   = 'pass';

            // Check CAPEX budget if there are CAPEX items
            if ($capexTotal > 0) {
                $capexBudget = Budget::findForTicket(Ticket::TYPE_CAPEX, $category, now()->year, false);
                if (! $capexBudget) {
                    $capexBudgetStatus = 'no_budget';
                    $gate4Status = 'fail';
                    $gate4Messages[] = "[Gagal] Tidak ditemukan anggaran <strong>CAPEX</strong> untuk kategori ini.";
                } elseif ($capexTotal > $capexBudget->available_balance) {
                    $capexBudgetStatus = 'over_budget';
                    $gate4Status = 'fail';
                    $gate4Messages[] = "[Gagal] Anggaran <strong>CAPEX</strong> tidak mencukupi. "
                        . "Dibutuhkan: Rp " . number_format($capexTotal, 0, ',', '.');
                } else {
                    $capexBudgetStatus = 'ok';
                    $gate4Messages[] = "[Lolos] Anggaran <strong>CAPEX</strong> memadai.";
                }
            }

            // Check OPEX budget if there are OPEX items
            if ($opexTotal > 0) {
                $opexBudget = Budget::findForTicket(Ticket::TYPE_OPEX, $category, now()->year, false);
                if (! $opexBudget) {
                    $opexBudgetStatus = 'no_budget';
                    $gate4Status = 'fail';
                    $gate4Messages[] = "[Gagal] Tidak ditemukan anggaran <strong>OPEX</strong> untuk kategori ini.";
                } elseif ($opexTotal > $opexBudget->available_balance) {
                    $opexBudgetStatus = 'over_budget';
                    if ($gate4Status !== 'fail') $gate4Status = 'fail';
                    $gate4Messages[] = "[Gagal] Anggaran <strong>OPEX</strong> tidak mencukupi. "
                        . "Dibutuhkan: Rp " . number_format($opexTotal, 0, ',', '.');
                } else {
                    $opexBudgetStatus = 'ok';
                    $gate4Messages[] = "[Lolos] Anggaran <strong>OPEX</strong> memadai.";
                }
            }

            // Determine overall budget_status
            if ($capexBudgetStatus === 'no_budget' || $opexBudgetStatus === 'no_budget') {
                $budgetStatus = 'no_budget';
            } elseif ($capexBudgetStatus === 'over_budget' || $opexBudgetStatus === 'over_budget') {
                $budgetStatus = 'over_budget';
            } else {
                $budgetStatus = 'ok';
            }

            $gate4 = [
                'status'  => $gate4Status,
                'message' => implode('<br>', $gate4Messages) ?: 'Ketersediaan anggaran OK.',
            ];
        }

        return [
            'classified_type'        => $classifiedType,
            'total_amount'           => $totalAmount,
            'capex_total'            => $capexTotal,
            'opex_total'             => $opexTotal,
            'per_item_classification' => $perItemClassification,
            'has_duplicate'          => $hasDuplicate,
            'nominal_warning'        => $nominalWarning,
            'budget_status'          => $budgetStatus,
            'capex_budget_status'    => $capexBudgetStatus,
            'opex_budget_status'     => $opexBudgetStatus,
            'available_balance'      => $availableBalance,
            'suggestions'            => $suggestions,
            'gates'                  => [
                1 => $gate1,
                2 => $gate2,
                3 => $gate3,
                4 => $gate4,
            ],
        ];
    }
}
