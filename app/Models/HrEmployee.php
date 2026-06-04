<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HrEmployee extends Model
{
    use HasFactory;

    protected $fillable = [
        'nip',
        'name',
        'position',
        'division',
    ];

    /**
     * Get the user account associated with this HR record.
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'hr_employee_id');
    }

    /**
     * Derive the system role from the HR position field.
     * Rules:
     *  - "Division Head" position → division_head
     *  - "Department Head" position → department_head
     *  - Procurement & Fixed Assets team → pfa
     *  - All other IT Infrastructure PM staff → requester
     */
    public function deriveRole(): string
    {
        $position = strtolower($this->position);

        if (str_contains($position, 'division head')) {
            return 'division_head';
        }

        if (str_contains($position, 'department head')) {
            return 'department_head';
        }

        if (str_contains($position, 'procurement') || str_contains($position, 'fixed assets')) {
            return 'pfa';
        }

        return 'requester';
    }
}
