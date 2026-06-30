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

    public function teamLeader(): self
    {
        return $this->state([
            'position' => 'Team Leader IT Infrastructure',
            'division' => 'IT Infrastructure Management',
        ]);
    }

    public function pfa(): self
    {
        return $this->teamLeader();
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
        return $this->departmentHead();
    }

    public function outsideDivision(): self
    {
        return $this->state([
            'position' => 'Staff Finance',
            'division' => 'Finance Division',
        ]);
    }
}
