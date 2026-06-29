<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'ticket_id',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    // ─────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    // ─────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ─────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────

    public function markAsRead(): void
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Send a notification to a specific user.
     */
    public static function notify(
        int $userId,
        string $type,
        string $title,
        string $message,
        ?int $ticketId = null
    ): void {
        static::create([
            'user_id'   => $userId,
            'type'      => $type,
            'title'     => $title,
            'message'   => $message,
            'ticket_id' => $ticketId,
            'read_at'   => null,
        ]);
    }

    /**
     * Notify all users with a given role.
     */
    public static function notifyRole(
        string $role,
        string $type,
        string $title,
        string $message,
        ?int $ticketId = null
    ): void {
        $users = User::where('role', $role)->get();
        foreach ($users as $user) {
            static::notify($user->id, $type, $title, $message, $ticketId);
        }
    }
}
