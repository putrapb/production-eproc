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

    // Relasi

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    // Scope pencarian

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Helper

    public function markAsRead(): void
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Kirim notifikasi ke user tertentu
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
     * Kirim notifikasi ke semua user dengan role tertentu
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
