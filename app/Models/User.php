<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'hr_employee_id',
        'name',
        'email',
        'password',
        'role',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // Relasi

    public function hrEmployee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'hr_employee_id');
    }

    /** Alias untuk relasi HR */
    public function employee(): BelongsTo
    {
        return $this->hrEmployee();
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'user_id');
    }

    public function approvalLogs(): HasMany
    {
        return $this->hasMany(ApprovalLog::class, 'user_id');
    }

    // Helper role

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function isRequester(): bool
    {
        return $this->role === 'requester';
    }

    public function isDepartmentHead(): bool
    {
        return $this->role === 'department_head'; // pembuat keputusan
    }

    public function isTeamLeader(): bool
    {
        return $this->role === 'team_leader'; // pengecek dokumen & form
    }

    /**
     * Label role untuk UI
     */
    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'requester'       => 'IT Infrastructure Project Management',
            'team_leader'     => 'Team Leader',
            'department_head' => 'Department Head',
            default           => $this->role,
        };
    }

    /**
     * Ambil inisial nama untuk avatar
     */
    public function getInitialsAttribute(): string
    {
        $words = explode(' ', trim($this->name));
        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= strtoupper($word[0] ?? '');
        }

        return $initials;
    }
}
