<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'pet_id', 'service_id', 'starts_at', 'ends_at', 'status', 'customer_notes'])]
class Appointment extends Model
{
    public const PENDING = 'pending';

    public const CONFIRMED = 'confirmed';

    public const COMPLETED = 'completed';

    public const NO_SHOW = 'no_show';

    public const CANCELLED = 'cancelled';

    public const ACTIVE_STATUSES = [self::PENDING, self::CONFIRMED];

    public const TRANSITIONS = [
        'confirm' => [[self::PENDING], self::CONFIRMED],
        'complete' => [[self::CONFIRMED], self::COMPLETED],
        'no_show' => [[self::CONFIRMED], self::NO_SHOW],
        'cancel' => [[self::PENDING, self::CONFIRMED], self::CANCELLED],
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES, true)
            && $this->starts_at->isFuture();
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::PENDING => 'Pending',
            self::CONFIRMED => 'Confirmed',
            self::COMPLETED => 'Completed',
            self::NO_SHOW => 'No-show',
            self::CANCELLED => 'Cancelled',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }
}
