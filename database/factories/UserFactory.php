<?php

namespace Database\Factories;

use App\Models\HrEmployee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        $hrEmployee = HrEmployee::factory()->requester()->create();

        return [
            'hr_employee_id'   => $hrEmployee->id,
            'name'             => $hrEmployee->name,
            'email'            => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'         => Hash::make('password'),
            'role'             => 'requester',
            'remember_token'   => Str::random(10),
        ];
    }

    // ─── State Methods ───

    public function unverified(): self
    {
        return $this->state(['email_verified_at' => null]);
    }

    public function requester(): self
    {
        return $this->state(function () {
            $hr = HrEmployee::factory()->requester()->create();

            return [
                'hr_employee_id' => $hr->id,
                'name'           => $hr->name,
                'role'           => 'requester',
            ];
        });
    }

    public function teamLeader(): self
    {
        return $this->state(function () {
            $hr = HrEmployee::factory()->teamLeader()->create();

            return [
                'hr_employee_id' => $hr->id,
                'name'           => $hr->name,
                'role'           => 'team_leader',
            ];
        });
    }

    public function pfa(): self
    {
        return $this->teamLeader();
    }

    public function departmentHead(): self
    {
        return $this->state(function () {
            $hr = HrEmployee::factory()->departmentHead()->create();

            return [
                'hr_employee_id' => $hr->id,
                'name'           => $hr->name,
                'role'           => 'department_head',
            ];
        });
    }

    public function divisionHead(): self
    {
        return $this->departmentHead();
    }
}
