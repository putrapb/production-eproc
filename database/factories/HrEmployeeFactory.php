<?php

namespace Database\Factories;

use App\Models\HrEmployee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HrEmployee>
 */
class HrEmployeeFactory extends Factory
{
    protected $model = HrEmployee::class;

    public function definition(): array
    {
        return [
            'nip'      => $this->faker->unique()->bothify('##########'),
            'name'     => $this->faker->name(),
            'position' => 'Staff IT Infrastructure Project Management',
            'division' => 'IT Infrastructure Management',
        ];
    }

    // ─── State Methods ───

    public function requester(): self
    {
        return $this->state([
            'position' => 'Staff IT Infrastructure Project Management',
            'division' => 'IT Infrastructure Management',
        ]);
    }

    public function pfa(): self
    {
        return $this->state([
            'position' => 'Staff Procurement & Fixed Assets',
            'division' => 'IT Infrastructure Management',
        ]);
    }

    public function departmentHead(): self
    {
        return $this->state([
            'position' => 'Department Head IT Infrastructure',
            'division' => 'IT Infrastructure Management',
        ]);
    }

    public function divisionHead(): self
    {
        return $this->state([
            'position' => 'Division Head IT Infrastructure Management',
            'division' => 'IT Infrastructure Management',
        ]);
    }

    public function outsideDivision(): self
    {
        return $this->state([
            'position' => 'Staff Finance',
            'division' => 'Finance Division',
        ]);
    }
}
