<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        $categories = [
            'infrastruktur_utama',
            'lisensi_sistem',
            'layanan_pemeliharaan',
            'perlengkapan_operasional',
        ];

        return [
            'user_id'         => User::factory()->requester(),
            'title'           => $this->faker->sentence(5),
            'item_name'       => $this->faker->words(3, true),
            'category'        => $this->faker->randomElement($categories),
            'description'     => $this->faker->paragraph(),
            'quantity'        => 1,
            'vendor_name'     => $this->faker->company(),
            'amount'          => $this->faker->randomFloat(2, 1_000_000, 500_000_000),
            'expenditure_type' => null,
            'document_path'   => null,
            'document_po_path' => null,
            'status'          => Ticket::STATUS_PENDING_REVIEW,
            'is_cross_fund'   => false,
        ];
    }

    // ─── Status State Methods ───

    public function pendingReview(): self
    {
        return $this->state(['status' => Ticket::STATUS_PENDING_REVIEW]);
    }

    public function revision(): self
    {
        return $this->state(['status' => Ticket::STATUS_REVISION]);
    }

    public function needToValidate(): self
    {
        return $this->state(['status' => Ticket::STATUS_NEED_TO_VALIDATE]);
    }

    public function pendingDeptHead(): self
    {
        return $this->state([
            'status'           => Ticket::STATUS_PENDING_DEPT_HEAD,
            'expenditure_type' => 'OPEX',
        ]);
    }

    public function pendingDivHead(): self
    {
        return $this->state([
            'status'           => Ticket::STATUS_PENDING_DIV_HEAD,
            'expenditure_type' => 'OPEX',
        ]);
    }

    public function approved(): self
    {
        return $this->state([
            'status'           => Ticket::STATUS_APPROVED,
            'expenditure_type' => 'OPEX',
        ]);
    }

    public function declined(): self
    {
        return $this->state([
            'status'           => Ticket::STATUS_DECLINED,
            'expenditure_type' => 'OPEX',
        ]);
    }

    public function poGenerated(): self
    {
        return $this->state([
            'status'           => Ticket::STATUS_PO_GENERATED,
            'expenditure_type' => 'OPEX',
            'document_po_path' => 'purchase_orders/PO-test.pdf',
        ]);
    }

    // ─── Category States ───

    public function infrastrukturUtama(): self
    {
        return $this->state(['category' => 'infrastruktur_utama']);
    }

    public function lisensiSistem(): self
    {
        return $this->state(['category' => 'lisensi_sistem']);
    }

    public function layananPemeliharaan(): self
    {
        return $this->state(['category' => 'layanan_pemeliharaan']);
    }

    public function perlengkapanOperasional(): self
    {
        return $this->state(['category' => 'perlengkapan_operasional']);
    }

    // Legacy aliases kept for any remaining test references
    public function hardware(): self { return $this->infrastrukturUtama(); }
    public function software(): self { return $this->lisensiSistem(); }
    public function services(): self { return $this->layananPemeliharaan(); }

    // ─── Amount States ───

    public function highValue(): self
    {
        return $this->state(['amount' => 350_000_000.00]); // Above CAPEX threshold
    }

    public function lowValue(): self
    {
        return $this->state(['amount' => 50_000_000.00]); // Below CAPEX threshold
    }

    public function crossFund(): self
    {
        return $this->state(['is_cross_fund' => true]);
    }
}
