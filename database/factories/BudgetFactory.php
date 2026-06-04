<?php

namespace Database\Factories;

use App\Models\Budget;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Budget>
 */
class BudgetFactory extends Factory
{
    protected $model = Budget::class;

    public function definition(): array
    {
        return [
            'expenditure_type' => $this->faker->randomElement(['CAPEX', 'OPEX']),
            'category'         => $this->faker->randomElement(['hardware', 'software', 'services', 'office_supplies', 'others']),
            'fiscal_year'      => now()->year,
            'total_limit'      => 1_000_000_000.00,
            'locked_amount'    => 0.00,
            'used_amount'      => 0.00,
        ];
    }

    public function capex(): self
    {
        return $this->state(['expenditure_type' => 'CAPEX']);
    }

    public function opex(): self
    {
        return $this->state(['expenditure_type' => 'OPEX']);
    }

    public function forCategory(string $category): self
    {
        return $this->state(['category' => $category]);
    }

    public function withLimit(float $limit): self
    {
        return $this->state(['total_limit' => $limit]);
    }

    public function almostExhausted(): self
    {
        return $this->state([
            'total_limit'  => 1_000_000_000.00,
            'used_amount'  => 980_000_000.00, // Only 20M remaining
            'locked_amount' => 0.00,
        ]);
    }

    public function fullyExhausted(): self
    {
        return $this->state([
            'total_limit'  => 1_000_000_000.00,
            'used_amount'  => 1_000_000_000.00,
            'locked_amount' => 0.00,
        ]);
    }
}
