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
     *  - "Department Head" position → department_head (decision maker)
     *  - "Team Leader" position     → team_leader (forwarder/reviewer)
     *  - "Division Head" position   → department_head (legacy mapping, same as dept head)
     *  - Procurement & Fixed Assets team → team_leader (replaces legacy pfa role)
     *  - All other IT Infrastructure PM staff → requester
     */
    public function deriveRole(): string
    {
        $position = strtolower($this->position);

        // Department Head = decision maker role (formerly division_head)
        if (str_contains($position, 'department head') || str_contains($position, 'departement head')) {
            return 'department_head';
        }

        // Legacy: if spreadsheet still has 'division head', map to department_head too
        if (str_contains($position, 'division head')) {
            return 'department_head';
        }

        // Team Leader = forwarder/reviewer role (formerly department_head)
        if (str_contains($position, 'team leader')) {
            return 'team_leader';
        }

        if (str_contains($position, 'procurement') || str_contains($position, 'fixed assets')) {
            return 'team_leader';
        }

        return 'requester';
    }
}
