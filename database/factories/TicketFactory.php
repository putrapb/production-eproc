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
        $categories = ['hardware', 'software', 'services', 'office_supplies', 'others'];

        return [
            'user_id'         => User::factory()->requester(),
            'title'           => $this->faker->sentence(5),
            'item_name'       => $this->faker->words(3, true),
            'category'        => $this->faker->randomElement($categories),
            'description'     => $this->faker->paragraph(),
            'quantity'        => $this->faker->numberBetween(1, 50),
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

    public function hardware(): self
    {
        return $this->state(['category' => 'hardware']);
    }

    public function software(): self
    {
        return $this->state(['category' => 'software']);
    }

    public function services(): self
    {
        return $this->state(['category' => 'services']);
    }

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
